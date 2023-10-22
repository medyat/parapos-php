<?php

namespace MedyaT\Parapos\Enums;

enum Card: string
{
    case AMEX = 'amex';

    case MASTERCARD = 'mastercard';

    case MAESTRO = 'maestro';

    case VISA = 'visa';

    case TROY = 'troy';

    case DISCOVER = 'discover';

    case JCB = 'jcb';

    case UNIONPAY = 'unionpay';

    case VISA_ELECTRON = 'visa_electron';

    case OTHER = 'other';

    public const CARDS = [
        self::AMEX->value => [
            'type' => self::AMEX->value,
            'pattern' => '/^3[47]/',
            'format' => '/(\d{1,4})(\d{1,6})?(\d{1,5})?/',
            'length' => [15],
            'cvcLength' => [3, 4],
            'luhn' => true,
        ],
        self::MASTERCARD->value => [
            'type' => self::MASTERCARD->value,
            'pattern' => '/^(5[0-5]|(222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720))/', // 2221-2720, 51-55
            'length' => [16],
            'cvcLength' => [3],
            'luhn' => true,
        ],
        self::MAESTRO->value => [
            'type' => self::MAESTRO->value,
            'pattern' => '/^(5(018|0[23]|[68])|6(3|7))/',
            'length' => [12, 13, 14, 15, 16, 17, 18, 19],
            'cvcLength' => [3],
            'luhn' => true,
        ],
        self::VISA->value => [
            'type' => self::VISA->value,
            'pattern' => '/^4/',
            'length' => [13, 16, 19],
            'cvcLength' => [3],
            'luhn' => true,
        ],
        self::TROY->value => [
            'type' => self::TROY->value,
            'pattern' => '/^9792/',
            'length' => [16],
            'cvcLength' => [3],
            'luhn' => true,
        ],
        self::VISA_ELECTRON->value => [
            'type' => self::VISA_ELECTRON->value,
            'pattern' => '/^4(026|17500|405|508|844|91[37])/',
            'length' => [16],
            'cvcLength' => [3],
            'luhn' => true,
        ],
        self::DISCOVER->value => [
            'type' => self::DISCOVER->value,
            'pattern' => '/^6([045]|22)/',
            'length' => [16],
            'cvcLength' => [3],
            'luhn' => true,
        ],
        self::UNIONPAY->value => [
            'type' => self::UNIONPAY->value,
            'pattern' => '/^(62|88)/',
            'length' => [16, 17, 18, 19],
            'cvcLength' => [3],
            'luhn' => false,
        ],
        self::JCB->value => [
            'type' => self::JCB->value,
            'pattern' => '/^35/',
            'length' => [16],
            'cvcLength' => [3],
            'luhn' => true,
        ],
        self::OTHER->value => [
            'type' => self::OTHER->value,
            'pattern' => '/undefined/',
            'length' => [16],
            'cvcLength' => [3],
            'luhn' => false,
        ],
    ];

    /**
     * @param  string[]  $types
     */
    public static function validCreditCard(string $number, array $types = []): bool
    {
        $types = self::wrap($types);

        // Strip non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);

        // if nunmber is empty return false
        if (empty($number)) {
            return false;
        }

        if ($types === []) {
            $types[] = self::creditCardType($number);
        }

        foreach ($types as $type) {
            if (! isset(self::CARDS[$type])) {
                continue;
            }
            if (! self::validCard($number, $type)) {
                continue;
            }

            return true;
        }

        return false;
    }

    public static function validCvc(string $cvc, string $type): bool
    {
        return ctype_digit($cvc) && array_key_exists($type, self::CARDS) && self::validCvcLength($cvc, $type);
    }

    public static function validDate(string $year, string $month): bool
    {
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);

        if (! preg_match('/^20\d\d$/', $year)) {
            return false;
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', $month)) {
            return false;
        }

        // past date
        return $year >= date('Y') && ! ($year == date('Y') && $month < date('m'));
    }

    protected static function creditCardType(string $number): string
    {
        foreach (self::CARDS as $type => $card) {
            if (preg_match($card['pattern'], $number)) {
                return $type;
            }
        }

        return self::OTHER->value;
    }

    protected static function validCard(string $number, string $type): bool
    {
        if (! self::validPattern($number, $type)) {
            return false;
        }
        if (! self::validLength($number, $type)) {
            return false;
        }

        return self::validLuhn($number, $type);
    }

    protected static function validPattern(string $number, string $type): bool
    {
        return preg_match(self::CARDS[$type]['pattern'], $number) === 1;
    }

    protected static function validLength(string $number, string $type): bool
    {
        return in_array(strlen($number), self::CARDS[$type]['length']);
    }

    protected static function validCvcLength(string $cvc, string $type): bool
    {
        return in_array(strlen($cvc), self::CARDS[$type]['cvcLength']);
    }

    protected static function validLuhn(string $number, string $type): bool
    {
        if (! self::CARDS[$type]['luhn']) {
            return true;
        }

        return self::luhnCheck($number);
    }

    protected static function luhnCheck(string $number): bool
    {
        $checksum = 0;
        for ($i = (2 - (strlen($number) % 2)); $i <= strlen($number); $i += 2) {
            $checksum += (int) substr($number, $i - 1, 1);
        }

        // Analyze odd digits in even length strings or even digits in odd length strings.
        for ($i = (strlen($number) % 2) + 1; $i < strlen($number); $i += 2) {
            $digit = (int) substr($number, $i - 1, 1);
            $digit *= 2;
            if ($digit < 10) {
                $checksum += $digit;
            } else {
                $checksum += ($digit - 9);
            }
        }

        return ($checksum % 10) == 0;
    }

    /**
     * @param  string|string[]  $value
     * @return string[]
     */
    public static function wrap(string|array $value): array
    {

        return is_array($value) ? $value : [$value];
    }
}
