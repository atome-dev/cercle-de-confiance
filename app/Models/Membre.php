<?php

namespace App\Models;

use Database\Factories\MembreFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membre extends Model
{
    /** @use HasFactory<MembreFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nom',
        'titre',
        'role',
        'photo',
        'courriel',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'photo_url',
    ];

    /**
     * The public URL of the member's photo, stored in storage/membres.
     */
    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->photo ? asset('storage/membres/'.$this->photo) : null,
        );
    }
}
