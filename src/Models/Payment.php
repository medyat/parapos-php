<?php

namespace MedyaT\Parapos\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $bin
 * @property float $amount
 * @property string $ip
 * @property string $response_hash
 * @property int $installment
 */
final class Payment extends Model
{
    public const PAYMENT_SUCCESS = 0;

    public const PAYMENT_WAITING = 1;

    public const PAYMENT_FAIL = 2;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'array',
        'amount' => 'decimal:2',
    ];

    protected $hidden = [
        'response_hash',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => self::PAYMENT_WAITING,
    ];

    /**
     * @var string
     */
    private const CHARACTERS = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    public static function boot(): void
    {

        self::bootTraits();

        self::creating(function ($model): void {
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
