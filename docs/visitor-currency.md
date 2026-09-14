# Visitor currency

Dashboard → Site Settings → Currency controls the display rates. Enter the amount in NPR corresponding to one INR or USD. Room prices and numeric package prices remain stored in NPR; a converted amount is `NPR amount / configured rate`.

The initial INR reference is 1.60 NPR. The initial USD reference is 153.01 NPR, the [Nepal Rastra Bank published USD selling rate for 11 September 2026](https://www.nrb.org.np/forex/). These are starting values, not automatic live exchange rates. Set the hotel's desired rates in the dashboard; changes take effect on the next page request.

## Country selection

- Nepal (`NP`): NPR.
- India (`IN`): INR.
- All other countries or failed lookups: USD.
- Local/private network previews: NPR, since their IPs cannot be geolocated.

Public visitor IPs are looked up server-side using the [GeoJS country endpoint](https://www.geojs.io/docs/v1/endpoints/country/), without an API key. Results are cached for 24 hours under hashed IP keys. Failed lookups use USD and retry after five minutes. No location-permission popup is shown. A VPN or proxy may affect the inferred country.

For a reverse proxy or CDN, configure Laravel's `TRUSTED_PROXIES` with that proxy's actual IP addresses/CIDRs so `request()->ip()` resolves the visitor correctly. Do not trust arbitrary forwarding headers. If the trusted edge supplies a country, set `CURRENCY_COUNTRY_HEADER=CF-IPCountry` (or the relevant provider header); country headers from untrusted peers are ignored. A local nginx → PHP-FPM deployment already passes the remote address through FastCGI.

Optional configuration:

```dotenv
CURRENCY_GEOIP_ENABLED=true
CURRENCY_CACHE_STORE=file
```

Frontend HTML is marked private/no-store so shared caches do not mix currencies between countries. Country detection runs only on public pages; dashboard prices remain in their original currency.

Room cards/details and package cards/details share the same formatter. Service price labels also convert recognized NPR/INR/USD amounts. Explicit currencies, units, ranges and descriptive numbers are handled separately: `NPR 1,600 / person` converts the amount, while `20% off for 2 nights` remains text. Unsupported foreign currency labels such as EUR remain explicit rather than being reinterpreted as NPR. Use NPR prices for automatic conversion. Freeform descriptions and advertisement text/images are not rewritten.

Tests use mocked GeoJS results and isolated SQLite; no production visitors or prices are modified.
