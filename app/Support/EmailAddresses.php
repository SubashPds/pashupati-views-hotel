<?php

namespace App\Support;

use Illuminate\Support\Facades\Validator;

class EmailAddresses
{
    public static function parse(?string $value): array
    {
        $addresses = preg_split('/[\s,;]+/u', trim($value ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_unique(array_map('strtolower', $addresses ?: [])));
    }

    public static function valid(?string $value): array
    {
        return array_values(array_filter(self::parse($value), fn ($address) => Validator::make(
            ['email' => $address], ['email' => 'required|email:rfc|max:254'],
        )->passes()));
    }
}
