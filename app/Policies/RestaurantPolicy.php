<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RestaurantPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Authenticated users can see restaurants (filtered by Scope usually)
    }

    public function view(User $user, Restaurant $restaurant): bool
    {
        return true; // Public or Internal read access
    }

    public function create(User $user): bool
    {
        return true; 
    }

    public function update(User $user, Restaurant $restaurant): bool
    {
        // Only Owner (ADMIN_RESTAURANT and owner of this restaurant)
        return $user->hasRole('ADMIN_RESTAURANT') && $user->id === $restaurant->proprietaire_id;
    }

    public function delete(User $user, Restaurant $restaurant): bool
    {
        return $user->hasRole('ADMIN_RESTAURANT') && $user->id === $restaurant->proprietaire_id;
    }

    // Other methods remain false or typically not used yet
}
