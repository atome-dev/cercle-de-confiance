<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Emoji previously typed by administrators, mapped to their Heroicons replacement.
     *
     * Matching happens in PHP: MySQL's utf8mb4 collations treat every emoji as
     * equal, so a WHERE on the emoji would convert all of them to the same icon.
     *
     * @var array<string, string>
     */
    private const array EMOJI_TO_ICON = [
        '🤝' => 'hand-raised',
        '⚖️' => 'scale',
        '⚖' => 'scale',
        '💬' => 'chat-bubble-left-right',
        '🔒' => 'lock-closed',
        '❤️' => 'heart',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (DB::table('cartouches')->get(['id', 'icone']) as $cartouche) {
            if (in_array($cartouche->icone, self::EMOJI_TO_ICON, true)) {
                continue;
            }

            DB::table('cartouches')
                ->where('id', $cartouche->id)
                ->update(['icone' => self::EMOJI_TO_ICON[$cartouche->icone] ?? 'heart']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $iconToEmoji = [
            'hand-raised' => '🤝',
            'scale' => '⚖️',
            'chat-bubble-left-right' => '💬',
            'lock-closed' => '🔒',
            'heart' => '❤️',
        ];

        foreach (DB::table('cartouches')->get(['id', 'icone']) as $cartouche) {
            DB::table('cartouches')
                ->where('id', $cartouche->id)
                ->update(['icone' => $iconToEmoji[$cartouche->icone] ?? $cartouche->icone]);
        }
    }
};
