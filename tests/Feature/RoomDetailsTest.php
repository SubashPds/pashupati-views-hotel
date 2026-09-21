<?php

namespace Tests\Feature;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RoomDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_only_loads_room_cards(): void
    {
        $room = Room::create([
            'name' => 'On Demand Room', 'category' => 'deluxe', 'price_per_night' => 1600,
            'description' => 'Detailed room description only available on demand.', 'is_active' => true,
        ]);
        $room->images()->create(['image_path' => 'rooms/gallery/on-demand.jpg', 'sort_order' => 1]);

        DB::enableQueryLog();
        $response = $this->get('/');
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertOk()->assertSee('On Demand Room')
            ->assertSee(route('rooms.details', $room))
            ->assertDontSee($room->description)
            ->assertDontSee('rooms/gallery/on-demand.jpg')
            ->assertDontSee('<dialog id="room-details-', false);
        foreach ($queries as $query) {
            $this->assertStringNotContainsString('room_images', $query['query']);
        }
        $this->assertFalse($response->viewData('rooms')->first()->relationLoaded('images'));
        $this->assertArrayNotHasKey('description', $response->viewData('rooms')->first()->getAttributes());
    }

    public function test_details_include_requested_room_gallery_and_selected_currency(): void
    {
        $room = Room::create([
            'name' => 'Requested Room', 'category' => 'deluxe', 'price_per_night' => 1600,
            'description' => 'Full requested room description.', 'is_active' => true,
        ]);
        $room->images()->create(['image_path' => 'rooms/gallery/requested.jpg', 'caption' => 'Balcony view', 'sort_order' => 1]);
        Room::create(['name' => 'Other Room', 'category' => 'deluxe', 'price_per_night' => 2000, 'is_active' => true]);

        $response = $this->withCookie('display_currency', 'INR')->get(route('rooms.details', $room));
        $response->assertOk()->assertSee('Requested Room')->assertSee($room->description)
            ->assertSee('rooms/gallery/requested.jpg')->assertSee('Balcony view')->assertSee('INR')
            ->assertSee('data-book-room="Requested Room"', false)->assertDontSee('Other Room');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_inactive_and_missing_room_details_are_not_public(): void
    {
        $room = Room::create(['name' => 'Hidden Room', 'category' => 'deluxe', 'price_per_night' => 1600, 'is_active' => false]);
        $this->get(route('rooms.details', $room))->assertNotFound()->assertDontSee('Hidden Room');
        $this->get(route('rooms.details', 999999))->assertNotFound();
    }
}
