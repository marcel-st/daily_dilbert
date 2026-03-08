## v1.1.0 — Security hardening and performance improvements

This release focuses on tightening backend safety, improving startup integrity checks, and reducing unnecessary client/network overhead.

### Highlights

- Hardened `get_comics.php`:
  - Removed user-controlled `root` parameter
  - Always resolves comics from `/comics` under document root
  - Added explicit JSON response codes (`200`, `304`, `404`, `500`)
  - Added `ETag` and short-lived API caching (`Cache-Control: public, max-age=300`)
- Added startup archive integrity verification in `docker-entrypoint.sh`:
  - New `COMICS_ARCHIVE_SHA256` environment variable
  - Startup fails if checksum verification does not match
- Optimized comic loading in `index.php`:
  - Removed per-image `HEAD` preflight request
  - Preserved existing `onload`/`onerror` UX behavior
- Added Apache cache policy (`apache-cache.conf`), enabled in image build:
  - `*.gif` → `Cache-Control: public, max-age=31536000, immutable`
  - `*.php`, `*.html` → `Cache-Control: no-cache, private, must-revalidate`

### Documentation and versioning

- Added changelog entry for `v1.1.0`
- Updated deployment examples to image tag `daily-dilbert:v1.1.0`
- Added release PR template file for this version

### Validation

- `php -l get_comics.php` passed
- `php -l index.php` passed
- `sh -n docker-entrypoint.sh` passed
- Docker image build succeeded with cache/header config enabled
- Apache config test (`apachectl -t`) returned `Syntax OK`

### Upgrade notes

- If you override `COMICS_ARCHIVE_URL`, also set matching `COMICS_ARCHIVE_SHA256`.
- Recommended image tag for this release: `daily-dilbert:v1.1.0`.
