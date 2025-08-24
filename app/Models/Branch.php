<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'token' => 'string',
    ];
}