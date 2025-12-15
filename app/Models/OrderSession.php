<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderSession extends Model
{
    protected $fillable = ['commande_id', 'token'];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }
}
