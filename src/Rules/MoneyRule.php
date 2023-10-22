<?php

namespace MedyaT\Parapos\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class MoneyRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        if ((is_string($value) || is_numeric($value)) && $this->isMoney((string) $value)) {
            return;
        }

        $fail(':attribute alanı için para formatında bir değer giriniz.');

    }

    private function isMoney(string $value): bool
    {
        return (bool) preg_match("/^\d+(\.\d{1,2})?$/", $value);
    }
}
