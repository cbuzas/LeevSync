<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithNotifications;
use App\Models\Task as TaskModel;
use App\Models\TaskRun;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Native\Laravel\Facades\ChildProcess;

class Tasks extends Component
{
    use WithNotifications;

    public array|\Illuminate\Database\Eloquent\Collection $tasks;

    /** Folder picker context: which field is being picked ('source' or 'destination') */
    public ?string $folderPickFor = null;

    /** Current path being browsed in the folder picker */
    public string $folderCurrentPath = '/';

    /** Directory entries for the current path */
    public array $folderEntries = [];

    /** Storage for ongoing process results */
    protected static array $processResults = [];

    protected static array $processErrors = [];

    protected $listeners = [
        'profileChanged' => 'refreshTasks',
        'tasks-updated' => 'refreshTasks',
    ];

    public ?TaskModel $selectedTask = null;

    public string $name = '';

    public string $source = '';

    public string $destination = '';

    public string $options = '';

    /** Key/Value option builder rows */
    public array $kvPairs = [];

    /** Toggle for the --delete flag */
    public bool $delete = true;

    /** Optional additional free-form flags (space separated) */
    public string $additionalOptions = '';

    /** Selected template for exclude patterns */
    public string $selectedTemplate = '';

    public function mount(): void
    {
        $this->tasks = $this->loadTasks();
        $this->resetOptionBuilder();
    }

    public function loadTasks(): array|\Illuminate\Database\Eloquent\Collection
    {
        $currentProfile = session('currentProfileId');

        if ($currentProfile) {
            return TaskModel::where('profile_id', $currentProfile)->latest()->get();
        }

        return [];
    }

    /**
     * Check if any tasks are currently running
     */
    public function hasRunningTasks(): bool
    {
        if (is_array($this->tasks) && empty($this->tasks)) {
            return false;
        }

        foreach ($this->tasks as $task) {
            if ($task->status === 'running') {
                return true;
            }
        }

        return false;
    }

