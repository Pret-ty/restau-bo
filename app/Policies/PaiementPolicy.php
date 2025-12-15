<?php

namespace App\Policies;

use App\Models\Paiement;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaiementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['ADMIN_RESTAURANT', 'CAISSIER']); 
    }

    public function view(User $user, Paiement $paiement): bool
    {
         return $user->hasRole(['ADMIN_RESTAURANT', 'CAISSIER'])
            && $user->restaurant_id === $paiement->commande?->table?->restaurant_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['SERVEUR', 'CLIENT', 'ADMIN_RESTAURANT']); 
    }

    public function update(User $user, Paiement $paiement): bool
    {
        // Validation by Caissier or Admin
        return $user->hasRole(['CAISSIER', 'ADMIN_RESTAURANT'])
            && $user->restaurant_id === $paiement->commande?->table?->restaurant_id;
    }

    public function delete(User $user, Paiement $paiement): bool
    {
        return $user->hasRole('ADMIN_RESTAURANT')
            && $user->restaurant_id === $paiement->commande?->table?->restaurant_id;
    }
}
