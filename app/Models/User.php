<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'employee_id','first_name','last_name','nickname',
        'department','phone','email','password','role','is_active'
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }
}
