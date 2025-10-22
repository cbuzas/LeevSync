<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskRun extends Model
{
    protected $fillable = [
        'task_id',
        'task_name',
        'source',
        'destination',
        'started_at',
        'completed_at',
        'status',
        'output',
        'log_file_path',
        'error_message',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
