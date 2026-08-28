<?php

namespace App\Services;

use App\Models\Thread;
use Illuminate\Support\Str;

class ThreadCodeGenerator
{
    protected const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // sans O/0/I/1 ambigus

    public function generateThreadCode(): string
    {
        do {
            $code = $this->randomPart();
        } while (Thread::where('code', $code)->exists());

        return $code;
    }

    public function generatePrivateKey(): string
    {
        return $this->randomPart();
    }

    protected function randomPart(): string
    {
        $alphabet = self::ALPHABET;

        return collect(range(1, 4))
            ->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])
            ->implode('');
    }

    public function fullCode(string $threadCode, string $privateKey): string
    {
        return "{$threadCode}-{$privateKey}";
    }

    public function parseFullCode(string $fullCode): ?array
    {
        if (! preg_match('/^([A-Z0-9]{4})-([A-Z0-9]{4})$/', trim(Str::upper($fullCode)), $matches)) {
            return null;
        }

        return [$matches[1], $matches[2]];
    }
}
