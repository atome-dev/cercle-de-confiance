<?php

namespace App\Enums;

enum Role: string
{
    case Administrateur = 'administrateur';
    case Parent = 'parent';
    case Professeur = 'professeur';
}
