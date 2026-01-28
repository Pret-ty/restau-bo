<?php

namespace App\Policies;

use App\Models\Paiement;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaiementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['ADMIN', 'CAISSIER']); 
    }

    public function view(User $user, Paiement $paiement): bool
    {
         return $user->hasRole(['ADMIN', 'CAISSIER'])
            && $user->restaurant_id === $paiement->commande?->table?->restaurant_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['SERVEUR', 'CLIENT', 'ADMIN']); 
    }

    public function update(User $user, Paiement $paiement): bool
    {
        // Validation by Caissier or Admin
        return $user->hasRole(['CAISSIER', 'ADMIN'])
            && $user->restaurant_id === $paiement->commande?->table?->restaurant_id;
    }

    public function delete(User $user, Paiement $paiement): bool
    {
        return $user->hasRole('ADMIN')
            && $user->restaurant_id === $paiement->commande?->table?->restaurant_id;
    }
}
