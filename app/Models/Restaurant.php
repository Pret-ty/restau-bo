<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="Restaurant",
 *      required={"nom", "proprietaire_id"},
 *      @OA\Property(property="id", type="integer", readOnly=true, example=1),
 *      @OA\Property(property="nom", type="string", example="La Belle Etoile"),
 *      @OA\Property(property="adresse", type="string", nullable=true, example="123 Rue de Paris"),
 *      @OA\Property(property="telephone", type="string", nullable=true, example="0102030405"),
 *      @OA\Property(property="proprietaire_id", type="integer", example=1),
 *      @OA\Property(property="created_at", type="string", format="date-time"),
 *      @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Restaurant extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    //
    protected $fillable = [
        'nom',
        'adresse',
        'telephone',
        'proprietaire_id'
    ];

    public function proprietaire() {
        return $this->belongsTo(User::class, 'proprietaire_id');
    }

    public function employes() {
        return $this->hasMany(User::class, 'restaurant_id');
    }

    public function tables(){ 
        return $this->hasMany(Table::class); 
    }

    public function categories(){ 
        return $this->hasMany(Categorie::class); 
    }
}
