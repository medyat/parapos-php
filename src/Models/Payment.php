<?php

namespace MedyaT\Parapos\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $parapos_code
 * @property string $bin
 * @property float $amount
 * @property string $name
 * @property string $ip
 * @property string $response_hash
 * @property string $currency_code
 * @property string $last_four
 * @property int $installment
 * @property float $ratio
 * @property int $status
 * @property ?int $reference_id
 * @property ?int $foreign_id_1
 * @property ?int $foreign_id_2
 * @property ?int $foreign_id_3
 * @property ?int $user_id
 */
final class Payment extends Model
{
    public const PAYMENT_SUCCESS = 0;

    public const PAYMENT_PENDING = 1;

    public const PAYMENT_FAIL = 2;

    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected $hidden = [
        'response_hash',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => self::PAYMENT_PENDING,
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
