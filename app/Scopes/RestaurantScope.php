<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class RestaurantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();

            // If user is ADMIN_RESTAURANT or generic Employee, restrict to their restaurant
            // Adjust logic based on how you identify 'Global Admin' vs 'Restaurant User'
            // For now, assuming all BO users must belong to a restaurant to see data
            if ($user->restaurant_id) {
                 // Employee logic: restrict to assigned restaurant
                 if ($model instanceof \App\Models\Table || $model instanceof \App\Models\Categorie || $model instanceof \App\Models\Boisson || $model instanceof \App\Models\TypePlat) {
                     $builder->where('restaurant_id', $user->restaurant_id);
                 }
                 
                 // Plats are linked via Categorie
                 if ($model instanceof \App\Models\Plat) {
                     $builder->whereHas('categorie', function ($query) use ($user) {
                         $query->where('restaurant_id', $user->restaurant_id);
                     });
                 }

                 // Commandes are linked via Table
                 if ($model instanceof \App\Models\Commande) {
                     $builder->whereHas('table', function ($query) use ($user) {
                         $query->where('restaurant_id', $user->restaurant_id);
                     });
                 }
            } else {
                // Owner logic: restrict to owned restaurants
                $ownedRestaurantIds = \App\Models\Restaurant::where('proprietaire_id', $user->id)->pluck('id');
                
                if ($ownedRestaurantIds->isNotEmpty()) {
                    if ($model instanceof \App\Models\Table || $model instanceof \App\Models\Categorie || $model instanceof \App\Models\Boisson || $model instanceof \App\Models\TypePlat) {
                        $builder->whereIn('restaurant_id', $ownedRestaurantIds);
                    }

                    if ($model instanceof \App\Models\Plat) {
                        $builder->whereHas('categorie', function ($query) use ($ownedRestaurantIds) {
                            $query->whereIn('restaurant_id', $ownedRestaurantIds);
                        });
                    }

                    if ($model instanceof \App\Models\Commande) {
                         $builder->whereHas('table', function ($query) use ($ownedRestaurantIds) {
                             $query->whereIn('restaurant_id', $ownedRestaurantIds);
                         });
                    }
                }
            }
        }
    }
}
