# Changelog

All notable changes to this project are documented in this file.

## [v1.1.0] - 2026-03-08

### Changed
- Hardened `get_comics.php` by removing user-controlled root input and always resolving comics from `/comics` under the document root.
- Added explicit API response behavior in `get_comics.php` with proper JSON status handling (`200`, `304`, `404`, `500`) and short-lived list caching via `ETag` + `Cache-Control`.
- Updated `index.php` comic loading flow to remove the per-image `HEAD` preflight request and rely on image `onload`/`onerror` handling.
- Added Apache cache policy config (`apache-cache.conf`) and enabled it in the image build for long-lived comic image caching and revalidation-friendly dynamic page caching.

### Security
- Added mandatory SHA-256 archive verification in `docker-entrypoint.sh` before extraction, with startup failure on checksum mismatch.
- Added `COMICS_ARCHIVE_SHA256` configuration documentation and usage guidance in `README.md`.

## [v1.0.4] - 2026-03-01

### Fixed
- Hardened mobile panel scroll reset when switching comics to prevent browsers from keeping the previous horizontal position.
- Added repeated post-render scroll resets (animation frame + delayed passes) to reliably return to panel 1 on navigation.

## [v1.0.3] - 2026-03-01

### Fixed
- Reset comic viewport position to the first panel/part when switching to previous/next comics on mobile.
- Added an additional post-render scroll reset to ensure the panel viewer always opens at the first segment after layout updates.

## [v1.0.2] - 2026-03-01

### Changed
- Added mobile-only comic panel side-scrolling in `index.php` by detecting white vertical separators and splitting strips into swipeable panels.
- Added adaptive separator detection sensitivity with strict/normal/loose presets and fallback scanning order for more reliable panel detection across comics.
- Added automatic fallback to full-image rendering when panel detection is not reliable.
- Updated `README.md` Mobile UX documentation and verification checklist for panel swipe behavior and fallback expectations.

## [v1.0.1] - 2026-02-28

### Changed
- Improved mobile responsiveness of the comic viewer UI in `index.php`:
	- Search and navigation controls now stack cleanly on small screens.
	- Touch targets for form and navigation controls were increased for better usability.
	- Comic image sizing was adjusted to avoid overflow and preserve visibility of controls.
	- Added landscape-phone breakpoint tuning for compact-height devices.
- Updated `README.md` to document responsive UI/mobile support in the included feature set.

## [v1.0.0] - 2026-02-28

### Added
- Docker image setup for Apache + PHP deployment.
- Container startup bootstrap script to download and extract the comics archive into the web root when `comics` is missing or empty.
- Docker Compose configuration for one-command local/remote deployment.
- Complete deployment and operations documentation in `README.md`, including remote Docker host usage.
- Production deployment checklist covering firewall, DNS, TLS/reverse proxy, monitoring, and backups.
- `.gitignore` rule for `.vscode/` local settings.

### Notes
- This is the first tagged release of the containerized build.