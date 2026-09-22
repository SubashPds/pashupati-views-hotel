<?php

namespace Tests\Feature;

use App\Models\Policy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PolicyUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::create([
            'name' => 'Policy Admin', 'email' => 'policy@example.test', 'password' => 'password',
            'role' => 'superadmin', 'is_active' => true,
        ]));
    }

    public function test_browser_status_buttons_toggle_without_changing_content(): void
    {
        $policy = Policy::firstOrFail();
        $original = $policy->only('title', 'description', 'category');

        foreach (['0', '1'] as $value) {
            $this->from(route('admin.policies.index'))->post(route('admin.policies.update', $policy), [
                '_token' => 'test-token', '_method' => 'PUT', 'is_active' => $value,
            ])->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.policies.index'))
                ->assertSessionHas('success', 'Status updated.');

            $policy->refresh();
            $this->assertSame($value === '1', $policy->is_active);
            $this->assertSame($original, $policy->only('title', 'description', 'category'));
        }
    }

    public static function jsonStatuses(): array
    {
        return [
            'integer inactive' => [0, false],
            'integer active' => [1, true],
            'boolean inactive' => [false, false],
            'boolean active' => [true, true],
        ];
    }

    #[DataProvider('jsonStatuses')]
    public function test_json_edits_save_content_and_status(int|bool $value, bool $expected): void
    {
        $policy = Policy::firstOrFail();
        $policy->update(['is_active' => ! $expected]);

        $this->putJson(route('admin.policies.update', $policy), [
            'title' => 'Revised policy', 'description' => '<p>Revised content.</p>', 'is_active' => $value,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.policies.index'));

        $policy->refresh();
        $this->assertSame('Revised policy', $policy->title);
        $this->assertSame('<p>Revised content.</p>', $policy->description);
        $this->assertSame($expected, $policy->is_active);
    }

    #[DataProvider('jsonStatuses')]
    public function test_json_status_only_requests_preserve_content(int|bool $value, bool $expected): void
    {
        $policy = Policy::firstOrFail();
        $policy->update(['is_active' => ! $expected]);
        $original = $policy->only('title', 'description');

        $this->putJson(route('admin.policies.update', $policy), ['is_active' => $value])
            ->assertSessionHasNoErrors()->assertRedirect();

        $policy->refresh();
        $this->assertSame($expected, $policy->is_active);
        $this->assertSame($original, $policy->only('title', 'description'));
    }

    public function test_browser_edits_save_content_with_either_status(): void
    {
        $policy = Policy::firstOrFail();

        foreach (['0', '1'] as $value) {
            $this->post(route('admin.policies.update', $policy), [
                '_token' => 'test-token', '_method' => 'PUT',
                'title' => 'Policy '.$value, 'description' => 'Content '.$value, 'is_active' => $value,
            ])->assertSessionHasNoErrors()->assertRedirect(route('admin.policies.index'));

            $policy->refresh();
            $this->assertSame('Policy '.$value, $policy->title);
            $this->assertSame('Content '.$value, $policy->description);
            $this->assertSame($value === '1', $policy->is_active);
        }
    }

    public static function invalidStatuses(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'out of range integer' => [2],
            'invalid string' => ['yes'],
            'array' => [[1]],
        ];
    }

    #[DataProvider('invalidStatuses')]
    public function test_invalid_status_rejects_toggles_and_content_edits(mixed $value): void
    {
        $policy = Policy::firstOrFail();
        $original = $policy->getAttributes();

        foreach ([[], ['title' => 'Must not save', 'description' => 'Must not save']] as $content) {
            $this->putJson(route('admin.policies.update', $policy), $content + ['is_active' => $value])
                ->assertUnprocessable()->assertJsonValidationErrors('is_active');

            $this->assertSame($original, $policy->fresh()->getAttributes());
        }
    }

    public function test_partial_content_requests_do_not_bypass_title_validation(): void
    {
        $policy = Policy::firstOrFail();
        $original = $policy->getAttributes();

        foreach ([['description' => 'Incomplete edit'], ['title' => '']] as $content) {
            $this->putJson(route('admin.policies.update', $policy), $content + ['is_active' => 0])
                ->assertUnprocessable()->assertJsonValidationErrors('title');

            $this->assertSame($original, $policy->fresh()->getAttributes());
        }
    }

    public function test_edit_without_status_preserves_existing_status(): void
    {
        $policy = Policy::firstOrFail();
        $policy->update(['is_active' => false]);

        $this->putJson(route('admin.policies.update', $policy), [
            'title' => 'Content-only edit', 'description' => null,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.policies.index'));

        $policy->refresh();
        $this->assertSame('Content-only edit', $policy->title);
        $this->assertNull($policy->description);
        $this->assertFalse($policy->is_active);
    }
}
