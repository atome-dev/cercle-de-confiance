<?php

namespace App\Models;

use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon $held_on
 * @property string|null $starts_at
 * @property string $title
 * @property string|null $notes
 */
class Meeting extends Model
{
    /** @use HasFactory<MeetingFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'held_on',
        'starts_at',
        'title',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'held_on' => 'date',
        ];
    }

    /**
     * Members who attended the meeting.
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
