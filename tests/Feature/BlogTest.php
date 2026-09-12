<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'blogs@example.com', 'password' => 'password', 'role' => 'superadmin', 'is_active' => true]);
    }

    private function postData(array $overrides = []): array
    {
        return array_merge(['title' => 'A Kathmandu story', 'content' => "First paragraph.\n\nSecond paragraph.", 'is_published' => 0], $overrides);
    }

    private function photo(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    }

    public function test_public_pages_only_show_published_posts_and_paginate(): void
    {
        for ($i = 1; $i <= 11; $i++) {
            Blog::create($this->postData(['title' => "Public story $i", 'slug' => "story-$i", 'is_published' => true, 'published_at' => now()->subDays(12 - $i)]));
        }
        $draft = Blog::create($this->postData(['title' => 'Secret draft', 'slug' => 'secret-draft']));
        Blog::create($this->postData(['title' => 'Future story', 'slug' => 'future', 'is_published' => true, 'published_at' => now()->addDay()]));
        $home = $this->get('/')->assertOk()->assertViewMissing('blogs')
            ->assertDontSee('id="blogs"', false)->assertDontSee('Public story')->assertDontSee('Secret draft')->assertDontSee('Future story');
        $document = new \DOMDocument();
        @$document->loadHTML($home->getContent());
        $links = (new \DOMXPath($document))->query('//a[normalize-space(.)="Blogs"]');
        $this->assertSame(3, $links->length);
        foreach ($links as $link) {
            $this->assertSame(route('blogs.index'), $link->getAttribute('href'));
        }
        $this->get('/blogs')->assertOk()->assertSeeInOrder(['Public story 11', 'Public story 10', 'Public story 9'])
            ->assertDontSee('Public story 2')->assertDontSee('Secret draft')->assertDontSee('Future story')
            ->assertDontSee('id="hero-section"', false)->assertDontSee('id="rooms"', false)
            ->assertDontSee('href="'.url('/').'#blogs"', false);
        $this->get('/blogs?page=2')->assertOk()->assertSee('Public story 2');
        $this->get('/blogs/story-11')->assertOk()->assertSee('Second paragraph.')->assertSee(url('/').'#rooms', false);
        $this->get('/blogs/'.$draft->slug)->assertNotFound();
        $this->get('/blogs/future')->assertNotFound();
        $this->get('/blogs/missing')->assertNotFound();
    }

    public function test_admin_can_create_publish_edit_unpublish_and_delete_with_image_cleanup(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $this->get(route('admin.blogs.index'))->assertOk();
        $this->get(route('admin.blogs.create'))->assertOk();
        $this->post(route('admin.blogs.store'), $this->postData(['cover_image' => $this->photo('cover.png')]))->assertRedirect(route('admin.blogs.index'));
        $blog = Blog::firstOrFail();
        $slug = $blog->slug;
        $oldCover = $blog->cover_image;
        Storage::disk('public')->assertExists($oldCover);
        $this->get(route('admin.blogs.edit', $blog))->assertOk()->assertSee('Cover image preview');
        $this->get('/blogs/'.$slug)->assertNotFound();
        $this->put(route('admin.blogs.update', $blog), $this->postData(['title' => 'Updated title', 'is_published' => 1, 'cover_image' => $this->photo('replacement.png')]))->assertRedirect();
        $blog->refresh();
        $this->assertSame($slug, $blog->slug);
        $this->assertNotNull($blog->published_at);
        Storage::disk('public')->assertMissing($oldCover);
        Storage::disk('public')->assertExists($blog->cover_image);
        $this->get('/blogs/'.$slug)->assertOk()->assertSee('Updated title');
        $oldCover = $blog->cover_image;
        $this->put(route('admin.blogs.update', $blog), $this->postData(['remove_cover' => 1]))->assertRedirect();
        $this->assertNull($blog->fresh()->cover_image);
        Storage::disk('public')->assertMissing($oldCover);
        $this->get('/blogs/'.$slug)->assertNotFound();
        $this->put(route('admin.blogs.update', $blog), $this->postData(['cover_image' => $this->photo('final.png')]))->assertRedirect();
        $lastCover = $blog->fresh()->cover_image;
        $this->delete(route('admin.blogs.destroy', $blog))->assertRedirect();
        $this->assertDatabaseCount('blogs', 0);
        Storage::disk('public')->assertMissing($lastCover);
    }

    public function test_validation_access_control_unique_slugs_and_safe_article_text(): void
    {
        $this->get(route('admin.blogs.index'))->assertRedirect(route('login'));
        $this->post(route('admin.blogs.store'), $this->postData())->assertRedirect(route('login'));
        $member = User::create(['name' => 'Guest', 'email' => 'guest@example.com', 'password' => 'password', 'role' => 'guest', 'is_active' => true]);
        $this->actingAs($member)->get(route('admin.blogs.create'))->assertRedirect(route('login'));
        $this->actingAs($this->admin())->post(route('admin.blogs.store'), $this->postData(['title' => '', 'content' => '', 'is_published' => 'invalid', 'cover_image' => UploadedFile::fake()->create('script.svg', 1, 'image/svg+xml')]))->assertSessionHasErrors(['title', 'content', 'is_published', 'cover_image']);
        $this->assertDatabaseCount('blogs', 0);
        foreach ([1, 2] as $i) {
            $this->post(route('admin.blogs.store'), $this->postData(['is_published' => 1, 'content' => '<script>alert("unsafe")</script>']))->assertRedirect();
        }
        $this->assertSame(['a-kathmandu-story', 'a-kathmandu-story-2'], Blog::orderBy('id')->pluck('slug')->all());
        $this->get('/blogs/a-kathmandu-story')->assertOk()->assertSee('<script>alert("unsafe")</script>')->assertDontSee('<script>alert("unsafe")</script>', false);
    }
}
