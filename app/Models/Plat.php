<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="Plat",
 *      required={"nom", "prix", "categorie_id"},
 *      @OA\Property(property="id", type="integer", readOnly=true, example=1),
 *      @OA\Property(property="nom", type="string", example="Burger Classique"),
 *      @OA\Property(property="description", type="string", example="Boeuf, cheddar, salade, tomate"),
 *      @OA\Property(property="prix", type="number", format="float", example=12.50),
 *      @OA\Property(property="image", type="string", nullable=true),
 *      @OA\Property(property="categorie_id", type="integer", example=1),
 *      @OA\Property(property="created_at", type="string", format="date-time"),
 *      @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Plat extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\RestaurantScope);
    }
    protected $fillable = [
        'nom',
        'description',
        'prix',
        'image',
        'categorie_id',
        'type_plat_id'
    ];

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function typePlat()
    {
        return $this->belongsTo(TypePlat::class);
    }

    public function commandeItems()
    {
        return $this->morphMany(CommandeItem::class, 'itemable');
    }
}
