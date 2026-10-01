# Verification results — 28 September 2026

## Standalone runtime

`python3 test_app.py`: **4 tests passed** using an isolated temporary SQLite database and HTTP server.

- Main pages return HTML; missing page returns 404.
- Missing/invalid session token is rejected with 403.
- Invalid email, insufficient message and missing required choices/consent are rejected with 400.
- Valid form persists to SQLite; injected markup is escaped in the inbox; CSV formula prefixes are neutralized; review status is updated; deletion removes the record.

## Actual WordPress runtime

Official WordPress image, PHP 8.3 image family, WordPress **7.1.2**, MariaDB image. Images pinned by digest in `compose.yaml`.

`python3 test_wordpress.py`: **8 integration checks passed**.

- Original theme homepage active.
- Contact shortcode and nonce render.
- Missing nonce rejected.
- Valid labeled test inquiry saved with truthful confirmation.
- Authenticated WordPress staff inbox shows the record.
- Review state persists.
- Authenticated CSV export contains the record.
- Test inquiries deleted afterward.

The first test attempt exposed an HTML-entity decoding mistake in the test’s export-link parser; it was corrected and the complete check passed on rerun. No test inquiries are retained.

## Limits

No public deployment, deliverability, load, penetration, formal accessibility or conversion testing is claimed. Browser visual checks and screenshots are recorded separately by the port assembly workflow. Tests verify local functionality, not business impact.

## Additional browser verification

Browser capture verified actual WordPress pages. The library bookmark action produced a saved list containing the selected article; the service form stored a labeled verification inquiry and showed a success confirmation. Each relevant action was checked on its own site. Both WordPress sites were inspected at a 390-pixel viewport with document width also 390 pixels. Screenshots are in `screenshots/`.
