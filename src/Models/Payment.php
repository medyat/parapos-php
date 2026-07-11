<?php

namespace MedyaT\Parapos\Models;

use Illuminate\Database\Eloquent\Model;
use MedyaT\Parapos\Concerns\InteractsWithParaposPayment;
use MedyaT\Parapos\Contracts\PaymentStatus;

/**
 * Default payment model (bigint primary key). Host apps may point
 * `config('parapos.model')` at their own Eloquent model instead — it only has
 * to `use InteractsWithParaposPayment` and `implements PaymentStatus`, so a
 * uuid-keyed / tenant-aware model works just as well.
 *
 * @property int|string $id
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
 * @property int $is_pre_auth
 * @property int $auto_complete
 * @property int|string|null $reference_id
 * @property int|string|null $foreign_id_1
 * @property int|string|null $foreign_id_2
 * @property int|string|null $foreign_id_3
 * @property int|string|null $user_id
 * @property ?string $request_code
 * @property ?string $response_code
 * @property ?string $description
 * @property ?string $result_message
 */
class Payment extends Model implements PaymentStatus
{
    use InteractsWithParaposPayment;

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
}
