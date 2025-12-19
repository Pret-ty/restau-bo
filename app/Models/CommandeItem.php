<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="CommandeItem",
 *      required={"commande_id", "itemable_id", "itemable_type", "quantite", "prix_unitaire"},
 *      @OA\Property(property="id", type="integer", readOnly=true, example=1),
 *      @OA\Property(property="commande_id", type="integer", example=1),
 *      @OA\Property(property="itemable_id", type="integer", example=1),
 *      @OA\Property(property="itemable_type", type="string", example="App\\Models\\Plat"),
 *      @OA\Property(property="quantite", type="integer", example=2),
 *      @OA\Property(property="prix_unitaire", type="number", format="float", example=12.50),
 *      @OA\Property(property="created_at", type="string", format="date-time"),
 *      @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CommandeItem extends Model
{
    protected $fillable = [
        'commande_id', 
        'itemable_id', 
        'itemable_type', 
        'quantite', 
        'prix_unitaire'
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function itemable()
    {
        return $this->morphTo();
    }
}
