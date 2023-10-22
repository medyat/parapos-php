<?php

namespace MedyaT\Parapos\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use MedyaT\Parapos\Enums\Card;

final class CardRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        if (is_string($value) && Card::validCreditCard($value)) {
            return;
        }

        $fail('Geçerli bir kredi kartı numarası girmelisiniz.');
    }
}
