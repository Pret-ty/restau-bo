<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="Paiement",
 *      required={"commande_id", "montant", "mode", "statut"},
 *      @OA\Property(property="id", type="integer", readOnly=true, example=1),
 *      @OA\Property(property="commande_id", type="integer", example=1),
 *      @OA\Property(property="montant", type="number", format="float", example=25.00),
 *      @OA\Property(property="mode", type="string", example="cash"),
 *      @OA\Property(property="statut", type="string", example="completed"),
 *      @OA\Property(property="created_at", type="string", format="date-time"),
 *      @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Paiement extends Model
{
    protected $fillable = ['commande_id', 'montant', 'mode', 'statut'];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }
}
