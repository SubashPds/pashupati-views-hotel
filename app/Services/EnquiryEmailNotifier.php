<?php

namespace App\Services;

use App\Mail\NewEnquiry;
use App\Models\Enquiry;
use App\Models\SiteSetting;
use App\Support\EmailAddresses;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EnquiryEmailNotifier
{
    public function send(Enquiry $enquiry): void
    {
        $recipients = EmailAddresses::valid(SiteSetting::get('contact_email'));
        if (!$recipients) {
            Log::warning('Enquiry notification skipped: no valid recipient configured.', ['enquiry_id' => $enquiry->id]);
            return;
        }

        // Separate messages keep recipients private and isolate delivery failures.
        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient)->send(new NewEnquiry($enquiry));
            } catch (Throwable $exception) {
                Log::error('Enquiry notification delivery failed; enquiry remains saved.', [
                    'enquiry_id' => $enquiry->id,
                    'recipient_hash' => hash('sha256', $recipient),
                    'exception_type' => get_class($exception),
                ]);
            }
        }
    }
}
