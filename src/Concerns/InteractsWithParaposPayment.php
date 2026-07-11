<?php

declare(strict_types=1);

namespace MedyaT\Parapos\Concerns;

/**
 * Shared behaviour every host payment model needs so the package can operate
 * on it regardless of the primary-key type (bigint or uuid). The host model
 * only needs to `use` this trait and implement {@see \MedyaT\Parapos\Contracts\PaymentStatus}.
 */
trait InteractsWithParaposPayment
{
    private const CHARACTERS = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    /**
     * Eloquent boots this automatically (boot{TraitName}). Fills a unique
     * response_hash used as the public payment reference / callback key.
     */
    public static function bootInteractsWithParaposPayment(): void
    {
        static::creating(function ($model): void {
            if (empty($model->response_hash)) {
                $model->response_hash = $model->generate_random_string();
            }
        });
    }

    public function generate_random_string(int $length = 20): string
    {
        $charactersLength = strlen(self::CHARACTERS);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= self::CHARACTERS[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