    /**
     * Automatically check and update running tasks status
     * This method is called by wire:poll
     */
    public function checkRunningTasks(): void
    {
        if (! $this->hasRunningTasks()) {
            return;
        }

        $currentProfile = session('currentProfileId');
        if (! $currentProfile) {
            return;
        }

        $runningTasks = TaskModel::where('profile_id', $currentProfile)
            ->where('status', 'running')
            ->get();

        foreach ($runningTasks as $task) {
            if ($task->log_file && file_exists($task->log_file)) {
                try {
                    $logContent = file_get_contents($task->log_file);

                    // Check if the process has finished by looking for completion indicators
                    $hasError = strpos($logContent, 'error') !== false ||
                                strpos($logContent, 'failed') !== false ||
                                strpos($logContent, 'rsync error') !== false;

                    // Check if rsync has completed (log file stops growing)
                    $fileSize = filesize($task->log_file);
                    $lastModified = filemtime($task->log_file);
                    $timeSinceModified = time() - $lastModified;

                    // If log hasn't been modified in 5 seconds, consider it complete
                    if ($timeSinceModified > 5) {
                        // Check if this is a dry-run by looking at the log filename
                        $isDryRun = strpos($task->log_file, 'dryrun') !== false;

                        $status = $hasError ? 'failed' : 'completed';
                        $errorMessage = $hasError ? 'Errors detected in log' : null;

                        $updateData = [
                            'status' => $status,
                            'last_output' => $this->tail($logContent, 100),
                            'last_error' => $errorMessage,
                        ];

                        // If it's a dry-run, parse and save the summary
                        if ($isDryRun) {
                            $summary = $this->parseRsyncDryRunSummary($logContent);
                            $updateData['last_dry_run_summary'] = $summary;
                        }

                        $task->update($updateData);

                        // Update TaskRun if not a dry-run
                        if (! $isDryRun) {
                            $taskRun = TaskRun::where('task_id', $task->id)
                                ->where('status', 'running')
                                ->latest('started_at')
                                ->first();

                            if ($taskRun) {
                                $taskRun->update([
                                    'completed_at' => now(),
                                    'status' => $status,
                                    'output' => $this->tail($logContent, 100),
                                    'error_message' => $errorMessage,
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error during automatic status check', [
                        'task_id' => $task->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Refresh the tasks list
        $this->tasks = $this->loadTasks();
    }

    /**
     * Get available exclude templates for common project types
     */
    public function getExcludeTemplates(): array
    {
        return [
            'none' => [
                'name' => 'No template',
                'filters' => [],
            ],
            'laravel' => [
                'name' => 'Laravel Project',
                'filters' => [
                    ['type' => 'exclude', 'value' => 'node_modules/'],
                    ['type' => 'exclude', 'value' => 'vendor/'],
                    ['type' => 'exclude', 'value' => '.env'],
                    ['type' => 'exclude', 'value' => 'storage/logs/'],
                    ['type' => 'exclude', 'value' => 'storage/framework/cache/'],
                    ['type' => 'exclude', 'value' => 'storage/framework/sessions/'],
                    ['type' => 'exclude', 'value' => 'storage/framework/views/'],
                    ['type' => 'exclude', 'value' => 'bootstrap/cache/'],
                    ['type' => 'exclude', 'value' => '.git/'],
                    ['type' => 'exclude', 'value' => '.idea/'],
                    ['type' => 'exclude', 'value' => '.vscode/'],
                    ['type' => 'exclude', 'value' => '*.log'],
                ],
            ],
            'nodejs' => [
                'name' => 'Node.js Project',
                'filters' => [
                    ['type' => 'exclude', 'value' => 'node_modules/'],
                    ['type' => 'exclude', 'value' => 'dist/'],
                    ['type' => 'exclude', 'value' => 'build/'],
                    ['type' => 'exclude', 'value' => '.env'],
                    ['type' => 'exclude', 'value' => '.env.local'],
                    ['type' => 'exclude', 'value' => '.git/'],
                    ['type' => 'exclude', 'value' => '.idea/'],
                    ['type' => 'exclude', 'value' => '.vscode/'],
                    ['type' => 'exclude', 'value' => '*.log'],
                    ['type' => 'exclude', 'value' => 'npm-debug.log*'],
                    ['type' => 'exclude', 'value' => 'yarn-error.log*'],
                ],
            ],
            'python' => [
                'name' => 'Python Project',
                'filters' => [
                    ['type' => 'exclude', 'value' => '__pycache__/'],
                    ['type' => 'exclude', 'value' => '*.py[cod]'],
                    ['type' => 'exclude', 'value' => '*$py.class'],
                    ['type' => 'exclude', 'value' => 'venv/'],
                    ['type' => 'exclude', 'value' => 'env/'],
                    ['type' => 'exclude', 'value' => '.env'],
                    ['type' => 'exclude', 'value' => '.venv/'],
                    ['type' => 'exclude', 'value' => 'dist/'],
                    ['type' => 'exclude', 'value' => 'build/'],
                    ['type' => 'exclude', 'value' => '*.egg-info/'],
                    ['type' => 'exclude', 'value' => '.git/'],
                    ['type' => 'exclude', 'value' => '.idea/'],
                    ['type' => 'exclude', 'value' => '.vscode/'],
                ],
            ],
            'wordpress' => [
                'name' => 'WordPress Project',
                'filters' => [
                    ['type' => 'exclude', 'value' => 'node_modules/'],
                    ['type' => 'exclude', 'value' => 'wp-content/uploads/'],
                    ['type' => 'exclude', 'value' => 'wp-content/cache/'],
                    ['type' => 'exclude', 'value' => 'wp-content/backup*/'],
                    ['type' => 'exclude', 'value' => '.git/'],
                    ['type' => 'exclude', 'value' => '.idea/'],
                    ['type' => 'exclude', 'value' => '.vscode/'],
                    ['type' => 'exclude', 'value' => '*.log'],
                    ['type' => 'exclude', 'value' => 'wp-config.php'],
                ],
            ],
            'git' => [
                'name' => 'Generic Git Project',
                'filters' => [
                    ['type' => 'exclude', 'value' => '.git/'],
                    ['type' => 'exclude', 'value' => '.gitignore'],
                    ['type' => 'exclude', 'value' => 'node_modules/'],
                    ['type' => 'exclude', 'value' => 'vendor/'],
                    ['type' => 'exclude', 'value' => 'dist/'],
                    ['type' => 'exclude', 'value' => 'build/'],
                    ['type' => 'exclude', 'value' => '.env'],
                    ['type' => 'exclude', 'value' => '.env.*'],
                    ['type' => 'exclude', 'value' => '.idea/'],
                    ['type' => 'exclude', 'value' => '.vscode/'],
                    ['type' => 'exclude', 'value' => '*.log'],
                ],
            ],
        ];
    }

    /**
     * Called when the selectedTemplate property is updated
     */
    public function updatedSelectedTemplate(): void
    {
        if ($this->selectedTemplate === '' || $this->selectedTemplate === 'none') {
            return;
        }

        $templates = $this->getExcludeTemplates();

        if (isset($templates[$this->selectedTemplate])) {
            $this->kvPairs = $templates[$this->selectedTemplate]['filters'];

            // Ensure at least one empty row if template has no filters
            if (empty($this->kvPairs)) {
                $this->kvPairs = [['type' => 'exclude', 'value' => '']];
            }

            $this->notifySuccess('The filters from the "'.$templates[$this->selectedTemplate]['name'].'" template have been applied.');
        }
    }

    private function resetOptionBuilder(): void
    {
        $this->kvPairs = [
            ['type' => 'exclude', 'value' => ''],
        ];
        $this->delete = true;
        $this->additionalOptions = '';
    }

    /**
     * Start an rsync process with ChildProcess
     */
    private function startRsyncProcess(TaskModel $task, bool $dryRun = false): ?string
    {

        $args = $this->buildCommandArgs($task);

        if ($dryRun) {
            $args[] = '--dry-run';
            $args[] = '--itemize-changes';
        }

        $time = time();

        // 📌 Define the log file path (in storage/logs/)
        $logFile = storage_path("logs/rsync_task_{$task->id}_".($dryRun ? 'dryrun' : 'run')."_$time.log");

        // 📌 Add the log option to rsync
        $args[] = "--log-file={$logFile}";

        try {
            $alias = $this->generateProcessAlias($task->id, $dryRun);

            Log::info('Starting rsync process', [
                'task_id' => $task->id,
                'alias' => $alias,
                'command' => implode(' ', $args),
                'dry_run' => $dryRun,
                'log_file' => $logFile,
            ]);

            ChildProcess::start(
                cmd: $args,
                alias: $alias
            );

            $task->update(['log_file' => $logFile]);

            // 📌 Return success
            return true;

        } catch (\Exception $e) {
            Log::error('Error starting rsync', [
                'task_id' => $task->id,
                'dry_run' => $dryRun,
                'error' => $e->getMessage(),
            ]);

            $this->notifyError('Unable to start rsync: '.$e->getMessage());

            return null;
        }
    }

    public function dryRun(TaskModel $task): void
    {
        $task->update([
            'status' => 'running',
            'last_run_at' => now(),
            'last_error' => null,
        ]);
        $this->tasks = $this->loadTasks();

        // Start the dry-run process
        $started = $this->startRsyncProcess($task, true);

        if (! $started) {
            $task->update(['status' => 'failed', 'last_error' => 'Failed to start dry-run']);
            $this->tasks = $this->loadTasks();
            $this->notifyError('Unable to start dry-run.');

            return;
        }

        $this->notifySuccess('The dry-run is in progress. Results will be updated automatically.');

        $this->dispatch('dry-run-started', taskId: $task->id);
    }

    /**
     * Generate a unique alias for a process
     */
    private function generateProcessAlias(int $taskId, bool $dryRun): string
    {
        $type = $dryRun ? 'dry' : 'run';

        return "rsync-task-{$taskId}-{$type}-".uniqid();
    }

    public function addPair(): void
    {
        $this->kvPairs[] = ['type' => 'exclude', 'value' => ''];
    }

    public function removePair(int $index): void
    {
        if (isset($this->kvPairs[$index])) {
            array_splice($this->kvPairs, $index, 1);
        }
        if (count($this->kvPairs) === 0) {
            $this->kvPairs[] = ['type' => 'exclude', 'value' => ''];
        }
    }

    public function openModal(string $modalName, ?int $key = null): void
    {
        $this->reset('name', 'source', 'destination', 'options');
        $this->resetOptionBuilder();
        $this->selectedTask = null;

        if ($modalName === 'create') {
            Flux::modal('task-create')->show();
        } elseif ($modalName === 'update' && $key !== null && isset($this->tasks[$key])) {
            $this->loadTaskDetails($key);
            Flux::modal('task-update')->show();
        } elseif ($modalName === 'log' && $key !== null && isset($this->tasks[$key])) {
            $this->loadTaskDetails($key);
            Flux::modal('task-log')->show();
        }
    }

    public function loadTaskDetails(int $key): void
    {
        $task = $this->tasks[$key] ?? null;
        if ($task instanceof TaskModel) {
            $this->selectedTask = $task;
            $this->name = (string) $task->name;
            $this->source = (string) $task->source;
            $this->destination = (string) $task->destination;

            $opts = (array) ($task->settings['options'] ?? []);
            $this->populateUIFromOptions($opts);
            $this->options = trim(collect($opts)->implode(' '));
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'source' => ['required', 'string', 'max:2048'],
            'destination' => ['required', 'string', 'max:2048'],
            'kvPairs.*.type' => ['nullable', 'string', 'max:50'],
            'kvPairs.*.value' => ['nullable', 'string', 'max:2048'],
            'additionalOptions' => ['nullable', 'string', 'max:2048'],
        ];
    }

    private function populateUIFromOptions(array $options): void
    {
        $this->delete = in_array('--delete', $options, true);
        $pairs = [];
        $remaining = [];

        foreach ($options as $opt) {
            if ($opt === '--delete') {
                continue;
            }
            if (preg_match('/^--([a-zA-Z0-9-]+)=(.*)$/', $opt, $m)) {
                $type = (string) $m[1];
                $value = trim((string) $m[2], "'\"");
                $pairs[] = ['type' => $type, 'value' => $value];
            } else {
                $remaining[] = $opt;
            }
        }

        $this->kvPairs = count($pairs) > 0 ? $pairs : [['type' => 'exclude', 'value' => '']];
        $this->additionalOptions = trim(collect($remaining)->implode(' '));
    }

    private function buildOptionsFromUI(): array
    {
        $opts = ['-avh'];

        if ($this->delete) {
            $opts[] = '--delete';
        }

        foreach ($this->kvPairs as $row) {
            $type = trim((string) ($row['type'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            if ($type !== '' && $value !== '') {
                $opts[] = "--{$type}=".$value;
            }
        }

        if (trim($this->additionalOptions) !== '') {
            $extra = $this->explodeOptions($this->additionalOptions);
            foreach ($extra as $e) {
                $opts[] = $e;
            }
        }

        return array_values(array_unique(array_filter($opts)));
    }

    public function createTask(): void
    {
        $this->validate();

        $profileId = (int) (session('currentProfileId') ?? 0);
        if ($profileId <= 0) {
            $this->notifyError('Please select or create a profile first.');

            return;
        }

        $settings = [
            'options' => $this->buildOptionsFromUI(),
        ];

        $task = TaskModel::create([
            'profile_id' => $profileId,
            'name' => $this->name,
            'source' => $this->source,
            'destination' => $this->destination,
            'settings' => $settings,
        ]);

        $task->update(['cmd' => $this->buildCommandString($task)]);

        $this->tasks = $this->loadTasks();

        Flux::modal('task-create')->close();
        $this->notifySuccess('Task "'.$this->name.'" created successfully!');
    }

    public function updateTask(): void
    {
        $this->validate();

        if ($this->selectedTask) {
            $this->selectedTask->update([
                'name' => $this->name,
                'source' => $this->source,
                'destination' => $this->destination,
                'settings' => [
                    'options' => $this->buildOptionsFromUI(),
                ],
            ]);

            $this->selectedTask->update(['cmd' => $this->buildCommandString($this->selectedTask)]);

            $this->tasks = $this->loadTasks();

            Flux::modal('task-update')->close();
            $this->notifySuccess('The task was updated successfully.');
        }
    }

    public function execute(int $id): void
    {
        $task = TaskModel::find($id);
        if (! $task) {
            $this->notifyError('Task not found.');

            return;
        }

        $task->update([
            'status' => 'running',
            'last_run_at' => now(),
            'last_error' => null,
        ]);
        $this->tasks = $this->loadTasks();

        $started = $this->startRsyncProcess($task, false);

        if ($started) {
            // Create TaskRun history entry
            TaskRun::create([
                'task_id' => $task->id,
                'task_name' => $task->name,
                'source' => $task->source,
                'destination' => $task->destination,
                'started_at' => now(),
                'status' => 'running',
                'log_file_path' => $task->log_file,
            ]);

            $this->notifySuccess('The synchronization is in progress. Refresh to see the results.');
            $this->dispatch('sync-started', taskId: $task->id);
        } else {
            $task->update([
                'status' => 'failed',
                'last_error' => 'Failed to start rsync process',
            ]);

            // Create failed TaskRun entry
            TaskRun::create([
                'task_id' => $task->id,
                'task_name' => $task->name,
                'source' => $task->source,
                'destination' => $task->destination,
                'started_at' => now(),
                'completed_at' => now(),
                'status' => 'failed',
                'error_message' => 'Failed to start rsync process',
            ]);

            $this->tasks = $this->loadTasks();
        }
    }

    /**
     * Parse and update dry-run results
     */
    public function updateDryRunResults(int $id): void
    {
        $task = TaskModel::find($id);
        if (! $task || ! $task->log_file || ! file_exists($task->log_file)) {
            $this->notifyError('Log file not found.');

            return;
        }

        try {
            $logContent = file_get_contents($task->log_file);
            $summary = $this->parseRsyncDryRunSummary($logContent);

            $task->update([
                'last_dry_run_summary' => $summary,
                'last_output' => $this->tail($logContent, 100),
            ]);

            $this->tasks = $this->loadTasks();
            $this->notifySuccess(
                sprintf(
                    'Added: %d, Modified: %d, Deleted: %d',
                    $summary['added'] ?? 0,
                    $summary['modified'] ?? 0,
                    $summary['deleted'] ?? 0
                )
            );
        } catch (\Exception $e) {
            Log::error('Error analyzing dry-run', [
                'task_id' => $id,
                'error' => $e->getMessage(),
            ]);
            $this->notifyError('Unable to analyze dry-run results.');
        }
    }

    /**
     * Update run results
     */
    public function updateRunResults(int $id): void
    {
        $task = TaskModel::find($id);
        if (! $task || ! $task->log_file || ! file_exists($task->log_file)) {
            $this->notifyError('Log file not found.');

            return;
        }

        try {
            $logContent = file_get_contents($task->log_file);

            // Check if the process succeeded by analyzing the log
            $hasError = strpos($logContent, 'error') !== false ||
                        strpos($logContent, 'failed') !== false ||
                        strpos($logContent, 'rsync error') !== false;

            $status = $hasError ? 'failed' : 'completed';
            $errorMessage = $hasError ? 'Errors detected in log' : null;

            $task->update([
                'status' => $status,
                'last_output' => $this->tail($logContent, 100),
                'last_error' => $errorMessage,
            ]);

            // Update the most recent TaskRun for this task
            $taskRun = TaskRun::where('task_id', $task->id)
                ->where('status', 'running')
                ->latest('started_at')
                ->first();

            if ($taskRun) {
                $taskRun->update([
                    'completed_at' => now(),
                    'status' => $status,
                    'output' => $this->tail($logContent, 100),
                    'error_message' => $errorMessage,
                ]);
            }

            $this->tasks = $this->loadTasks();

            if ($hasError) {
                $this->notifyError('Synchronization completed with errors. Check the logs for more details.');
            } else {
                $this->notifySuccess('The synchronization completed successfully.');
            }
        } catch (\Exception $e) {
            Log::error('Error analyzing results', [
                'task_id' => $id,
                'error' => $e->getMessage(),
            ]);

            $task->update(['status' => 'failed', 'last_error' => $e->getMessage()]);

            // Update TaskRun if exists
            $taskRun = TaskRun::where('task_id', $task->id)
                ->where('status', 'running')
                ->latest('started_at')
                ->first();

            if ($taskRun) {
                $taskRun->update([
                    'completed_at' => now(),
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            $this->tasks = $this->loadTasks();
            $this->notifyError('Unable to analyze results.');
        }
    }

    /**
     * Folder Picker Methods
     */
    public function openFolderPicker(string $for): void
    {
        $for = in_array($for, ['source', 'destination'], true) ? $for : 'source';
        $this->folderPickFor = $for;

        if ($this->{$for} !== '') {
            $start = $this->{$for};
        } else {
            $start = is_dir('/Volumes') && is_readable('/Volumes') ? '/Volumes' : getcwd();
        }

        if (! $start || ! is_dir($start)) {
            $start = DIRECTORY_SEPARATOR;
        }

        $this->loadFolderEntries($start);
        Flux::modal('folder-picker')->show();
    }

    public function loadFolderEntries(?string $path = null): void
    {
        $path = $path ?: $this->folderCurrentPath;
        $real = $this->sanitizePath($path);

        if (! is_dir($real) || ! is_readable($real)) {
            $this->notifyError('The selected path is not accessible: '.$path);

            return;
        }

        $this->folderCurrentPath = $real;
        $this->folderEntries = $this->listDirectories($real);
    }

    public function folderUp(): void
    {
        $parent = dirname(rtrim($this->folderCurrentPath, DIRECTORY_SEPARATOR));
        if ($parent === '' || $parent === $this->folderCurrentPath) {
            $parent = DIRECTORY_SEPARATOR;
        }
        $this->loadFolderEntries($parent);
    }

    public function folderInto(string $path): void
    {
        $this->loadFolderEntries($path);
    }

    public function folderUseCurrent(): void
    {
        if ($this->folderPickFor === 'destination') {
            $this->destination = $this->folderCurrentPath.(str_ends_with($this->folderCurrentPath, DIRECTORY_SEPARATOR) ? '' : DIRECTORY_SEPARATOR);
        } else {
            $this->source = $this->folderCurrentPath.(str_ends_with($this->folderCurrentPath, DIRECTORY_SEPARATOR) ? '' : DIRECTORY_SEPARATOR);
        }

        Flux::modal('folder-picker')->close();
    }

    public function folderJump(): void
    {
        $this->loadFolderEntries($this->folderCurrentPath);
    }

    public function folderCancel(): void
    {
        Flux::modal('folder-picker')->close();
    }

    public function folderGoToVolumes(): void
    {
        $target = is_dir('/Volumes') && is_readable('/Volumes') ? '/Volumes' : DIRECTORY_SEPARATOR;
        $this->loadFolderEntries($target);
    }

    private function sanitizePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return DIRECTORY_SEPARATOR;
        }
        $real = realpath($path);

        return $real !== false ? $real : $path;
    }

    private function listDirectories(string $path): array
    {
        $items = @scandir($path) ?: [];
        $dirs = [];

        foreach ($items as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }
            $full = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$name;
            if (is_dir($full)) {
                $dirs[] = [
                    'name' => $name,
                    'path' => $full,
                ];
            }
        }

        usort($dirs, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        return $dirs;
    }

    /**
     * Utility Methods
     */
    private function explodeOptions(string $options): array
    {
        $options = trim($options);
        if ($options === '') {
            return [];
        }

        preg_match_all('/\"[^\"]+\"|\'[^\']+\'|[^\s]+/', $options, $matches);

        return collect($matches[0])
            ->map(fn ($opt) => trim($opt, "'\""))
            ->filter()
            ->values()
            ->all();
    }

    private function buildCommandArgs(TaskModel $task): array
    {

        $args[] = 'rsync';

        $options = (array) ($task->settings['options'] ?? []);

        if (empty($options)) {
            $options = ['-avh', '--delete'];
        }

        foreach ($options as $opt) {
            $args[] = $opt;
        }

        $args[] = $task->source;
        $args[] = $task->destination;

        return $args;
    }

    private function buildCommandString(TaskModel $task): string
    {
        $args = $this->buildCommandArgs($task);

        return implode(' ', array_map(fn ($a) => str_contains($a, ' ') ? escapeshellarg($a) : $a, $args));
    }

    private function parseRsyncDryRunSummary(string $output): array
    {
        $lines = preg_split("/[\r\n]+/", $output);
        $deleted = 0;
        $transferred = 0;
        $added = 0;
        $modified = 0;

        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '') {
                continue;
            }

            if (str_starts_with($trim, '*deleting ')) {
                $deleted++;
            }
            if (preg_match('/^>f\+{3,}/', $trim)) {
                $added++;
            }
            if (preg_match('/^>f(?!\+{3,})/', $trim)) {
                $modified++;
            }
            if (preg_match('/^Number of files transferred:\s*(\d+)/i', $trim, $m)) {
                $transferred = (int) ($m[1] ?? 0);
            }
        }

        return [
            'transferred' => $transferred,
            'added' => $added,
            'modified' => $modified,
            'deleted' => $deleted,
            'raw' => $this->tail($output, 50),
        ];
    }

    private function tail(string $text, int $lines = 50): string
    {
        $arr = preg_split("/[\r\n]+/", $text);
        $slice = array_slice($arr, -$lines);

        return implode(PHP_EOL, $slice);
    }

    public function refreshTasks(): void
    {
        $this->tasks = $this->loadTasks();
    }

    public function render()
    {
        return view('livewire.tasks', [
            'tasks' => $this->tasks,
        ]);

    }
}
