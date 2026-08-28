<?php

namespace App\Services;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;

class ThreadEncryptionService
{
    public function generateThreadKey(): string
    {
        return random_bytes(32);
    }

    /** Clé dérivée du code privé, utilisée comme clé d'enveloppe (pas comme thread_key elle-même). */
    protected function envelopeKeyFromCode(string $threadCode, string $privateKey): string
    {
        return hash_hkdf('sha256', $privateKey, 32, $threadCode);
    }

    public function sealForApp(string $threadKey): string
    {
        return Crypt::encryptString(base64_encode($threadKey));
    }

    public function openAppEnvelope(string $envelope): string
    {
        return base64_decode(Crypt::decryptString($envelope));
    }

    public function sealForAnon(string $threadKey, string $threadCode, string $privateKey): string
    {
        $envelopeKey = $this->envelopeKeyFromCode($threadCode, $privateKey);

        return (new Encrypter($envelopeKey, 'AES-256-CBC'))
            ->encryptString(base64_encode($threadKey));
    }

    public function openAnonEnvelope(string $envelope, string $threadCode, string $privateKey): string
    {
        $envelopeKey = $this->envelopeKeyFromCode($threadCode, $privateKey);

        return base64_decode(
            (new Encrypter($envelopeKey, 'AES-256-CBC'))->decryptString($envelope)
        );
    }

    /**
     * @return array{ciphertext: string, iv: string, tag: string}
     */
    public function encryptMessage(string $plainText, string $threadKey): array
    {
        $iv = random_bytes(16);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plainText,
            'aes-256-gcm',
            $threadKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return [
            'ciphertext' => base64_encode($ciphertext),
            'iv' => bin2hex($iv),
            'tag' => bin2hex($tag),
        ];
    }

    public function decryptMessage(string $cipherText, string $iv, string $tag, string $threadKey): string
    {
        $plainText = openssl_decrypt(
            base64_decode($cipherText),
            'aes-256-gcm',
            $threadKey,
            OPENSSL_RAW_DATA,
            hex2bin($iv),
            hex2bin($tag)
        );

        if ($plainText === false) {
            throw new \RuntimeException('Impossible de déchiffrer le message.');
        }

        return $plainText;
    }
}
