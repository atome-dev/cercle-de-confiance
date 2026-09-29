<?php

namespace App\Enums;

/**
 * Each case's value is the Heroicons name rendered by `<flux:icon>`.
 */
enum CartoucheIcone: string
{
    case Bienveillance = 'heart';
    case Confidentialite = 'lock-closed';
    case Ecoute = 'chat-bubble-left-right';
    case Neutralite = 'scale';
    case Respect = 'hand-raised';

    public function label(): string
    {
        return match ($this) {
            self::Bienveillance => 'Bienveillance',
            self::Confidentialite => 'Confidentialité',
            self::Ecoute => 'Écoute',
            self::Neutralite => 'Neutralité',
            self::Respect => 'Respect',
        };
    }
}
