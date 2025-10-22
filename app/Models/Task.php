<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'profile_id',
        'name',
        'settings',
        'source',
        'destination',
        'cmd',
        'last_dry_run_summary',
        'last_run_log',
        'last_run_at',
        'status',
        'last_output',
        'last_error',
        'log_file',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function syncs(): HasMany
    {
        return $this->hasMany(Sync::class);
    }

    public function taskRuns(): HasMany
    {
        return $this->hasMany(TaskRun::class);
    }

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'last_dry_run_summary' => 'array',
            'last_run_at' => 'datetime',
        ];
    }
}
