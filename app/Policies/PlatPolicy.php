<?php

namespace App\Policies;

use App\Models\Plat;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PlatPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Read accessible to all
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Plat $plat): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('ADMIN_RESTAURANT') || $user->ownedRestaurants()->exists();
    }

    public function update(User $user, Plat $plat): bool
    {
        // Access restaurant via category
        $restaurant = $plat->categorie->restaurant;
        return $user->hasRole('ADMIN_RESTAURANT') && 
               ($user->restaurant_id === $restaurant->id || $user->id === $restaurant->proprietaire_id);
    }

    public function delete(User $user, Plat $plat): bool
    {
        $restaurant = $plat->categorie->restaurant;
        return $user->hasRole('ADMIN_RESTAURANT') && 
               ($user->restaurant_id === $restaurant->id || $user->id === $restaurant->proprietaire_id);
    }

}
