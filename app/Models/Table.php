<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="Table",
 *      required={"numero", "restaurant_id"},
 *      @OA\Property(property="id", type="integer", readOnly=true, example=1),
 *      @OA\Property(property="numero", type="string", example="Table 1"),
 *      @OA\Property(property="qr_code_url", type="string", nullable=true, example="https://example.com/qr/1"),
 *      @OA\Property(property="restaurant_id", type="integer", example=1),
 *      @OA\Property(property="created_at", type="string", format="date-time"),
 *      @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Table extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\RestaurantScope);
    }
    protected $fillable = ['numero', 'qr_code_url', 'restaurant_id'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }
}
