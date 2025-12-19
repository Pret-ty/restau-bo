<?php

namespace App\Policies;

use App\Models\Table;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TablePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Table $table): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('ADMIN_RESTAURANT');
    }

    public function update(User $user, Table $table): bool
    {
        return $user->hasRole('ADMIN_RESTAURANT') && 
               ($user->restaurant_id === $table->restaurant_id || $user->id === $table->restaurant->proprietaire_id);
    }

    public function delete(User $user, Table $table): bool
    {
        return $user->hasRole('ADMIN_RESTAURANT') && 
               ($user->restaurant_id === $table->restaurant_id || $user->id === $table->restaurant->proprietaire_id);
    }
}
