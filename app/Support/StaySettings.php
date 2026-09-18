<?php

namespace App\Support;

class StaySettings
{
    public static function definitions(): array
    {
        $fields = [
            ['enabled', '1', 'boolean', 'Show Plan your stay section'],
            ['eyebrow', 'Plan your stay', 'text', 'Section label'],
            ['title', 'Your ideal hotel experience starts here.', 'text', 'Heading'],
            ['description', 'Choose the room, package, or experience that matches your travel style — whether it is a relaxing getaway, family retreat, or a premium spiritual stay near Pashupatinath.', 'textarea', 'Description'],
            ['rooms_label', 'Explore rooms', 'text', 'Rooms button label'],
            ['packages_label', 'View packages', 'text', 'Packages button label'],
            ['contact_label', 'Contact us', 'text', 'Contact button label'],
            ['card_1_label', 'For couples', 'text', 'Card 1 label'],
            ['card_1_title', 'Quiet luxury', 'text', 'Card 1 heading'],
            ['card_1_description', 'Wake up to temple views and enjoy calm, elegant spaces designed for restful stays.', 'textarea', 'Card 1 description'],
            ['card_2_label', 'For families', 'text', 'Card 2 label'],
            ['card_2_title', 'Comfortable stays', 'text', 'Card 2 heading'],
            ['card_2_description', 'Spacious options and warm hospitality make group visits convenient and memorable.', 'textarea', 'Card 2 description'],
            ['card_3_label', 'Direct booking', 'text', 'Card 3 label'],
            ['card_3_title', 'Best value', 'text', 'Card 3 heading'],
            ['card_3_description', 'Book directly with us for a smooth experience and a more personal service.', 'textarea', 'Card 3 description'],
        ];

        return array_map(fn ($field, $order) => [
            'key' => 'stay_'.$field[0], 'value' => $field[1], 'type' => $field[2],
            'group' => 'stay', 'label' => $field[3], 'sort_order' => $order,
        ], $fields, array_keys($fields));
    }

    public static function defaults(): array
    {
        return array_column(self::definitions(), 'value', 'key');
    }
}
