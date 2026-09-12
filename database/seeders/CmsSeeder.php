<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;
use App\Models\Room;
use App\Models\Experience;
use App\Models\GalleryItem;
use App\Models\Service;
use App\Models\Testimonial;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Site Settings ─────────────────────────────────────────────
        $settings = [
            // General
            ['key' => 'site_name',        'value' => 'Pashupati Views Hotel', 'type' => 'text',     'group' => 'general', 'label' => 'Site Name',         'sort_order' => 1],
            ['key' => 'site_tagline',     'value' => 'Where comfort meets devotion.', 'type' => 'text', 'group' => 'general', 'label' => 'Tagline',       'sort_order' => 2],
            ['key' => 'announcement_bar', 'value' => 'PRIVATE PREVIEW · A glimpse of what\'s to come', 'type' => 'text', 'group' => 'general', 'label' => 'Announcement Banner', 'sort_order' => 3],
            ['key' => 'announcement_bar_active', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'label' => 'Show Announcement Banner', 'sort_order' => 4],

            // Hero
            ['key' => 'hero_badge',       'value' => 'A WARM WELCOME. A QUIETER PACE.', 'type' => 'text',  'group' => 'hero', 'label' => 'Hero Badge Text',   'sort_order' => 1],
            ['key' => 'hero_heading',     'value' => 'Where comfort meets devotion.',    'type' => 'text',  'group' => 'hero', 'label' => 'Hero Heading',       'sort_order' => 2],
            ['key' => 'hero_subheading',  'value' => 'आराम र आत्मीयताको न्यानो संगम।', 'type' => 'text',  'group' => 'hero', 'label' => 'Hero Sub-heading',   'sort_order' => 3],
            ['key' => 'hero_body',        'value' => 'Make room for meaningful moments. A thoughtfully imagined stay at Pashupati Views Hotel.', 'type' => 'textarea', 'group' => 'hero', 'label' => 'Hero Body Text', 'sort_order' => 4],
            ['key' => 'hero_cta_primary', 'value' => 'Plan your stay ↗', 'type' => 'text', 'group' => 'hero', 'label' => 'Primary CTA Label', 'sort_order' => 5],

            // Rooms section
            ['key' => 'rooms_subtitle',      'value' => 'REST, BEAUTIFULLY REIMAGINED',          'type' => 'text',     'group' => 'rooms', 'label' => 'Rooms Section Subtitle', 'sort_order' => 1],
            ['key' => 'rooms_title',         'value' => 'Your own little sanctuary.',             'type' => 'text',     'group' => 'rooms', 'label' => 'Rooms Section Title',    'sort_order' => 2],
            ['key' => 'rooms_description',   'value' => 'A quiet corner for every kind of journey. Our rooms are designed for rest, reflection and a good night\'s sleep.', 'type' => 'textarea', 'group' => 'rooms', 'label' => 'Rooms Section Description', 'sort_order' => 3],

            // Packages section
            ['key' => 'packages_subtitle', 'value' => 'STAY EXPERIENCES', 'type' => 'text', 'group' => 'packages', 'label' => 'Packages Section Subtitle', 'sort_order' => 1],
            ['key' => 'packages_title', 'value' => 'Curated Packages', 'type' => 'text', 'group' => 'packages', 'label' => 'Packages Section Title', 'sort_order' => 2],
            ['key' => 'packages_description', 'value' => 'Tailored experiences that go beyond a simple room — moments designed around your purpose of visit.', 'type' => 'textarea', 'group' => 'packages', 'label' => 'Packages Section Description', 'sort_order' => 3],
            ['key' => 'packages_note', 'value' => 'All packages can be customised. Contact us to tailor a package that perfectly fits your itinerary.', 'type' => 'textarea', 'group' => 'packages', 'label' => 'Packages Bottom Note', 'sort_order' => 4],

            // Experience section
            ['key' => 'experience_subtitle', 'value' => 'THE EXPERIENCE',     'type' => 'text',     'group' => 'experience', 'label' => 'Experience Section Subtitle', 'sort_order' => 1],
            ['key' => 'experience_title',    'value' => 'A quieter rhythm.',   'type' => 'text',     'group' => 'experience', 'label' => 'Experience Section Title',    'sort_order' => 2],

            // Gallery section
            ['key' => 'gallery_subtitle',    'value' => 'A GLIMPSE INSIDE',   'type' => 'text',     'group' => 'gallery', 'label' => 'Gallery Section Subtitle', 'sort_order' => 1],
            ['key' => 'gallery_title',       'value' => 'Spaces to unwind.',  'type' => 'text',     'group' => 'gallery', 'label' => 'Gallery Section Title',    'sort_order' => 2],

            // Services section
            ['key' => 'services_subtitle',   'value' => 'SERVICES & CONVENIENCES', 'type' => 'text', 'group' => 'services', 'label' => 'Services Section Subtitle', 'sort_order' => 1],
            ['key' => 'services_title',      'value' => 'Thoughtfully arranged.',   'type' => 'text', 'group' => 'services', 'label' => 'Services Section Title',    'sort_order' => 2],

            // Testimonials section
            ['key' => 'testimonials_subtitle','value' => 'MOMENTS TO REMEMBER',     'type' => 'text', 'group' => 'testimonials', 'label' => 'Testimonials Subtitle', 'sort_order' => 1],
            ['key' => 'testimonials_title',   'value' => 'A few words about the stay.','type' => 'text','group'=> 'testimonials', 'label' => 'Testimonials Title',    'sort_order' => 2],

            // Contact
            ['key' => 'contact_phone',     'value' => '+977-1-XXXXXXX',              'type' => 'text', 'group' => 'contact', 'label' => 'Phone Number',      'sort_order' => 1],
            ['key' => 'contact_whatsapp',  'value' => '+977XXXXXXXXX',               'type' => 'text', 'group' => 'contact', 'label' => 'WhatsApp Number',   'sort_order' => 2],
            ['key' => 'contact_email',     'value' => 'info@pashupativiews.com',     'type' => 'text', 'group' => 'contact', 'label' => 'Email Address',     'sort_order' => 3],
            ['key' => 'contact_address',   'value' => 'Pashupatinath, Kathmandu, Nepal', 'type' => 'text', 'group' => 'contact', 'label' => 'Address',      'sort_order' => 4],
            ['key' => 'contact_map_location', 'value' => '', 'type' => 'text', 'group' => 'contact', 'label' => 'Map Location (full address or latitude, longitude)', 'sort_order' => 5],

            // Footer
            ['key' => 'footer_disclaimer', 'value' => 'Private concept · Pashupati Views Hotel. No live reservations or payments.', 'type' => 'textarea', 'group' => 'footer', 'label' => 'Footer Disclaimer', 'sort_order' => 1],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // ── Rooms ─────────────────────────────────────────────────────
        $rooms = [
            [
                'name'              => 'Deluxe King',
                'slug'              => 'deluxe-king',
                'category'          => 'deluxe',
                'tagline'           => 'A QUIET RETREAT',
                'price_per_night'   => 6500,
                'size_sqm'          => 28,
                'max_guests'        => 2,
                'bed_type'          => '1 king bed',
                'short_description' => 'Soft textures, warm light and a generously sized king bed.',
                'description'       => '<p>Soft textures, warm light and a generously sized king bed make the Deluxe King the ideal room for couples or solo travellers seeking a comfortable retreat in Kathmandu.</p><p>The room features thoughtfully curated décor drawing inspiration from traditional Nepali craft, with modern comforts woven seamlessly throughout.</p>',
                'amenities'         => ['Wi-Fi', 'Air conditioning', 'Private bathroom', 'Tea & coffee setup'],
                'is_active'         => true,
                'sort_order'        => 1,
            ],
            [
                'name'              => 'Premium Twin',
                'slug'              => 'premium-twin',
                'category'          => 'premium',
                'tagline'           => 'COMFORT TO SHARE',
                'price_per_night'   => 8200,
                'size_sqm'          => 32,
                'max_guests'        => 2,
                'bed_type'          => '2 single beds',
                'short_description' => 'Two comfortable single beds with extra seating space.',
                'description'       => '<p>Two comfortable single beds, extra seating space and a dedicated work desk make this room ideal for friends travelling together or business guests.</p><p>Enjoy premium amenities including a minibar stocked with local favourites and a spacious private bathroom.</p>',
                'amenities'         => ['Wi-Fi', 'Air conditioning', 'Work desk', 'Minibar', 'Private bathroom'],
                'is_active'         => true,
                'sort_order'        => 2,
            ],
            [
                'name'              => 'Family Suite',
                'slug'              => 'family-suite',
                'category'          => 'suite',
                'tagline'           => 'A LITTLE MORE TOGETHER',
                'price_per_night'   => 12500,
                'size_sqm'          => 48,
                'max_guests'        => 4,
                'bed_type'          => '1 king + 2 single beds',
                'short_description' => 'Separate living area, space for four guests in comfort.',
                'description'       => '<p>Separate living area, space for four guests — the Family Suite is our most spacious accommodation, designed to bring families closer together without compromising personal space.</p><p>The suite features a dedicated living room, premium bedding throughout, a Smart TV, and stunning views of the surrounding landscape.</p>',
                'amenities'         => ['Living area', 'Wi-Fi', 'Smart TV', 'Tea & coffee setup', 'Bathrobes', 'Air conditioning'],
                'is_active'         => true,
                'sort_order'        => 3,
            ],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(['slug' => $room['slug']], $room);
        }

        // ── Experiences ────────────────────────────────────────────────
        $experiences = [
            ['title' => 'Temple Proximity',     'description' => 'Steps away from the sacred Pashupatinath Temple, one of the holiest Hindu shrines in the world.', 'icon' => '🛕', 'sort_order' => 1],
            ['title' => 'Peaceful Atmosphere',  'description' => 'Escape the city noise. Our hotel is surrounded by serene gardens and tranquil courtyards.', 'icon' => '🌿', 'sort_order' => 2],
            ['title' => 'High-Speed Wi-Fi',     'description' => 'Stay connected throughout your stay with complimentary high-speed wireless internet.', 'icon' => '📶', 'sort_order' => 3],
            ['title' => '24/7 Guest Assistance','description' => 'Our dedicated concierge team is always available to cater to your every need, any time of day.', 'icon' => '🛎️', 'sort_order' => 4],
            ['title' => 'Fine Dining',          'description' => 'Savour authentic Nepali cuisine and international dishes prepared by our expert culinary team.', 'icon' => '🍽️', 'sort_order' => 5],
            ['title' => 'Valley Views',         'description' => 'Wake up to breathtaking panoramic views of the Kathmandu Valley from your private room.', 'icon' => '🌄', 'sort_order' => 6],
        ];

        foreach ($experiences as $exp) {
            Experience::updateOrCreate(['title' => $exp['title']], array_merge($exp, ['is_active' => true]));
        }

        // ── Services ───────────────────────────────────────────────────
        $services = [
            ['title' => 'Airport Pickup & Drop',  'description' => 'Comfortable, air-conditioned transfer from Tribhuvan International Airport to the hotel and back.', 'icon' => '✈️', 'price_label' => 'Price on request', 'sort_order' => 1],
            ['title' => 'Car Hire with Driver',    'description' => 'Explore Kathmandu and surrounding areas at your own pace with our professional driver service.', 'icon' => '🚗', 'price_label' => 'Price on request', 'sort_order' => 2],
            ['title' => 'Local Sightseeing',       'description' => 'Guided tours to Boudhanath Stupa, Swayambhunath, Bhaktapur Durbar Square, and beyond.', 'icon' => '🗺️', 'price_label' => 'Price on request', 'sort_order' => 3],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['title' => $service['title']], array_merge($service, ['is_active' => true]));
        }

        // ── Testimonials ───────────────────────────────────────────────
        $testimonials = [
            ['author_name' => 'Anisha K.',  'author_date' => 'September 2026', 'rating' => 5, 'review' => 'An absolutely wonderful stay. The location next to the temple created such a peaceful and spiritual atmosphere. The staff were incredibly warm and attentive throughout.', 'tag' => 'Verified Guest', 'sort_order' => 1],
            ['author_name' => 'Rohan S.',   'author_date' => 'August 2026',    'rating' => 5, 'review' => 'The Family Suite exceeded our expectations. Spacious, beautifully decorated, and the views from the window were simply magical at sunrise.', 'tag' => 'Verified Guest', 'sort_order' => 2],
            ['author_name' => 'Meera T.',   'author_date' => 'July 2026',      'rating' => 5, 'review' => 'I will never forget the experience. The food, the service, and the ambiance were all world-class. Highly recommend the Deluxe King room.', 'tag' => 'Verified Guest', 'sort_order' => 3],
        ];

        foreach ($testimonials as $t) {
            Testimonial::updateOrCreate(
                ['author_name' => $t['author_name'], 'author_date' => $t['author_date']],
                array_merge($t, ['is_active' => true])
            );
        }

        // ── Gallery Items ──────────────────────────────────────────────
        $galleryItems = [
            ['title' => 'A restful guest room',   'image_path' => '/images/gallery/room-1.jpg',  'badge_label' => 'Rooms',   'section' => 'rooms',   'sort_order' => 1],
            ['title' => 'Temple at golden hour',  'image_path' => '/images/gallery/temple-1.jpg','badge_label' => 'Location','section' => 'general', 'sort_order' => 2],
            ['title' => 'Evening by the garden',  'image_path' => '/images/gallery/garden-1.jpg','badge_label' => 'Garden',  'section' => 'general', 'sort_order' => 3],
            ['title' => 'Dining experience',      'image_path' => '/images/gallery/dining-1.jpg','badge_label' => 'Dining',  'section' => 'dining',  'sort_order' => 4],
            ['title' => 'Suite living area',      'image_path' => '/images/gallery/suite-1.jpg', 'badge_label' => 'Rooms',   'section' => 'rooms',   'sort_order' => 5],
            ['title' => 'Kathmandu Valley view',  'image_path' => '/images/gallery/view-1.jpg',  'badge_label' => 'Views',   'section' => 'general', 'sort_order' => 6],
        ];

        foreach ($galleryItems as $item) {
            GalleryItem::updateOrCreate(['title' => $item['title']], array_merge($item, ['is_active' => true]));
        }
    }
}
