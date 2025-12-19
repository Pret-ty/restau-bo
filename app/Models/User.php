<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @OA\Schema(
 *      schema="User",
 *      required={"nom", "email"},
 *      @OA\Property(property="id", type="integer", readOnly=true, example=1),
 *      @OA\Property(property="nom", type="string", example="Jean Dupont"),
 *      @OA\Property(property="email", type="string", format="email", example="jean@example.com"),
 *      @OA\Property(property="restaurant_id", type="integer", nullable=true, example=1),
 *      @OA\Property(property="created_at", type="string", format="date-time"),
 *      @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'nom',
        'email',
        'password',
        'restaurant_id'
    ];

    protected $guard_name = 'web';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Check if user has a specific role (using Spatie)
     */
    public function isRole(string $roleName): bool
    {
        return $this->hasRole($roleName);
    }

    /**
     * Check if user is a restaurant manager
     */
    public function isManager(): bool
    {
        return $this->hasRole('ADMIN_RESTAURANT');
    }

    /**
     * Restaurant where user works
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Restaurant owned by this user
     */
    public function ownedRestaurant()
    {
        return $this->hasOne(Restaurant::class, 'proprietaire_id');
    }
}
