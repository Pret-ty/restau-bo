<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="Commande",
 *      required={"table_id", "statut"},
 *      @OA\Property(property="id", type="integer", readOnly=true, example=1),
 *      @OA\Property(property="table_id", type="integer", example=1),
 *      @OA\Property(property="statut", type="string", example="en_attente"),
 *      @OA\Property(property="total", type="number", format="float", example=0.00),
 *      @OA\Property(property="created_at", type="string", format="date-time"),
 *      @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Commande extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\RestaurantScope);
    }
    protected $fillable = ['table_id', 'statut', 'total'];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function items()
    {
        return $this->hasMany(CommandeItem::class);
    }

    public function paiement()
    {
        return $this->hasOne(Paiement::class);
    }

    public function calculerTotal()
    {
        $this->total = $this->items->sum(function ($item) {
            return $item->prix_unitaire * $item->quantite;
        });
        $this->save();
        return $this->total;
    }
}
