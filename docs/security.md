# Security hardening and deployment

The application now uses a nonce-based Content Security Policy, rejects inline JavaScript event handlers, sanitizes policy HTML using a DOM allowlist, prevents framing and MIME sniffing, limits login attempts by IP and account, caps media batches, and rate-limits detail requests. Production configuration disables Laravel debug output and defaults session cookies to Secure. TLS responses include HSTS. Existing CSRF protection, parameterized database queries, role permissions, and session rotation remain in place.

These code changes do not deploy the site, rotate existing credentials, install a firewall, or guarantee protection from every attack.

## Before deploying

- Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-real-domain`, and `SESSION_SECURE_COOKIE=true`. Set `TRUSTED_HOSTS` to the exact comma-separated public hostnames if you serve more than the `APP_URL` hostname. Ensure the server serves only the repository's `public/` directory.
- Terminate HTTPS at the server or trusted edge. Configure `TRUSTED_PROXIES` with only the actual proxy IP addresses/CIDRs. Configure Nginx's real-IP handling for the same trusted edge before using per-IP limits. Do not use a wildcard to trust client-supplied forwarding headers.
- The old superadmin seeder embedded a fixed password. **Change that account's password immediately if the seed was used.** This change does not reset or lock out existing accounts. New seeding requires a unique `ADMIN_PASSWORD` of at least 15 characters (maximum 72 bytes with bcrypt); `ADMIN_EMAIL` optionally chooses the initial account. Re-seeding does not change existing accounts. Remove the bootstrap password from the environment after provisioning and rebuild the config cache.
- Set a unique `DB_PASSWORD` and a separate `DB_ROOT_PASSWORD`. The Docker database port now binds only to localhost. Docker's initial-password variables do not rotate accounts in an existing database volume; rotate those credentials separately and update application configuration together.
- Deploy the updated Nginx configuration, validate it with `nginx -t`, then reload gracefully. It executes only `public/index.php`, denies hidden/sensitive files, and serves only approved media extensions from `/storage`. The Apache rules provide equivalent path restrictions when `.htaccess` is enabled.
- Run `composer install --no-dev --optimize-autoloader`, `npm ci`, `npm run build`, then `php artisan optimize`. Remove a stale `public/hot` file before production deployment. Keep `storage` and `bootstrap/cache` writable only by the application account; keep `.env` and cached configuration private.
- Run `composer audit --locked` and `npm audit` in a network-enabled environment. Both full audits were blocked by DNS/network access in this workspace; dependencies have **not** been certified free of known vulnerabilities. Rebuild supported PHP, Nginx, MySQL, OS, and image-processing components regularly rather than retaining old images.

## Hosting protections still needed

Enable a managed WAF and DDoS protection at the edge, restrict origin access to the edge where possible, and rate-limit abusive traffic before PHP. Use MFA for hosting, domain, source-control, database administration, and CDN accounts; restrict dashboard access through a VPN or an identity-aware gateway where practical. Application MFA is not implemented by this change.

Keep encrypted off-site backups, test restores, monitor failed logins and unusual uploads, and alert on unexpected deployments or file changes. Never log passwords, session cookies, or secret configuration. Automated application limits cannot stop attacks that saturate the network.

## Validation

Database-free security tests cover encoded HTML payloads, URL/attribute allowlists, password boundaries, security headers, CSP nonces, and login-account rate limiting. Full database feature tests require the SQLite PDO extension. Browser checks and server configuration reloads must be performed in the deployment environment; they were not available in this workspace during this change.

References: [OWASP Laravel guidance](https://cheatsheetseries.owasp.org/cheatsheets/Laravel_Cheat_Sheet.html), [OWASP authentication guidance](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html), and [OWASP file upload guidance](https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html).
