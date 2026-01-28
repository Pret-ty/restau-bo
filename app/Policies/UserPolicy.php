<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('ADMIN');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole('ADMIN') && $user->restaurant_id === $model->restaurant_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('ADMIN');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole('ADMIN') && $user->restaurant_id === $model->restaurant_id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('ADMIN') && $user->restaurant_id === $model->restaurant_id;
    }
}
