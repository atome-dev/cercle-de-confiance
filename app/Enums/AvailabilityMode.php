<?php

namespace App\Enums;

/**
 * How a member can attend during one of their availability slots. Being unavailable is not a
 * mode: it is the absence of a `MeetingAvailability` row.
 */
enum AvailabilityMode: string
{
    case InPerson = 'presentiel';
    case Remote = 'distanciel';

    public function label(): string
    {
        return match ($this) {
            self::InPerson => 'Présentiel',
            self::Remote => 'Distanciel',
        };
    }
}
