<?php

namespace App\Enums;

enum Section: string
{
    case JardinEnfants = 'jardin_enfants';
    case Elementaire = 'elementaire';
    case College = 'college';
    case Lycee = 'lycee';

    public function label(): string
    {
        return match ($this) {
            self::JardinEnfants => "Jardin d'enfants",
            self::Elementaire => 'Élémentaire',
            self::College => 'Collège',
            self::Lycee => 'Lycée',
        };
    }
}
