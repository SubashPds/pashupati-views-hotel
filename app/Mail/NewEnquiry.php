<?php

namespace App\Mail;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewEnquiry extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enquiry $enquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New website enquiry #'.$this->enquiry->id,
            replyTo: $this->enquiry->email ? [new Address($this->enquiry->email)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.new-enquiry', text: 'emails.new-enquiry-text');
    }
}
