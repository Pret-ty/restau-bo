<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Boisson;

class BoissonPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Boisson $boisson): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('ADMIN');
    }

    public function update(User $user, Boisson $boisson): bool
    {
        return $user->hasRole('ADMIN') && 
               ($user->restaurant_id === $boisson->restaurant_id || $user->id === $boisson->restaurant->proprietaire_id);
    }

    public function delete(User $user, Boisson $boisson): bool
    {
        return $user->hasRole('ADMIN') && 
               ($user->restaurant_id === $boisson->restaurant_id || $user->id === $boisson->restaurant->proprietaire_id);
    }
}
