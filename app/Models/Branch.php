<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'token',
        'manager_email',
        'manager_password',
        'admin_email',
        'admin_password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'token' => 'string',
        'manager_email' => 'string',
        'manager_password' => 'string',
        'admin_email' => 'string',
        'admin_password' => 'string',
    ];
}