<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name'              => 'Pashupatinath Darshan Package',
                'slug'              => 'pashupatinath-darshan-package',
                'tagline'           => 'SPIRITUAL IMMERSION',
                'badge'             => 'Most Popular',
                'short_description' => 'A deeply spiritual experience with guided Aarati views, temple walk, and comfortable stay.',
                'description'       => '<p>Begin your spiritual journey steps from one of the world\'s most sacred Hindu temples. This package is designed for pilgrims and devoted visitors who wish to immerse themselves in the rituals, sounds, and sacred atmosphere of the Pashupatinath Temple complex.</p><p>Includes guided evening Aarati viewing from our terrace, a morning temple walk with a knowledgeable local guide, and daily yoga/meditation session.</p>',
                'price_label'       => 'NPR 12,000 / night (per couple)',
                'price_from'        => 12000,
                'duration'          => '2 Nights / 3 Days',
                'min_guests'        => 1,
                'max_guests'        => 4,
                'includes'          => [
                    'Accommodation (Deluxe King or Twin room)',
                    'Daily breakfast for two',
                    'Evening Aarati viewing from hotel terrace',
                    'Guided morning temple walk',
                    'Daily yogic meditation session',
                    'Welcome drink on arrival',
                    'Complimentary Wi-Fi',
                ],
                'highlights'        => [
                    'Steps from Pashupatinath Temple',
                    'Sacred evening Aarati views',
                    'Guided spiritual walks',
                    'Peaceful garden courtyard',
                ],
                'is_active'   => true,
                'sort_order'  => 1,
            ],
            [
                'name'              => 'Airport Transit Stay',
                'slug'              => 'airport-transit-stay',
                'tagline'           => 'QUICK & COMFORTABLE',
                'badge'             => 'Flexible',
                'short_description' => 'A seamless overnight or day-stay solution for travellers in transit through Kathmandu.',
                'description'       => '<p>Passing through Kathmandu on your way to or from a trekking destination, or waiting for a connecting flight? Our Airport Transit Stay ensures you have a comfortable, clean room available on flexible check-in/check-out terms.</p><p>Includes airport pickup/drop, a light meal, and complimentary Wi-Fi to keep you connected throughout your layover.</p>',
                'price_label'       => 'NPR 7,500 / room',
                'price_from'        => 7500,
                'duration'          => 'Flexible (Day or Night)',
                'min_guests'        => 1,
                'max_guests'        => 2,
                'includes'          => [
                    'Room (8-hour or overnight)',
                    'Airport pickup & drop (Tribhuvan International)',
                    'Light breakfast or meal box',
                    'Complimentary Wi-Fi',
                    'Luggage storage',
                ],
                'highlights'        => [
                    'Flexible check-in/out times',
                    'Airport pickup included',
                    'Close to Boudhanath & Pashupatinath',
                    'No minimum night stay',
                ],
                'is_active'   => true,
                'sort_order'  => 2,
            ],
            [
                'name'              => 'Family Pilgrimage Package',
                'slug'              => 'family-pilgrimage-package',
                'tagline'           => 'TOGETHER IN DEVOTION',
                'badge'             => 'Family Friendly',
                'short_description' => 'A thoughtfully crafted multi-day itinerary for families visiting the sacred Pashupatinath and surrounding temples.',
                'description'       => '<p>Bring the entire family for a meaningful pilgrimage experience in Kathmandu. Our Family Pilgrimage Package accommodates up to 6 guests across our spacious Family Suite and adjoining rooms, with a full itinerary of temple visits, cultural experiences, and communal dining.</p><p>Includes guided visits to Pashupatinath, Boudhanath Stupa, and Swayambhunath (Monkey Temple).</p>',
                'price_label'       => 'NPR 28,000 / family (up to 4)',
                'price_from'        => 28000,
                'duration'          => '3 Nights / 4 Days',
                'min_guests'        => 2,
                'max_guests'        => 6,
                'includes'          => [
                    'Family Suite + 1 adjoining room',
                    'Daily breakfast & one group dinner',
                    'Guided Pashupatinath temple tour',
                    'Boudhanath Stupa & Swayambhunath day trip',
                    'Return airport transfers',
                    'Children\'s welcome gift',
                    'Complimentary Wi-Fi',
                ],
                'highlights'        => [
                    'Spacious Family Suite',
                    'Guided temple tours included',
                    'Suitable for all ages',
                    'Children welcome',
                ],
                'is_active'   => true,
                'sort_order'  => 3,
            ],
            [
                'name'              => 'Honeymoon & Anniversary Setup',
                'slug'              => 'honeymoon-anniversary-setup',
                'tagline'           => 'CELEBRATE TOGETHER',
                'badge'             => 'Romantic',
                'short_description' => 'A romantic escape with premium suite, rose petal setup, candlelight dinner and couples experiences.',
                'description'       => '<p>Mark this special chapter of your love story in the serene ambience of Pashupati Views Hotel. The Honeymoon & Anniversary package transforms your room into a haven of romance — rose petals, champagne on arrival, and a private candlelight dinner set under the stars in our courtyard garden.</p><p>Includes a couples\' yoga session and a personalized photo keepsake of your stay.</p>',
                'price_label'       => 'NPR 22,000 / couple',
                'price_from'        => 22000,
                'duration'          => '2 Nights / 3 Days',
                'min_guests'        => 2,
                'max_guests'        => 2,
                'includes'          => [
                    'Premium Suite with rose petal decoration',
                    'Welcome champagne / sparkling juice',
                    'Private candlelight dinner (courtyard)',
                    'Couples\' yoga & meditation session',
                    'Daily gourmet breakfast in room',
                    'Late checkout (subject to availability)',
                    'Personalized anniversary/honeymoon card',
                ],
                'highlights'        => [
                    'Rose petal room decoration',
                    'Private candlelight dinner',
                    'Couples yoga session',
                    'Serene garden courtyard',
                ],
                'is_active'   => true,
                'sort_order'  => 4,
            ],
            [
                'name'              => 'Long-Stay Kathmandu Package',
                'slug'              => 'long-stay-kathmandu-package',
                'tagline'           => 'YOUR HOME IN KATHMANDU',
                'badge'             => 'Best Value',
                'short_description' => 'Discounted monthly rates for researchers, volunteers, digital nomads and long-term visitors.',
                'description'       => '<p>Planning an extended stay in Kathmandu for work, research, volunteering, or spiritual retreat? Our Long-Stay Package offers our best available rates, with the comfort of a premium hotel and the flexibility of home.</p><p>Includes dedicated work desk, laundry service, weekly room deep-clean, and access to our rooftop terrace workspace — all at a flat monthly rate.</p>',
                'price_label'       => 'NPR 75,000 / month (single)',
                'price_from'        => 75000,
                'duration'          => '7+ Nights (Weekly / Monthly)',
                'min_guests'        => 1,
                'max_guests'        => 2,
                'includes'          => [
                    'Deluxe Room (7+ nights)',
                    'Daily breakfast',
                    'Weekly laundry service (10 items)',
                    'Dedicated work desk & high-speed Wi-Fi',
                    'Rooftop terrace workspace access',
                    'Monthly deep-clean',
                    'Local SIM card assistance',
                ],
                'highlights'        => [
                    'Best discounted rate',
                    'Work-from-hotel setup',
                    'Flexible weekly / monthly terms',
                    'Ideal for digital nomads & volunteers',
                ],
                'is_active'   => true,
                'sort_order'  => 5,
            ],
            [
                'name'              => 'Airport Pickup + Breakfast Package',
                'slug'              => 'airport-pickup-breakfast-package',
                'tagline'           => 'ARRIVE IN COMFORT',
                'badge'             => 'Add-On',
                'short_description' => 'Start your Kathmandu experience right — comfortable airport pickup and a nourishing hot breakfast awaiting you.',
                'description'       => '<p>Nothing sets the tone for a great stay like being greeted at the airport and arriving to a warm, freshly prepared breakfast. This simple add-on package can be combined with any room booking.</p><p>Our driver will meet you at the arrivals gate with a name board, transfer you to the hotel in an air-conditioned vehicle, and your choice of hot Nepali or continental breakfast will be ready upon arrival.</p>',
                'price_label'       => 'NPR 3,500 / booking',
                'price_from'        => 3500,
                'duration'          => 'Single arrival day',
                'min_guests'        => 1,
                'max_guests'        => 4,
                'includes'          => [
                    'Airport pickup in air-conditioned vehicle',
                    'Name board greeting at arrivals',
                    'Hotel-to-airport return transfer',
                    'Hot breakfast on arrival (Nepali or continental)',
                    'Complimentary mineral water en route',
                ],
                'highlights'        => [
                    'Meet & greet at airport',
                    'Air-conditioned transfer',
                    'Hot breakfast on arrival',
                    'Can be added to any booking',
                ],
                'is_active'   => true,
                'sort_order'  => 6,
            ],
        ];

        foreach ($packages as $pkg) {
            Package::updateOrCreate(['slug' => $pkg['slug']], $pkg);
        }

        $this->command->info('✓ Seeded ' . count($packages) . ' packages.');
    }
}
