<?php

namespace App\Enums;

enum SchoolClass: string
{
    case JeParc = 'je_parc';
    case JePommier = 'je_pommier';
    case Classe1 = 'classe_1';
    case Classe2 = 'classe_2';
    case Classe3 = 'classe_3';
    case Classe4 = 'classe_4';
    case Classe5 = 'classe_5';
    case Classe6 = 'classe_6';
    case Classe7 = 'classe_7';
    case Classe8 = 'classe_8';
    case Classe9 = 'classe_9';
    case Classe10 = 'classe_10';
    case Classe11 = 'classe_11';
    case Classe12 = 'classe_12';

    public function label(): string
    {
        return match ($this) {
            self::JeParc => 'JE Parc',
            self::JePommier => 'JE Pommier',
            self::Classe1 => '1ère classe',
            self::Classe2 => '2ème classe',
            self::Classe3 => '3ème classe',
            self::Classe4 => '4ème classe',
            self::Classe5 => '5ème classe',
            self::Classe6 => '6ème classe',
            self::Classe7 => '7ème classe',
            self::Classe8 => '8ème classe',
            self::Classe9 => '9ème classe',
            self::Classe10 => '10ème classe',
            self::Classe11 => '11ème classe',
            self::Classe12 => '12ème classe',
        };
    }

    public function section(): Section
    {
        return match ($this) {
            self::JeParc, self::JePommier => Section::JardinEnfants,
            self::Classe1, self::Classe2, self::Classe3, self::Classe4, self::Classe5 => Section::Elementaire,
            self::Classe6, self::Classe7, self::Classe8, self::Classe9 => Section::College,
            self::Classe10, self::Classe11, self::Classe12 => Section::Lycee,
        };
    }

    /**
     * @return list<self>
     */
    public static function forSection(Section $section): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $classe) => $classe->section() === $section
        ));
    }
}
