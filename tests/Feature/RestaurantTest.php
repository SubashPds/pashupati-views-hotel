<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::create(['name' => 'Restaurant Admin', 'email' => 'restaurant@example.test', 'password' => 'password', 'role' => 'admin', 'is_active' => true]);
    }

    private function photo(string $name = 'photo.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    }

    private function details(array $overrides = []): array
    {
        return array_replace([
            'name' => 'CMS Dining Room', 'description' => 'Seasonal dining description', 'overview' => "An inviting dining room.\nA second paragraph.",
            'opening_time' => '06:00', 'closing_time' => '01:00',
            'breakfast_start_time' => '06:30', 'breakfast_end_time' => '10:00',
            'lunch_start_time' => '12:00', 'lunch_end_time' => '15:00',
            'dinner_start_time' => '18:00', 'dinner_end_time' => '22:00',
            'cuisines_raw' => " Nepali \n\n Continental ", 'featured_dishes_raw' => "Seasonal thali\nChef's soup", 'is_active' => '1',
        ], $overrides);
    }

    public function test_admin_can_manage_a_single_restaurant_and_public_content_comes_from_the_database(): void
    {
        $this->actingAs($this->admin)->get(route('admin.restaurant.index'))->assertOk()->assertSee('Restaurant Management')->assertSee('menu_image');
        $this->put(route('admin.restaurant.update'), $this->details(['menu_image' => $this->photo(), 'gallery_images' => [$this->photo(), $this->photo()]]))
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.restaurant.index'));
        $restaurant = Restaurant::with('images')->findOrFail(1);
        $this->assertSame(['Nepali', 'Continental'], $restaurant->cuisines);
        $this->assertSame(['Seasonal thali', "Chef's soup"], $restaurant->featured_dishes);
        $this->assertSame($this->admin->id, $restaurant->created_by);
        $this->assertCount(2, $restaurant->images);
        Storage::disk('public')->assertExists($restaurant->menu_image);
        foreach ($restaurant->images as $image) {
            Storage::disk('public')->assertExists($image->image_path);
        }
        $this->get('/restaurant')->assertOk()->assertSee('CMS Dining Room')->assertSee('Seasonal dining description')
            ->assertSee('An inviting dining room.')->assertSee('Nepali')->assertSee('Seasonal thali')->assertSee('6:30 AM')
            ->assertSee('Closes the following day')->assertSee(Storage::disk('public')->url($restaurant->menu_image))
            ->assertSee('Open full-size menu')->assertSee('data-restaurant-thumbnail', false);
        $this->get(route('admin.restaurant.index'))->assertOk()->assertSee('Replace gallery image 1');

        $this->put(route('admin.restaurant.update'), $this->details(['name' => 'Updated Dining Room', 'overview' => null]))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('restaurants', 1);
        $this->assertSame($restaurant->menu_image, $restaurant->fresh()->menu_image);
        $this->get('/restaurant')->assertSee('Updated Dining Room')->assertDontSee('An inviting dining room.');
    }

    public function test_menu_and_gallery_can_be_replaced_removed_and_appended_without_orphan_files(): void
    {
        $this->actingAs($this->admin)->put(route('admin.restaurant.update'), $this->details(['menu_image' => $this->photo(), 'gallery_images' => [$this->photo(), $this->photo()]]))->assertSessionHasNoErrors();
        $restaurant = Restaurant::with('images')->findOrFail(1);
        [$first, $second] = $restaurant->images->all();
        $oldMenu = $restaurant->menu_image;
        $this->put(route('admin.restaurant.update'), $this->details([
            'menu_image' => $this->photo('new-menu.png'),
            'gallery_images' => [$this->photo('new-gallery.png')],
            'images' => [
                ['id' => $first->id, 'caption' => 'Window-side dining', 'replacement' => $this->photo()],
                ['id' => $second->id, 'remove' => '1'],
            ],
        ]))->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing([$oldMenu, $first->image_path, $second->image_path]);
        $restaurant->refresh();
        Storage::disk('public')->assertExists($restaurant->menu_image);
        $this->assertCount(2, $restaurant->images);
        $this->assertSame('Window-side dining', $first->fresh()->caption);
        $this->get('/restaurant')->assertSee('Window-side dining');
        $this->put(route('admin.restaurant.update'), $this->details([
            'remove_menu_image' => '1',
            'images' => $restaurant->images->map(fn ($image) => ['id' => $image->id, 'remove' => '1'])->all(),
        ]))->assertSessionHasNoErrors();
        $this->assertNull($restaurant->fresh()->menu_image);
        $this->assertDatabaseCount('restaurant_images', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->get('/restaurant')->assertDontSee('Open full-size menu')->assertDontSee('Restaurant gallery');
    }

    public function test_validation_rejects_invalid_uploads_times_and_foreign_images_before_saving(): void
    {
        $this->actingAs($this->admin);
        foreach ([
            ['menu_image' => UploadedFile::fake()->createWithContent('bad.txt', 'not a photo')],
            ['gallery_images' => [$this->photo()->size(4097)]],
            ['gallery_images' => 'invalid'],
            ['opening_time' => '25:00'],
            ['breakfast_end_time' => null],
            ['name' => str_repeat('x', 256)],
            ['images' => [['id' => 999, 'remove' => '1']]],
            ['menu_image' => $this->photo(), 'remove_menu_image' => '1'],
        ] as $invalid) {
            $this->put(route('admin.restaurant.update'), $this->details($invalid))->assertSessionHasErrors();
            $this->assertDatabaseCount('restaurants', 0);
            $this->assertSame([], Storage::disk('public')->allFiles());
        }
        $other = Restaurant::create(['name' => 'Other']);
        $other->id = 2;
        $other->save();
        $image = $other->images()->create(['image_path' => 'other.png']);
        $this->put(route('admin.restaurant.update'), $this->details(['images' => [['id' => $image->id, 'remove' => '1']]]))
            ->assertSessionHasErrors('images.0.id');
        $this->assertDatabaseHas('restaurant_images', ['id' => $image->id]);
    }

    public function test_drafts_are_private_and_missing_content_has_an_empty_state(): void
    {
        $this->get('/restaurant')->assertOk()->assertSee('Restaurant details will be available soon.');
        $this->actingAs($this->admin)->put(route('admin.restaurant.update'), $this->details(['is_active' => '0']))->assertSessionHasNoErrors();
        $this->get('/restaurant')->assertOk()->assertDontSee('CMS Dining Room')->assertDontSee('Seasonal thali');
        $this->put(route('admin.restaurant.update'), ['name' => 'Minimal restaurant', 'is_active' => '1'])->assertSessionHasNoErrors();
        $this->get('/restaurant')->assertOk()->assertSee('Minimal restaurant')->assertDontSee('Food menu')->assertDontSee('Special &amp; featured dishes');
    }

    public function test_permissions_protect_reads_and_writes_and_support_revocation(): void
    {
        $this->get(route('admin.restaurant.index'))->assertRedirect(route('login'));
        $this->put(route('admin.restaurant.update'), $this->details())->assertRedirect(route('login'));
        $this->actingAs($this->admin)->get(route('admin.restaurant.index'))->assertOk();
        Role::where('name', 'admin')->firstOrFail()->update(['permissions' => ['rooms']]);
        $this->get(route('admin.restaurant.index'))->assertForbidden();
        $this->put(route('admin.restaurant.update'), $this->details())->assertForbidden();
        $this->get(route('admin.dashboard'))->assertDontSee('Restaurant Management');
        $this->assertDatabaseCount('restaurants', 0);
    }

    public function test_frontend_escapes_admin_text_and_links_to_the_restaurant(): void
    {
        $this->actingAs($this->admin)->put(route('admin.restaurant.update'), $this->details([
            'name' => '</title><script>alert(2)</script>',
            'description' => '"><script>alert(3)</script>',
            'overview' => '<script>alert(1)</script>',
        ]))->assertSessionHasNoErrors();
        $this->get('/restaurant')->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->get('/restaurant')->assertDontSee('<script>alert(2)</script>', false)->assertDontSee('<script>alert(3)</script>', false);
        $this->get('/')->assertOk()->assertSee(route('restaurant'));
    }
}
