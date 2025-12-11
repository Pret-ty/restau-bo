<?php

namespace App\Enums;

enum RoleEnum: string
{
    //
    case CLIENT = 'CLIENT';
    case CUISINIER = 'CUISINIER';
    case CAISSIER = 'CAISSIER';
    case SERVEUR = 'SERVEUR';
    case ADMIN_RESTAURANT = 'ADMIN_RESTAURANT';
}


