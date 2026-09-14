# Enquiry email notifications

In **Dashboard → Settings → Contact**, enter recipients in **Email addresses (enquiry notifications)**. Separate up to 20 addresses with commas or new lines. Duplicates are removed. These addresses also appear as individual email links on the public website.

Both the contact form and Plan Your Stay form save the enquiry, then send a separate notification to every valid configured address. Notifications include guest contact details, enquiry type, message, and any submitted booking dates and guest count. Reply-To uses the guest's email when provided; the sender remains the hotel's configured address.

## Server setup

The default `log` mailer does not deliver to inboxes. Configure the server's `.env` with your email provider's SMTP details, for example:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=your-provider-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-provider-username
MAIL_PASSWORD="your-provider-password"
MAIL_FROM_ADDRESS=reservations@your-hotel-domain
MAIL_FROM_NAME="Pashupati Views Hotel"
MAIL_TIMEOUT=10
```

Use the provider's required host, port and scheme (for implicit TLS on port 465, use `MAIL_SCHEME=smtps`). Use a sender verified by that provider and set `APP_URL` to the public website URL so dashboard links work. Keep credentials out of source control. Refresh cached configuration after changing it (`php artisan config:cache` in the application container/server).

Notifications send synchronously; no queue worker is required. If a recipient fails, the remaining recipients are still attempted and the saved enquiry remains available in the dashboard. Failures are logged with enquiry ID and a hashed recipient address. There is no automatic retry. If no valid recipient is configured, the enquiry is saved and a warning is logged. The settings page displays a notice while the server uses the `log` or `array` mailer.

Tests use fake or in-memory mail transports and an isolated SQLite database; they do not send to real inboxes.
