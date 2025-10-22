<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = ['name', 'description'];

    // CASTS
    protected $casts = [
        'name' => 'string',
        'description' => 'string',
    ];

    // RELATIONSHIPS
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
