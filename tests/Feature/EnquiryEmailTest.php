<?php

namespace Tests\Feature;

use App\Mail\NewEnquiry;
use App\Models\Enquiry;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class EnquiryEmailTest extends TestCase
{
    use RefreshDatabase;

    private function recipients(?string $value): void
    {
        SiteSetting::updateOrCreate(['key' => 'contact_email'], [
            'value' => $value, 'type' => 'text', 'group' => 'contact', 'label' => 'Email Address',
        ]);
    }

    private function enquiry(array $overrides = []): array
    {
        return array_replace([
            'guest_name' => 'Guest & Family', 'email' => 'guest@example.test',
            'phone' => '9800000000', 'category' => 'Room booking', 'message' => 'A quiet room, please.',
        ], $overrides);
    }

    public function test_all_recipients_receive_one_private_notification_and_settings_are_read_fresh(): void
    {
        Mail::fake();
        $this->recipients("FIRST@example.test, second@example.test\nfirst@example.test; third@example.test");
        $this->from('/')->post(route('enquire'), $this->enquiry())->assertRedirect('/')->assertSessionHas('enquiry_success');
        $this->assertDatabaseCount('enquiries', 1);
        Mail::assertSentCount(3);
        foreach (['first', 'second', 'third'] as $recipient) {
            Mail::assertSent(NewEnquiry::class, fn ($mail) => $mail->hasTo($recipient.'@example.test') && count($mail->to) === 1 && $mail->enquiry->id === Enquiry::first()->id);
        }
        $this->recipients('updated@example.test');
        $this->post(route('enquire'), $this->enquiry())->assertSessionHasNoErrors();
        Mail::assertSentCount(4);
        Mail::assertSent(NewEnquiry::class, fn ($mail) => $mail->hasTo('updated@example.test'));
    }

    public function test_booking_details_and_reply_to_survive_real_message_rendering(): void
    {
        config(['mail.default' => 'array', 'mail.from.address' => 'hotel@example.test']);
        Mail::purge();
        $this->recipients('reservations@example.test');
        $this->post(route('enquire'), $this->enquiry([
            'message' => '<script>alert(1)</script> & Deluxe room',
            'checkin' => '2026-10-01', 'checkout' => '2026-10-03', 'guests' => '5+',
        ]))->assertSessionHasNoErrors()->assertSessionHas('enquiry_success');
        $messages = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $message = $messages->first()->getOriginalMessage();
        $this->assertSame('hotel@example.test', $message->getFrom()[0]->getAddress());
        $this->assertSame('guest@example.test', $message->getReplyTo()[0]->getAddress());
        $this->assertSame('reservations@example.test', $message->getTo()[0]->getAddress());
        $this->assertStringContainsString('Guest &amp; Family', $message->getHtmlBody());
        $this->assertStringNotContainsString('<script>', $message->getHtmlBody());
        $this->assertStringContainsString('&lt;script&gt;', $message->getHtmlBody());
        $this->assertStringContainsString('Guest & Family', $message->getTextBody());
        foreach (['Check-in: 2026-10-01', 'Check-out: 2026-10-03', 'Guests: 5+'] as $detail) {
            $this->assertStringContainsString($detail, Enquiry::first()->message);
            $this->assertStringContainsString($detail, $message->getTextBody());
        }
        $this->assertStringContainsString(route('admin.enquiries.show', Enquiry::first()), $message->getHtmlBody());
    }

    public function test_phone_only_enquiry_sends_without_reply_to(): void
    {
        Mail::fake();
        $this->recipients('hotel@example.test');
        $this->post(route('enquire'), $this->enquiry(['email' => null]))->assertSessionHasNoErrors()->assertSessionHas('enquiry_success');
        Mail::assertSent(NewEnquiry::class, fn ($mail) => $mail->envelope()->replyTo === []);
    }

    public function test_invalid_submissions_do_not_save_or_send(): void
    {
        Mail::fake();
        $this->recipients('hotel@example.test');
        foreach ([
            [['guest_name' => ''], 'guest_name'], [['email' => 'invalid'], 'email'],
            [['email' => null, 'phone' => null], 'contact'], [['checkin' => 'invalid'], 'checkin'],
            [['checkin' => '2026-10-03', 'checkout' => '2026-10-01'], 'checkout'], [['guests' => '100'], 'guests'],
        ] as [$fields, $error]) {
            $this->post(route('enquire'), $this->enquiry($fields))->assertSessionHasErrors($error);
        }
        $this->assertDatabaseCount('enquiries', 0);
        Mail::assertNothingSent();
    }

    public function test_missing_or_invalid_legacy_recipients_do_not_lose_enquiries(): void
    {
        Mail::fake();
        $this->post(route('enquire'), $this->enquiry())->assertSessionHas('enquiry_success');
        $this->recipients('invalid; also-invalid');
        $this->post(route('enquire'), $this->enquiry())->assertSessionHas('enquiry_success');
        $this->assertDatabaseCount('enquiries', 2);
        Mail::assertNothingSent();
    }

    public function test_failed_recipient_does_not_block_other_recipients_or_save(): void
    {
        $this->recipients('first@example.test, second@example.test');
        $failed = Mockery::mock(PendingMail::class);
        $failed->shouldReceive('send')->once()->with(Mockery::type(NewEnquiry::class))->andThrow(new RuntimeException('SMTP unavailable'));
        $successful = Mockery::mock(PendingMail::class);
        $successful->shouldReceive('send')->once()->with(Mockery::type(NewEnquiry::class));
        Mail::shouldReceive('to')->once()->with('first@example.test')->andReturn($failed);
        Mail::shouldReceive('to')->once()->with('second@example.test')->andReturn($successful);
        Log::spy();
        $this->post(route('enquire'), $this->enquiry())->assertSessionHasNoErrors()->assertSessionHas('enquiry_success');
        $this->assertDatabaseCount('enquiries', 1);
        Log::shouldHaveReceived('error')->once()->withArgs(fn ($message, $context) => $context['enquiry_id'] === Enquiry::first()->id && $context['recipient_hash'] === hash('sha256', 'first@example.test'));
    }

    public function test_settings_validate_recipient_list_and_public_links_are_individual(): void
    {
        $this->actingAs(User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password', 'role' => 'superadmin', 'is_active' => true]));
        $this->get(route('admin.settings.index'))->assertOk()->assertSee('Email addresses (enquiry notifications)');
        $this->post(route('admin.settings.update'), ['contact_email' => "ONE@example.test\ntwo@example.test; one@example.test"])->assertSessionHasNoErrors();
        $this->assertSame('one@example.test, two@example.test', SiteSetting::get('contact_email'));
        $this->get('/')->assertOk()->assertSee('mailto:one@example.test')->assertSee('mailto:two@example.test')->assertDontSee('mailto:one@example.test,');
        foreach (['valid@example.test, invalid', ['array@example.test'], implode(',', array_map(fn ($i) => "hotel$i@example.test", range(1, 21)))] as $invalid) {
            $this->post(route('admin.settings.update'), ['contact_email' => $invalid])->assertSessionHasErrors('contact_email');
            $this->assertSame('one@example.test, two@example.test', SiteSetting::get('contact_email'));
        }
        $this->post(route('admin.settings.update'), ['contact_email' => ''])->assertSessionHasNoErrors();
        $this->assertSame('', SiteSetting::get('contact_email'));
    }
}
