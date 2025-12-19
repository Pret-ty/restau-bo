<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="Boisson",
 *      required={"nom", "prix", "restaurant_id"},
 *      @OA\Property(property="id", type="integer", readOnly=true, example=1),
 *      @OA\Property(property="nom", type="string", example="Coca Cola"),
 *      @OA\Property(property="description", type="string", nullable=true, example="Boisson gazeuse"),
 *      @OA\Property(property="prix", type="number", format="float", example=2.50),
 *      @OA\Property(property="volume", type="string", nullable=true, example="33cl"),
 *      @OA\Property(property="image", type="string", nullable=true),
 *      @OA\Property(property="restaurant_id", type="integer", example=1),
 *      @OA\Property(property="created_at", type="string", format="date-time"),
 *      @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Boisson extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\RestaurantScope);
    }
    protected $fillable = [
        'nom', 
        'description', 
        'prix', 
        'volume', 
        'image', 
        'restaurant_id'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function commandeItems()
    {
        return $this->morphMany(CommandeItem::class, 'itemable');
    }
}
