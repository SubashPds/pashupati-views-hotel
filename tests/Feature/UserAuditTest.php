<?php

namespace Tests\Feature;

use App\Models\{Blog, Enquiry, Experience, GalleryItem, HeroSlide, Package, PackageImage, Promotion, Role, Room, RoomImage, Service, SiteSetting, Testimonial, User};
use App\Models\Concerns\TracksUserChanges;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Auth, DB, Mail, Schema, Storage};
use Tests\TestCase;

class UserAuditTest extends TestCase
{
    use RefreshDatabase;

    private function actor(string $name): User
    {
        return User::create(['name' => $name, 'email' => $name.'@example.test', 'password' => 'Test-password-2026', 'role' => 'superadmin', 'is_active' => true]);
    }

    private function signIn(User $user): void
    {
        $this->flushSession();
        $this->actingAs($user);
    }

    public function test_all_application_models_track_creator_and_latest_editor(): void
    {
        $author = $this->actor('author');
        $editor = $this->actor('editor');
        $this->signIn($author);
        $room = Room::create(['name' => 'Parent room', 'category' => 'deluxe', 'price_per_night' => 1000]);
        $package = Package::create(['name' => 'Parent package']);
        $fixtures = [
            [User::class, ['name' => 'Staff', 'email' => 'staff@example.test', 'password' => 'Test-password-2026'], ['name' => 'New staff name']],
            [Role::class, ['name' => 'audit-role', 'permissions' => []], ['permissions' => ['rooms']]],
            [SiteSetting::class, ['key' => 'audit-key', 'label' => 'Audit setting', 'value' => 'Before'], ['value' => 'After']],
            [Room::class, ['name' => 'Audit room', 'category' => 'deluxe', 'price_per_night' => 1000], ['price_per_night' => 2000]],
            [RoomImage::class, ['room_id' => $room->id, 'image_path' => 'room.jpg'], ['caption' => 'Updated room image']],
            [Package::class, ['name' => 'Audit package'], ['tagline' => 'Updated package']],
            [PackageImage::class, ['package_id' => $package->id, 'image_path' => 'package.jpg'], ['caption' => 'Updated package image']],
            [Experience::class, ['title' => 'Audit experience'], ['title' => 'Updated experience']],
            [GalleryItem::class, ['image_path' => 'gallery.jpg'], ['title' => 'Updated gallery item']],
            [Service::class, ['title' => 'Audit service'], ['title' => 'Updated service']],
            [Testimonial::class, ['author_name' => 'Guest', 'review' => 'Review'], ['review' => 'Updated review']],
            [Enquiry::class, ['guest_name' => 'Guest'], ['status' => 'replied']],
            [HeroSlide::class, ['title' => 'Slide', 'media_path' => 'slide.jpg', 'media_type' => 'image'], ['title' => 'Updated slide']],
            [Blog::class, ['title' => 'Blog', 'slug' => 'audit-blog', 'content' => 'Content'], ['title' => 'Updated blog']],
            [Promotion::class, ['title' => 'Offer'], ['title' => 'Updated offer']],
        ];
        foreach ($fixtures as [$class, $data, $changes]) {
            $this->signIn($author);
            $record = new $class;
            $record->forceFill($data + ['created_by' => $editor->id, 'updated_by' => $editor->id])->save();
            $record->refresh();
            $this->assertSame($author->id, $record->created_by, $class);
            $this->assertSame($author->id, $record->updated_by, $class);
            $this->signIn($editor);
            $record->forceFill($changes + ['created_by' => $editor->id, 'updated_by' => $author->id])->save();
            $record->refresh();
            $this->assertSame($author->id, $record->created_by, $class);
            $this->assertSame($editor->id, $record->updated_by, $class);
            $this->assertTrue($record->creator->is($author));
            $this->assertTrue($record->updater->is($editor));
        }
        foreach (glob(app_path('Models/*.php')) as $file) {
            $class = 'App\\Models\\'.basename($file, '.php');
            $this->assertContains(TracksUserChanges::class, class_uses_recursive($class));
            $this->assertTrue(Schema::hasColumns((new $class)->getTable(), ['created_by', 'updated_by']));
        }
    }

