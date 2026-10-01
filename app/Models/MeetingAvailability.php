<?php

namespace App\Models;

use App\Enums\AvailabilityMode;
use Database\Factories\MeetingAvailabilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One 30-minute slot during which a member is available for a meeting.
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon $starts_at
 * @property AvailabilityMode $mode
 */
class MeetingAvailability extends Model
{
    /** @use HasFactory<MeetingAvailabilityFactory> */
    use HasFactory;

    public const string FIRST_SLOT = '08:00';

    public const string DAY_ENDS_AT = '22:00';

    public const int SLOT_MINUTES = 30;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'starts_at',
        'mode',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'mode' => AvailabilityMode::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Start times of every slot of a day, from 08:00 to 21:30.
     *
     * @return list<string>
     */
    public static function slotTimes(): array
    {
        $times = [];
        $time = Carbon::createFromFormat('H:i', self::FIRST_SLOT);
        $end = Carbon::createFromFormat('H:i', self::DAY_ENDS_AT);

        while ($time->lt($end)) {
            $times[] = $time->format('H:i');
            $time->addMinutes(self::SLOT_MINUTES);
        }

        return $times;
    }
}
