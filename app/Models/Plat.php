<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plat extends Model
{
    //
    public function restaurant(){ 
        return $this->belongsTo(Restaurant::class); 
    }

    public function commandes(){ 
        return $this->hasMany(Commande::class); 
    }

}
