<?php

namespace App\Models;

use Database\Factories\CartoucheFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cartouche extends Model
{
    /** @use HasFactory<CartoucheFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'icone',
        'titre',
        'description',
    ];
}
