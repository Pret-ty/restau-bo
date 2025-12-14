<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'restaurant_id' => $this->restaurant_id,
            'roles' => $this->roles->pluck('name'),
            'restaurant' => new RestaurantResource($this->whenLoaded('restaurant')),
            'owned_restaurant' => new RestaurantResource($this->whenLoaded('ownedRestaurant')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
