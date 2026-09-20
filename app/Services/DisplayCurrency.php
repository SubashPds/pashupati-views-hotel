<?php

namespace App\Services;

use App\Models\Package;
use App\Models\SiteSetting;
use App\Support\CurrencySettings;

class DisplayCurrency
{
    public function __construct(public readonly string $code, private array $rates) {}

    public static function fromCode(string $code): self
    {
        $defaults = CurrencySettings::defaults();
        $settings = SiteSetting::whereIn('key', array_keys($defaults))->pluck('value', 'key')->all();
        $rates = ['NPR' => 1.0];
        foreach (['INR' => 'currency_npr_per_inr', 'USD' => 'currency_npr_per_usd'] as $currency => $key) {
            $value = $settings[$key] ?? $defaults[$key];
            $rates[$currency] = is_numeric($value) && (float) $value > 0 ? (float) $value : (float) $defaults[$key];
        }

        return new self($code, $rates);
    }

    public function amount(float|string $amount, string $source = 'NPR'): string
    {
        $converted = (float) $amount * $this->rates[$source] / $this->rates[$this->code];
        $decimals = $this->code === 'NPR' && round($converted, 2) === round($converted) ? 0 : 2;
        return number_format($converted, $decimals);
    }

    public function format(float|string $amount, string $source = 'NPR'): string
    {
        return $this->code.' '.$this->amount($amount, $source);
    }

    public function package(Package $package): string
    {
        return $this->label($package->price_label, $package->price_from);
    }

    public function label(?string $label, float|string|null $fallback = null): string
    {
        $label = trim($label ?? '');
        if ($label === '') return $fallback !== null ? 'From '.$this->format($fallback) : 'Contact for pricing';
        $number = '\d[\d,]*(?:\.\d+)?';
        $money = '/(?<currency>\bNPR\b|\bINR\b|\bUSD\b|\bRs\.?|रु\.?|रू\.?|\$|₹)\s*(?<amount>'.$number.')(?:\s*[-–—]\s*(?<end>'.$number.'))?/iu';
        $converted = preg_replace_callback($money, function ($match) {
            $source = match (strtoupper($match['currency'])) {
                'USD', '$' => 'USD', 'INR', '₹' => 'INR', default => 'NPR',
            };
            $price = $this->format(str_replace(',', '', $match['amount']), $source);
            if (!empty($match['end'])) $price .= '–'.$this->amount(str_replace(',', '', $match['end']), $source);
            return $price;
        }, $label, -1, $count);
        if ($count) return $converted;
        // Don't reinterpret explicit unsupported currencies or numbers describing durations/discounts.
        if (preg_match('/\p{Sc}|\b(?:EUR|GBP|AUD|CAD|JPY|CNY)\b/iu', $label)) return $label;
        $pattern = '/(?<prefix>\bfrom\s+|\bstarting\s+(?:at|from)\s+|^)(?<amount>'.$number.')(?![\d.,%])(?<range>\s*[-–—]\s*(?<end>'.$number.'))?(?=\s*(?:\/|$|per\b))/iu';
        return preg_replace_callback($pattern, function ($match) {
            $price = $match['prefix'].$this->format(str_replace(',', '', $match['amount']));
            if (!empty($match['end'])) $price .= '–'.$this->amount(str_replace(',', '', $match['end']));
            return $price;
        }, $label);
    }
}
