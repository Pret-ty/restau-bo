<?php

namespace App\Policies;

use App\Models\Commande;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommandePolicy
{
    public function viewAny(User $user): bool
    {
        // Scope handles visibility, but good to check role
        return $user->hasRole(['ADMIN', 'SERVEUR', 'CUISINIER', 'CAISSIER']);
    }

    public function view(User $user, Commande $commande): bool
    {
        if ($user->hasRole('CLIENT')) return true; 
        return $user->hasRole(['ADMIN', 'SERVEUR', 'CUISINIER', 'CAISSIER']) 
            && $user->restaurant_id === $commande->table?->restaurant_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['SERVEUR', 'CLIENT', 'ADMIN']);
    }

    public function update(User $user, Commande $commande): bool
    {
        return $user->hasRole(['SERVEUR', 'CUISINIER', 'CAISSIER', 'ADMIN'])
            && $user->restaurant_id === $commande->table?->restaurant_id;
    }

    public function delete(User $user, Commande $commande): bool
    {
        return $user->hasRole('ADMIN')
            && $user->restaurant_id === $commande->table?->restaurant_id;
    }
}
