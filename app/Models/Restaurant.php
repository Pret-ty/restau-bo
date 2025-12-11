<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    //
    protected $fillable = [
        'nom',
        'adresse',
        'telephone',
        'propriétaire_id'
    ];

    public function proprietaire() {
        return $this->belongsTo(Utilisateur::class, 'proprietaire_id');
    }

    public function employes() {
        return $this->hasMany(Utilisateur::class, 'restaurant_id');
    }

    public function tables(){ 
        return $this->hasMany(Table::class); 
    }

    public function categories(){ 
        return $this->hasMany(Categorie::class); 
    }
}
