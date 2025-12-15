<?php

namespace App\Policies;

use App\Models\Categorie;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoriePolicy
{
    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, Categorie $categorie): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('ADMIN_RESTAURANT');
    }

    public function update(User $user, Categorie $categorie): bool
    {
        // Must be Admin of the restaurant linked to category
        return $user->hasRole('ADMIN_RESTAURANT') && $user->restaurant_id === $categorie->restaurant_id;
    }

    public function delete(User $user, Categorie $categorie): bool
    {
        return $user->hasRole('ADMIN_RESTAURANT') && $user->restaurant_id === $categorie->restaurant_id;
    }
}
