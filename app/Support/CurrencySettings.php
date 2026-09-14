<?php

namespace App\Support;

class CurrencySettings
{
    // Starting reference values; the dashboard controls the hotel's display rates.
    // USD: NRB published selling rate, 11 September 2026. INR: NPR/INR parity.
    public static function definitions(): array
    {
        return [
            ['key' => 'currency_npr_per_inr', 'value' => '1.6000', 'type' => 'number', 'group' => 'currency', 'label' => '1 INR = how many NPR?', 'sort_order' => 0],
            ['key' => 'currency_npr_per_usd', 'value' => '153.0100', 'type' => 'number', 'group' => 'currency', 'label' => '1 USD = how many NPR?', 'sort_order' => 1],
        ];
    }

    public static function defaults(): array
    {
        return array_column(self::definitions(), 'value', 'key');
    }
}
