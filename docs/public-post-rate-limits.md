# Public POST rate limits

The application limits attempts by client IP, across sessions and both contact/booking forms. Invalid submissions count too. Browsing and dashboard content changes are outside these limits.

| Action | Per minute | Per hour |
| --- | ---: | ---: |
| Contact, booking, package enquiries (`/enquire`) | 5 | 20 |
| Country/currency preference (`/currency`) | 20 | 100 |
| Login (`/login`) | 5 | 30 |
| Logout (`/logout`) | 20 | 100 |

The named limiters live in `app/Providers/AppServiceProvider.php`. Blocked requests receive HTTP 429, `Retry-After`, and private/no-store headers before controller processing. AJAX forms retain inputs and show a retry message; the contact and booking forms share their browser cooldown. There is no automatic resend.

`RATE_LIMITER_STORE` defaults to `CACHE_STORE` (database by default). Use a persistent store shared by all app instances in production, such as the same database or Redis; never use `array` or `null`. Tests explicitly use an isolated array store.

The supplied Nginx configuration also limits the combined public POST routes to an average of one request per second per IP, with a burst allowance of 10 and immediate 429 responses for excess traffic. This layer runs before PHP, including for requests without a valid CSRF token. Deploy `nginx/default.conf`, validate with `nginx -t`, then gracefully reload Nginx. Laravel limits still apply when serving the app through a different web server.

If a CDN/load balancer sits in front of the site, configure Nginx's real-IP handling and Laravel's `TRUSTED_PROXIES` for only the actual trusted proxy addresses. Never trust arbitrary client-supplied IP headers. Otherwise visitors behind a proxy may share a quota. Visitors sharing a public IP (for example, office Wi-Fi) also share these limits.

These controls reduce form spam and request bursts. They do not make the site immune to distributed or bandwidth-exhausting attacks; production still needs hosting/CDN DDoS protection and traffic monitoring. See [OWASP's denial-of-service guidance](https://cheatsheetseries.owasp.org/cheatsheets/Denial_of_Service_Cheat_Sheet.html).

Verification: `php artisan test --filter='PublicPostRateLimitTest|EnquiryEmailTest|VisitorCurrencyTest'` and `node tests/browser/frontend-forms.mjs` (the browser check intercepts enquiry submissions and sends no emails).
