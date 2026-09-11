<?php

namespace Tests\Unit;

use App\Models\Package;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PackagePriceTest extends TestCase
{
    #[DataProvider('prices')]
    public function test_package_prices_include_currency(?string $label, ?float $amount, string $expected): void
    {
        $package = new Package(['price_label' => $label, 'price_from' => $amount]);

        $this->assertSame($expected, $package->formatted_price);
    }

    public static function prices(): array
    {
        return [
            ['12,000 / person', 12000, 'NPR 12,000 / person'],
            ['From 12,000 / night', 12000, 'From NPR 12,000 / night'],
            ['NPR 12,000 / couple', 12000, 'NPR 12,000 / couple'],
            ['Rs. 12,000', 12000, 'Rs. 12,000'],
            ['USD 100', 100, 'USD 100'],
            ['€100', 100, '€100'],
            ['Price on request', null, 'Price on request'],
            [null, 12000, 'From NPR 12,000'],
            ['  ', 0, 'From NPR 0'],
            [null, null, 'Contact for pricing'],
        ];
    }
}
