## Summary

This release hardens the Daily Dilbert deployment and improves runtime efficiency.

## What changed

- Hardened API path handling in `get_comics.php`:
  - Removed user-controlled `root` input
  - Always resolves comics from `/comics` under document root
  - Added explicit JSON status handling (`200`, `304`, `404`, `500`)
  - Added `ETag` + `Cache-Control` for comic list responses
- Added mandatory archive checksum verification in `docker-entrypoint.sh`:
  - New `COMICS_ARCHIVE_SHA256` env var
  - Container exits on checksum mismatch
- Optimized comic loading in `index.php`:
  - Removed redundant per-image `HEAD` request
  - Keeps existing `onload`/`onerror` UX behavior
- Added Apache cache policy via `apache-cache.conf`:
  - `*.gif`: `public, max-age=31536000, immutable`
  - `*.php`, `*.html`: `no-cache, private, must-revalidate`
  - Enabled `mod_headers` and Apache conf in image build

## Security impact

- Reduces path-manipulation attack surface on comic list endpoint.
- Adds supply-chain integrity verification before archive extraction.

## Performance impact

- Reduces network chatter during comic navigation by removing extra HEAD calls.
- Improves client-side cache hit rate for immutable comic assets.

## Documentation updates

- Updated `README.md` with checksum configuration and HTTP caching behavior.
- Added `CHANGELOG.md` entry for `v1.1.0`.

## Validation performed

- `php -l get_comics.php` → no syntax errors
- `php -l index.php` → no syntax errors
- `sh -n docker-entrypoint.sh` → no syntax errors
- `docker build -t daily-dilbert:test-cache .` → success
- `docker run --rm --entrypoint apachectl daily-dilbert:test-cache -t` → `Syntax OK`
- `docker run --rm --entrypoint apachectl daily-dilbert:test-cache -M | grep headers_module` → module enabled

## Release notes

Target tag: `v1.1.0`
