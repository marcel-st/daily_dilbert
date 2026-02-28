# Changelog

All notable changes to this project are documented in this file.

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