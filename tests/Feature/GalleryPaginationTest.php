<?php

namespace Tests\Feature;

use App\Models\GalleryItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_pages_contain_at_most_16_active_items_in_stable_order(): void
    {
        $ids = [];
        for ($i = 1; $i <= 37; $i++) {
            $ids[] = GalleryItem::create([
                'title' => 'Gallery photo '.$i, 'image_path' => 'gallery/'.$i.'.jpg',
                'section' => 'rooms', 'sort_order' => 0, 'is_active' => true,
            ])->id;
        }
        GalleryItem::create(['title' => 'Hidden photo', 'image_path' => 'hidden.jpg', 'is_active' => false]);

        foreach ([1, 2, 3] as $page) {
            $response = $this->get(route('gallery', ['page' => $page]))->assertOk()->assertDontSee('Hidden photo');
            $items = $response->viewData('galleryItems');
            $this->assertSame(16, $items->perPage());
            $this->assertSame(37, $items->total());
            $this->assertSame(array_slice($ids, ($page - 1) * 16, 16), $items->pluck('id')->all());
            $this->assertSame(min(16, 37 - ($page - 1) * 16), substr_count($response->getContent(), 'data-gallery-category='));
        }
        $this->get(route('gallery'))->assertSee('Showing 1–16 of 37 moments')->assertDontSee('Gallery photo 17');
    }

    public function test_filters_search_the_full_gallery_and_persist_across_pages(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            GalleryItem::create(['title' => 'Room '.$i, 'image_path' => 'room.jpg', 'section' => ' Rooms ', 'is_active' => true]);
        }
        for ($i = 1; $i <= 17; $i++) {
            GalleryItem::create(['title' => 'Dining '.$i, 'image_path' => 'dining.jpg', 'section' => 'dining', 'is_active' => true]);
        }

        $first = $this->get(route('gallery'))->assertOk()->assertSee('data-gallery-filter="dining"', false);
        $this->assertSame(17, (int) $first->viewData('categoryCounts')->get('dining'));
        $response = $this->get(route('gallery', ['category' => 'dining']))->assertOk()->assertDontSee('Room 1');
        $items = $response->viewData('galleryItems');
        $this->assertSame(17, $items->total());
        $this->assertCount(16, $items);
        $this->assertStringContainsString('category=dining', $items->nextPageUrl());
        $this->assertStringEndsWith('#gallery', $items->nextPageUrl());
        $this->get($items->nextPageUrl())->assertOk()->assertSee('Dining 17')->assertSee('Showing 17–17 of 17 moments');
        $this->get(route('gallery', ['category' => 'rooms']))->assertOk()->assertSee('Room 1')->assertDontSee('Dining 1');
    }

    public function test_empty_gallery_and_invalid_categories_are_handled(): void
    {
        $this->get(route('gallery'))->assertOk()->assertSee('New perspectives, coming soon.');
        $this->get(route('gallery', ['category' => ['rooms']]))->assertNotFound();
        $this->get(route('gallery', ['category' => 'missing']))->assertNotFound();
    }
}
