<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sync extends Model
{
    protected $fillable = [
        'task_id',
        'executed_at',
        'alias',
        'log_file',
        'method',
        'status',
        'cmd',
        'logs',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    protected function casts(): array
    {
        return [
            'executed_at' => 'datetime',
            'logs' => 'array',
        ];
    }
}
