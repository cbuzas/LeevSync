<div @if($this->hasRunningTasks()) wire:poll.3s="checkRunningTasks" @endif>

    <div class="flex justify-between items-center">
        <flux:heading size="2xl" level="1">
            {{ __('Your synchronisations') }}
        </flux:heading>

        <flux:button
            class="text-leev-primary-300 cursor-pointer"
            icon="plus"
            variant="secondary"
            wire:click="openModal('create')"
        />
    </div>

    <flux:separator variant="subtle" class="my-6" />

    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-gray-200 dark:border-zinc-700 px-6 p-3">
        @if(count($tasks) === 0)
            <div class="text-center p-6">
                <p class="text-gray-400 font-light dark:text-white/30">
                    {{ __('You have no synchronisations yet. Start by creating one!') }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Folders</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dry-run Summary</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Run</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700">
                        @foreach($tasks as $key => $task)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="font-semibold text-black dark:text-white tracking-wider">{{ $task->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <flux:badge size="sm" color="gray">
                                        Source:
                                    </flux:badge>
                                    <flux:tooltip content="{{ $task->source }}" position="top">
                                        <span class="text-xs font-mono font-light text-gray-600 dark:text-white/70">{{ Str::limit($task->source, 20) }}</span>
                                    </flux:tooltip>
                                    <flux:separator variant="subtle" class="my-1" />
                                    <flux:badge size="sm" color="gray">
                                        Destination:
                                    </flux:badge>
                                    <flux:tooltip content="{{ $task->destination }}" position="top">
                                        <span class="text-xs font-mono font-light text-gray-600 dark:text-white/70">{{ Str::limit($task->destination, 20) }}</span>
                                    </flux:tooltip>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $status = $task->status ?? 'idle';
                                        $statusConfig = [
                                            'idle' => ['color' => 'gray', 'icon' => 'pause', 'text' => 'Idle'],
                                            'running' => ['color' => 'blue', 'icon' => 'arrow-path', 'text' => 'Running'],
                                            'completed' => ['color' => 'green', 'icon' => 'check', 'text' => 'Completed'],
                                            'failed' => ['color' => 'red', 'icon' => 'x-mark', 'text' => 'Failed'],
                                        ];
                                        $config = $statusConfig[$status] ?? $statusConfig['idle'];
                                    @endphp
                                    <flux:badge color="{{ $config['color'] }}" size="sm">
                                        <flux:icon name="{{ $config['icon'] }}" :class="'w-3 h-3' . ($status === 'running' ? ' animate-spin' : '')" />
                                        {{ $config['text'] }}
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(!empty($task->last_dry_run_summary))
                                        <div class="flex gap-2 text-xs">
                                            <span class="text-green-600 dark:text-green-400" title="Added">
                                                <flux:icon name="plus" class="w-3 h-3 inline" />{{ $task->last_dry_run_summary['added'] ?? 0 }}
                                            </span>
                                            <span class="text-blue-600 dark:text-blue-400" title="Modified">
                                                <flux:icon name="pencil" class="w-3 h-3 inline" />{{ $task->last_dry_run_summary['modified'] ?? 0 }}
                                            </span>
                                            <span class="text-red-600 dark:text-red-400" title="Deleted">
                                                <flux:icon name="trash" class="w-3 h-3 inline" />{{ $task->last_dry_run_summary['deleted'] ?? 0 }}
                                            </span>
                                        </div>

                                    @else
                                        <span class="text-gray-500 dark:text-gray-400 text-xs italic">No dry-run yet</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs">
                                        @if($task->last_run_at)
                                            <div class="text-gray-600 dark:text-white/70">
                                                {{ $task->last_run_at->diffForHumans() }}
                                            </div>
                                            <div class="text-gray-500 dark:text-gray-400 text-xs">
                                                {{ $task->last_run_at->format('M j, Y H:i') }}
                                            </div>
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">Never run</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:button
                                            icon="pencil"
                                            size="sm"
                                            variant="outline"
                                            wire:click="openModal('update', {{ $key }})"
                                            wire:loading.attr="disabled"
                                            title="Edit task"
                                        />

                                        <flux:button
                                            icon="document-text"
                                            size="sm"
                                            variant="outline"
                                            wire:click="openModal('log', {{ $key }})"
                                            title="View logs"
                                            wire:loading.attr="disabled"
                                        />

                                        @if($task->log_file && file_exists($task->log_file))
                                            <flux:button
                                                icon="arrow-path"
                                                size="sm"
                                                variant="ghost"
                                                wire:click="updateDryRunResults({{ $task->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="updateDryRunResults({{ $task->id }})"
                                                title="Refresh dry-run results"
                                            />
                                        @endif

                                        @if($task->status === 'running')
                                            <flux:button
                                                icon="arrow-path"
                                                size="sm"
                                                variant="ghost"
                                                wire:click="updateRunResults({{ $task->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="updateRunResults({{ $task->id }})"
                                                title="Check execution status"
                                                class="animate-pulse"
                                            />
                                        @endif

                                        <flux:button
                                            icon="bolt"
                                            size="sm"
                                            variant="outline"
                                            wire:click="dryRun({{ $task->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="dryRun({{ $task->id }})"
                                            title="Dry run"
                                        />

                                        <flux:button
                                            icon="play"
                                            size="sm"
                                            variant="primary"
                                            wire:click="execute({{ $task->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="execute({{ $task->id }})"
                                            title="Execute sync"
                                        />

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>


    <!-- Create Task Modal -->
    <flux:modal class="min-w-2xl" position="top" name="task-create">
        <div class="space-y-6">
            <flux:heading size="xl" level="2">Create a new task</flux:heading>
            <flux:separator />

            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input wire:model="name" placeholder="My backup task" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Source</flux:label>
                <div class="flex gap-2">
                    <div class="w-full">
                        <flux:input wire:model="source" placeholder="/path/to/source/" />
                    </div>
                    <div class="col-span-1">
                        <flux:button icon="folder" variant="outline" title="Browse" wire:click="openFolderPicker('source')" />
                    </div>
                </div>
                <flux:error name="source" />
                <flux:text class="text-xs text-gray-500">Local path to sync from</flux:text>
            </flux:field>

            <flux:field>
                <flux:label>Destination</flux:label>
                <div class="flex gap-2">
                    <div class="w-full">
                        <flux:input wire:model="destination" placeholder="user@host:/path/to/destination/ or /local/path/" />
                    </div>
                    <div class="col-span-1">
                        <flux:button icon="folder" variant="outline" title="Browse" wire:click="openFolderPicker('destination')" />
                    </div>
                </div>
                <flux:error name="destination" />
                <flux:text class="text-xs text-gray-500">Remote or local destination path</flux:text>
            </flux:field>

            <div class="space-y-3">
                <flux:label>Rsync Options</flux:label>

                <!-- Delete Option -->
                <flux:field>
                    <div class="flex items-center gap-3">
                        <flux:switch wire:model="delete" />
                        <flux:label>Delete missing files in destination</flux:label>
                    </div>
                    <flux:text class="text-xs text-gray-400 ml-12">Adds <code class="bg-zinc-100 dark:bg-zinc-800 px-1 rounded">--delete</code> flag to remove files from destination that don't exist in source</flux:text>
                </flux:field>

                <!-- Template Selector -->
                <flux:field>
                    <flux:label>Template d'exclusion</flux:label>
                    <flux:select wire:model.live="selectedTemplate" placeholder="Choose a template...">
                        @foreach($this->getExcludeTemplates() as $key => $template)
                            <option value="{{ $key }}">{{ $template['name'] }}</option>
                        @endforeach
                    </flux:select>
                    <flux:text class="text-xs text-gray-400">
                        Automatically applies exclusion filters for common project types
                    </flux:text>
                </flux:field>

                <!-- Exclude/Include Options -->
                <div class="space-y-2">
                    <flux:label class="text-sm">Filters</flux:label>
                    @foreach($kvPairs as $i => $row)
                        <div class="flex gap-2 items-end">
                            <div class="w-3/12">
                                <flux:select wire:model="kvPairs.{{ $i }}.type">
                                    <option value="exclude">exclude</option>
                                    <option value="include">include</option>
                                    <option value="filter">filter</option>
                                    <option value="exclude-from">exclude-from</option>
                                    <option value="include-from">include-from</option>
                                </flux:select>
                            </div>
                            <div class="w-full">
                                <flux:input wire:model="kvPairs.{{ $i }}.value" placeholder="*.log, .git/, node_modules/" />
                            </div>
                            <div>
                                <flux:button icon="x-mark" size="sm" variant="danger" wire:click="removePair({{ $i }})" />
                            </div>
                        </div>
                    @endforeach
                    <div>
                        <flux:button icon="plus" size="sm" variant="ghost" wire:click="addPair">Add filter</flux:button>
                    </div>
                    <flux:error name="kvPairs.*.value" />
                </div>
            </div>

            <flux:field>
                <flux:label>Additional rsync options</flux:label>
                <flux:input wire:model="additionalOptions" placeholder="--bwlimit=5000 --compress" />
                <flux:text class="text-xs text-gray-400">Space-separated rsync flags. Example: --compress --bwlimit=1000</flux:text>
                <flux:error name="additionalOptions" />
            </flux:field>

            <div class="flex gap-3 pt-4">
                <flux:button wire:click="createTask" variant="primary" class="flex-1" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="createTask">Create task</span>
                    <span wire:loading wire:target="createTask">Creating...</span>
                </flux:button>
                <flux:button variant="secondary" onclick="Flux.modal('task-create').close()">Cancel</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Update Task Modal -->
    <flux:modal class="min-w-2xl" position="top" name="task-update">
        <div class="space-y-6">
            <flux:heading size="xl" level="2">Update: {{ $selectedTask->name ?? '' }}</flux:heading>
            <flux:separator />

            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input wire:model="name" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Source</flux:label>
                <div class="flex gap-2">
                    <div class="w-full">
                        <flux:input wire:model="source" />
                    </div>
                    <div class="col-span-1">
                        <flux:button icon="folder" variant="outline" title="Browse" wire:click="openFolderPicker('source')" />
                    </div>
                </div>
                <flux:error name="source" />
            </flux:field>

            <flux:field>
                <flux:label>Destination</flux:label>
                <div class="flex gap-2">
                    <div class="w-full">
                        <flux:input wire:model="destination" />
                    </div>
                    <div class="col-span-1">
                        <flux:button icon="folder" variant="outline" title="Browse" wire:click="openFolderPicker('destination')" />
                    </div>
                </div>
                <flux:error name="destination" />
            </flux:field>

            <div class="space-y-3">
                <flux:label>Rsync Options</flux:label>

                <!-- Delete Option -->
                <flux:field>
                    <div class="flex items-center gap-3">
                        <flux:switch wire:model="delete" />
                        <flux:label>Delete missing files in destination</flux:label>
                    </div>
                    <flux:text class="text-xs text-gray-400 ml-12">Adds <code class="bg-zinc-100 dark:bg-zinc-800 px-1 rounded">--delete</code> flag</flux:text>
                </flux:field>

                <!-- Template Selector -->
                <flux:field>
                    <flux:label>Template d'exclusion</flux:label>
                    <flux:select wire:model.live="selectedTemplate" placeholder="Choisir un template...">
                        <option value="">-- Sélectionner un template --</option>
                        @foreach($this->getExcludeTemplates() as $key => $template)
                            <option value="{{ $key }}">{{ $template['name'] }}</option>
                        @endforeach
                    </flux:select>
                    <flux:text class="text-xs text-gray-400">Applique automatiquement des filtres d'exclusion pour des types de projets courants</flux:text>
                </flux:field>

                <!-- Exclude/Include Options -->
                <div class="space-y-2">
                    <flux:label class="text-sm">Filters</flux:label>
                    @foreach($kvPairs as $i => $row)
                        <div class="flex gap-2 items-end">
                            <div class="w-3/12">
                                <flux:select wire:model="kvPairs.{{ $i }}.type">
                                    <option value="exclude">exclude</option>
                                    <option value="include">include</option>
                                    <option value="filter">filter</option>
                                    <option value="exclude-from">exclude-from</option>
                                    <option value="include-from">include-from</option>
                                </flux:select>
                            </div>
                            <div class="w-full">
                                <flux:input wire:model="kvPairs.{{ $i }}.value" placeholder="*.log, .git/, node_modules/" />
                            </div>
                            <div>
                                <flux:button icon="x-mark" size="sm" variant="danger" wire:click="removePair({{ $i }})" />
                            </div>
                        </div>
                    @endforeach
                    <div>
                        <flux:button icon="plus" size="sm" variant="ghost" wire:click="addPair">Add filter</flux:button>
                    </div>
                    <flux:error name="kvPairs.*.value" />
                </div>
            </div>

            <flux:field>
                <flux:label>Additional rsync options</flux:label>
                <flux:input wire:model="additionalOptions" placeholder="--bwlimit=5000 --compress" />
                <flux:text class="text-xs text-gray-400">Space-separated rsync flags</flux:text>
                <flux:error name="additionalOptions" />
            </flux:field>

            <div class="flex gap-2 pt-4">
                <flux:button wire:click="updateTask" variant="primary" class="flex-1" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="updateTask">Update task</span>
                    <span wire:loading wire:target="updateTask">Updating...</span>
                </flux:button>
                <flux:button wire:click="dryRun({{ $selectedTask->id ?? 0 }})" variant="secondary" icon="bolt" title="Test with dry run" wire:loading.attr="disabled">
                    Dry Run
                </flux:button>
                <flux:button variant="ghost" onclick="Flux.modal('task-update').close()">Cancel</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal class="min-w-4xl" position="top" name="task-log">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="xl" level="2">Task Logs: {{ $selectedTask->name ?? '' }}</flux:heading>
                @if($selectedTask)
                    @php
                        $status = $selectedTask->status ?? 'idle';
                        $statusConfig = [
                            'idle' => ['color' => 'gray', 'icon' => 'pause', 'text' => 'Idle'],
                            'running' => ['color' => 'blue', 'icon' => 'arrow-path', 'text' => 'Running'],
                            'completed' => ['color' => 'green', 'icon' => 'check', 'text' => 'Completed'],
                            'failed' => ['color' => 'red', 'icon' => 'x-mark', 'text' => 'Failed'],
                        ];
                        $config = $statusConfig[$status] ?? $statusConfig['idle'];
                    @endphp
                    <flux:badge color="{{ $config['color'] }}">
                        <flux:icon name="{{ $config['icon'] }}" :class="'w-3 h-3' . ($status === 'running' ? ' animate-spin' : '')" />
                        {{ $config['text'] }}
                    </flux:badge>
                @endif
            </div>
            <flux:separator />

            @if($selectedTask)
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <strong class="text-zinc-700 dark:text-gray-300">Command:</strong>
                        <code class="block mt-1 p-2 bg-zinc-100 dark:bg-zinc-800 rounded text-xs font-mono">{{ $selectedTask->cmd }}</code>
                    </div>
                    <div>
                        <strong class="text-zinc-700 dark:text-gray-300">Last Run:</strong>
                        <div class="mt-1 text-gray-600 dark:text-gray-400">
                            {{ $selectedTask->last_run_at ? $selectedTask->last_run_at->format('M j, Y H:i:s') : 'Never' }}
                        </div>
                    </div>
                </div>

                @if($selectedTask->last_error)
                    <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded">
                        <strong class="text-red-800 dark:text-red-200">Error:</strong>
                        <pre class="text-sm text-red-700 dark:text-red-300 mt-1 whitespace-pre-wrap">{{ $selectedTask->last_error }}</pre>
                    </div>
                @endif

                <div class="bg-zinc-50 dark:bg-zinc-900/50 rounded border">
                    <div class="p-3 border-b border-gray-200 dark:border-zinc-700">
                        <strong class="text-zinc-700 dark:text-gray-300">Output Log:</strong>
                    </div>
                    <div class="p-3 max-h-96 overflow-auto">
                        @if($selectedTask->last_output)
                            <pre class="text-xs whitespace-pre-wrap text-gray-800 dark:text-gray-200 font-mono">{{ $selectedTask->last_output }}</pre>
                        @else
                            <div class="text-sm text-gray-500 dark:text-gray-400 italic">No output logged yet.</div>
                        @endif
                    </div>
                </div>

                @if(!empty($selectedTask->dry_run_summary))
                    @php($summary = $selectedTask->dry_run_summary)
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
                        <div class="p-3 border-b border-blue-200 dark:border-blue-700">
                            <strong class="text-blue-800 dark:text-blue-200">Last Dry-Run Summary:</strong>
                        </div>
                        <div class="p-3 grid grid-cols-4 gap-4 text-sm">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $summary['added'] ?? 0 }}</div>
                                <div class="text-gray-600 dark:text-gray-400">Added</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $summary['modified'] ?? 0 }}</div>
                                <div class="text-gray-600 dark:text-gray-400">Modified</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $summary['deleted'] ?? 0 }}</div>
                                <div class="text-gray-600 dark:text-gray-400">Deleted</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $summary['transferred'] ?? 0 }}</div>
                                <div class="text-gray-600 dark:text-gray-400">Transferred</div>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="text-sm text-gray-500">No task selected.</div>
            @endif

            <div class="flex justify-end">
                <flux:button variant="secondary" onclick="Flux.modal('task-log').close()">Close</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Folder Picker Modal -->
    <flux:modal class="min-w-3xl" position="top" name="folder-picker">
        <div class="space-y-4">
            <flux:heading size="xl" level="2">Choose a folder</flux:heading>
            <flux:separator />

            <div class="grid grid-cols-12 gap-2 items-center">
                <div class="col-span-7">
                    <flux:input size="sm" wire:model="folderCurrentPath" placeholder="/" />
                </div>
                <div class="col-span-2">
                    <flux:button icon="arrow-up"  size="sm" variant="outline" class="w-full" wire:click="folderUp">Up</flux:button>
                </div>
                <div class="col-span-1">
                    <flux:button icon="arrow-right"  size="sm" variant="outline" class="w-full" wire:click="folderJump" />
                </div>
                <div class="col-span-2">
                    <flux:button icon="server"  size="sm" variant="outline" class="w-full" wire:click="folderGoToVolumes">Volumes</flux:button>
                </div>
            </div>

            <div class="bg-zinc-50 dark:bg-zinc-900/50 rounded border max-h-96 overflow-auto">
                <div class="divide-y divide-gray-200 dark:divide-zinc-700">
                    @foreach($folderEntries as $entry)
                        <button
                            class="w-full flex items-center gap-3 py-3 px-3 text-left hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                            wire:click="folderInto('{{ $entry['path'] }}')"
                            wire:key="dir-{{ md5($entry['path']) }}"
                        >
                            <flux:icon name="folder" class="shrink-0 text-blue-600 dark:text-blue-400" />
                            <span class="font-medium">{{ $entry['name'] }}</span>
                            <span class="ml-auto text-xs text-gray-500 truncate font-mono">{{ $entry['path'] }}</span>
                        </button>
                    @endforeach
                    @if(empty($folderEntries))
                        <div class="text-sm text-gray-500 dark:text-gray-400 p-6 text-center">
                            <flux:icon name="folder-open" class="mx-auto w-8 h-8 mb-2 opacity-50" />
                            No sub-folders found in this directory.
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-5 gap-3">
                <flux:button class="col-span-3 w-full" variant="primary" icon="check" wire:click="folderUseCurrent">
                    Use this folder
                </flux:button>
                <flux:button class="col-span-2 w-full" variant="filled" icon="x-mark" wire:click="folderCancel">
                    Cancel
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>
