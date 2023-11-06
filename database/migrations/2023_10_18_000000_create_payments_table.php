<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('parapos_code')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('reference_id')->nullable();
            $table->string('response_hash', 36)->nullable();
            $table->char('last_four', 4)->nullable();
            $table->char('currency_code', 3)->default('TRY');
            $table->unsignedTinyInteger('installment')->default(1);
            $table->string('card_code', 100)->nullable();
            $table->string('bank_code', 100)->nullable();
            $table->tinyInteger('is_foreign_card')->default(0);
            $table->decimal('ratio')->default(0.00);
            $table->decimal('amount', 11, 2)->unsigned()->default(0.00);
            $table->string('name', 50)->nullable();
            $table->string('bin', 8)->nullable();
            $table->unsignedInteger('foreign_id_1')->nullable();
            $table->unsignedInteger('foreign_id_2')->nullable();
            $table->unsignedInteger('foreign_id_3')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