    public function test_settings_update_paths_record_the_editor_and_preserve_creator(): void
    {
        $author = $this->actor('author');
        $editor = $this->actor('editor');
        $this->signIn($author);
        $setting = SiteSetting::create(['key' => 'site_name', 'value' => 'Before', 'label' => 'Site name']);
        $this->signIn($editor);
        $this->post(route('admin.settings.update'), ['site_name' => 'After', 'currency_npr_per_usd' => '150', 'created_by' => $author->id])
            ->assertSessionHasNoErrors();
        $this->assertSame($author->id, $setting->fresh()->created_by);
        $this->assertSame($editor->id, $setting->fresh()->updated_by);
        $currency = SiteSetting::where('key', 'currency_npr_per_usd')->firstOrFail();
        $this->assertSame($editor->id, $currency->created_by);
        $this->assertSame($editor->id, $currency->updated_by);
        $this->signIn($author);
        SiteSetting::set('site_name', 'Via helper');
        $this->assertSame($author->id, $setting->fresh()->updated_by);
    }

    public function test_guest_enquiry_stays_anonymous_until_staff_updates_it(): void
    {
        Mail::fake();
        $staff = $this->actor('staff');
        $this->post(route('enquire'), ['guest_name' => 'Guest', 'email' => 'guest@example.test', 'created_by' => $staff->id, 'updated_by' => $staff->id])->assertSessionHasNoErrors();
        $enquiry = Enquiry::firstOrFail();
        $this->assertNull($enquiry->created_by);
        $this->assertNull($enquiry->updated_by);
        $this->signIn($staff);
        $this->get(route('admin.enquiries.show', $enquiry))->assertOk();
        $this->assertNull($enquiry->fresh()->created_by);
        $this->assertSame($staff->id, $enquiry->fresh()->updated_by);
    }

    public function test_gallery_uploads_and_status_changes_record_the_actor(): void
    {
        Storage::fake('public');
        $author = $this->actor('author');
        $editor = $this->actor('editor');
        $photo = fn () => UploadedFile::fake()->createWithContent('photo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
        $this->signIn($author);
        $this->post(route('admin.packages.store'), ['name' => 'Package with images', 'gallery_images' => [$photo()]])->assertSessionHasNoErrors();
        $package = Package::firstOrFail();
        $this->assertSame($author->id, $package->images()->first()->created_by);
        $this->signIn($editor);
        $this->put(route('admin.packages.update', $package), ['name' => $package->name, 'gallery_images' => [$photo()]])->assertSessionHasNoErrors();
        $this->assertSame($editor->id, $package->images()->get()->last()->created_by);
        $this->patch(route('admin.packages.toggle-status', $package))->assertRedirect();
        $this->assertSame($author->id, $package->fresh()->created_by);
        $this->assertSame($editor->id, $package->fresh()->updated_by);
    }

    public function test_deleting_an_actor_preserves_records_and_clears_foreign_keys(): void
    {
        $actor = $this->actor('actor');
        $this->signIn($actor);
        $service = Service::create(['title' => 'Service']);
        $actor->delete();
        $this->assertNotNull($service->fresh());
        $this->assertNull($service->fresh()->created_by);
        $this->assertNull($service->fresh()->updated_by);
    }

    public function test_migration_preserves_legacy_data_without_inventing_an_actor(): void
    {
        $migration = require database_path('migrations/2026_09_19_000001_add_user_audit_columns.php');
        $migration->down();
        $id = DB::table('services')->insertGetId(['title' => 'Legacy service']);
        $migration->up();
        $this->assertDatabaseHas('services', ['id' => $id, 'title' => 'Legacy service', 'created_by' => null, 'updated_by' => null]);
    }

    public function test_authentication_token_rotation_does_not_replace_the_account_editor(): void
    {
        $author = $this->actor('author');
        $this->signIn($author);
        $account = $this->actor('account');
        $this->signIn($account);
        Auth::getProvider()->updateRememberToken($account, 'rotated-token');
        $this->assertSame($author->id, $account->fresh()->updated_by);
    }
}
