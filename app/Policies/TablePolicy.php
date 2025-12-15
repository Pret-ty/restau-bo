<?php

namespace App\Policies;

use App\Models\Table;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TablePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['ADMIN_RESTAURANT', 'SERVEUR', 'CLIENT']);
    }

    public function view(User $user, Table $table): bool
    {
        return $user->hasRole(['ADMIN_RESTAURANT', 'SERVEUR', 'CLIENT']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('ADMIN_RESTAURANT');
    }

    public function update(User $user, Table $table): bool
    {
        return $user->hasRole('ADMIN_RESTAURANT');
    }

    public function delete(User $user, Table $table): bool
    {
        return $user->hasRole('ADMIN_RESTAURANT');
    }
}
