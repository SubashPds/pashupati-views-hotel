New website enquiry #{!! $enquiry->id !!}

Guest: {!! $enquiry->guest_name !!}
Email: {!! $enquiry->email ?: 'Not provided' !!}
Phone: {!! $enquiry->phone ?: 'Not provided' !!}
Enquiry type: {!! $enquiry->category ?: 'General enquiry' !!}
Submitted (Nepal time): {!! $enquiry->created_at->copy()->timezone('Asia/Kathmandu')->format('j M Y, g:i A') !!}

Message / stay details:
{!! $enquiry->message ?: 'No message provided.' !!}

View enquiry: {!! route('admin.enquiries.show', $enquiry) !!}

{!! $enquiry->email ? 'Reply to this email to contact the guest.' : 'The guest did not provide an email address. Use their phone number to respond.' !!}
