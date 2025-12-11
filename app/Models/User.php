<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\RoleEnum;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'email',
        'password',
        'role',
        'restaurant_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'role' => RoleEnum::class,
        'email_verified_at' => 'datetime',
    ];

    public function isRole(RoleEnum $role): bool
    {
        return $this->role === $role;
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
