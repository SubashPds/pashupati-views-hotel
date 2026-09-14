<!doctype html>
<html lang="en">
<body style="margin:0;background:#faf8f3;font-family:Arial,sans-serif;color:#0d1b2a;">
    <div style="max-width:600px;margin:24px auto;padding:28px;background:#ffffff;border:1px solid #e8ddc6;border-radius:12px;">
        <p style="margin:0 0 10px;color:#856534;font-size:12px;letter-spacing:1px;">WEBSITE ENQUIRY #{{ $enquiry->id }}</p>
        <h1 style="margin:0 0 20px;font-size:24px;">A guest has contacted the hotel</h1>
        <table role="presentation" style="width:100%;border-collapse:collapse;font-size:14px;">
            @foreach(['Guest' => $enquiry->guest_name, 'Email' => $enquiry->email ?: 'Not provided', 'Phone' => $enquiry->phone ?: 'Not provided', 'Enquiry type' => $enquiry->category ?: 'General enquiry', 'Submitted (Nepal time)' => $enquiry->created_at->copy()->timezone('Asia/Kathmandu')->format('j M Y, g:i A')] as $label => $value)
                <tr><th style="text-align:left;vertical-align:top;padding:8px 12px 8px 0;color:#666;font-weight:normal;">{{ $label }}</th><td style="padding:8px 0;overflow-wrap:anywhere;">{{ $value }}</td></tr>
            @endforeach
        </table>
        <h2 style="margin:24px 0 8px;font-size:16px;">Message / stay details</h2>
        <div style="padding:16px;background:#faf8f3;border-radius:8px;line-height:1.6;overflow-wrap:anywhere;">{!! nl2br(e($enquiry->message ?: 'No message provided.')) !!}</div>
        <p style="margin:24px 0;"><a href="{{ route('admin.enquiries.show', $enquiry) }}" style="display:inline-block;padding:12px 18px;background:#856534;color:#fff;text-decoration:none;border-radius:8px;">View enquiry in dashboard</a></p>
        <p style="color:#666;font-size:12px;">{{ $enquiry->email ? 'Reply to this email to contact the guest.' : 'The guest did not provide an email address. Use their phone number to respond.' }}</p>
    </div>
</body>
</html>
