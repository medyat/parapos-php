<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use MedyaT\Parapos\Concerns\InteractsWithParaposPayment;
use MedyaT\Parapos\Contracts\PaymentStatus;

/**
 * A host-app payment model living in its own table, so a test can prove which
 * model the package resolved rather than relying on both classes happening to
 * infer the same `payments` table.
 */
class TestCustomPayment extends Model implements PaymentStatus
{
    use InteractsWithParaposPayment;

    protected $table = 'custom_payments';

    protected $guarded = ['id'];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => self::PAYMENT_PENDING,
    ];

    public static function createTable(): void
    {
        Schema::create('custom_payments', function ($table): void {
            $table->id();
            $table->string('parapos_code')->nullable();
            $table->string('request_code')->nullable();
            $table->string('response_code')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->string('response_hash', 36)->nullable();
            $table->unsignedTinyInteger('is_pre_auth')->default(0);
            $table->unsignedTinyInteger('auto_complete')->default(0);
            $table->string('result_message', 255)->nullable();

            /* The columns addPayment() writes, so a full payment round-trips here too. */
            $table->string('ip')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->unsignedInteger('installment')->default(1);
            $table->decimal('ratio', 10, 2)->default(0);
            $table->string('currency_code', 3)->nullable();
            $table->string('user_id')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('foreign_id_1')->nullable();
            $table->string('foreign_id_2')->nullable();
            $table->string('foreign_id_3')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }
}
