<?php

namespace App\Livewire;

use App\Models\TaskRun;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;

class History extends Component
{
    use WithPagination;

    public ?TaskRun $selectedRun = null;

    public ?string $logContent = null;

    public function mount(): void
    {
        //
    }

    public function viewLog(int $runId): void
    {
        $this->selectedRun = TaskRun::find($runId);

        if ($this->selectedRun && $this->selectedRun->log_file_path && file_exists($this->selectedRun->log_file_path)) {
            $this->logContent = file_get_contents($this->selectedRun->log_file_path);
        } else {
            $this->logContent = 'Log file not found or no longer available.';
        }

        Flux::modal('log-viewer')->show();
    }

    public function closeLogModal(): void
    {
        $this->selectedRun = null;
        $this->logContent = null;
        Flux::modal('log-viewer')->close();
    }

    public function render()
    {
        $taskRuns = TaskRun::with('task')
            ->orderBy('started_at', 'desc')
            ->paginate(20);

        return view('livewire.history', [
            'taskRuns' => $taskRuns,
        ]);
    }
}
