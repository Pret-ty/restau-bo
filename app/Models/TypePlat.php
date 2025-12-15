<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="TypePlat",
 *      required={"nom", "restaurant_id"},
 *      @OA\Property(property="id", type="integer", readOnly=true, example=1),
 *      @OA\Property(property="nom", type="string", example="Entrée"),
 *      @OA\Property(property="restaurant_id", type="integer", example=1),
 *      @OA\Property(property="created_at", type="string", format="date-time"),
 *      @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class TypePlat extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\RestaurantScope);
    }
    protected $fillable = ['nom', 'restaurant_id'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function plats()
    {
        return $this->hasMany(Plat::class);
    }
}
