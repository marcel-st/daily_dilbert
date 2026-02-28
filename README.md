# Daily Dilbert

Dockerized PHP/Apache deployment for the Daily Dilbert viewer.

At container startup, the entrypoint checks for comics in `/var/www/html/comics`.
If the folder is missing or empty, it downloads and extracts:

- `https://cds.xocloud.nl/mta1ujxiq8ln1rl/dilbert-comics.tgz`

## What is included

- Apache + PHP runtime image (`php:8.3-apache`)
- Startup bootstrap script that downloads/extracts the comics archive
- Docker Compose service for one-command deploys

## Requirements

- Docker Engine 24+ (or compatible)
- Docker Compose v2 (`docker compose`)
- Network access from the container to the comics archive URL

## Quick start (recommended: Compose)

From this folder:

```bash
docker compose up -d --build
```

Then open:

- `http://localhost`

Stop and remove container/network:

```bash
docker compose down
```

## Quick start (plain Docker)

Build image:

```bash
docker build -t daily-dilbert:latest .
```

Run container:

```bash
docker run -d --restart unless-stopped -p 80:80 --name daily-dilbert daily-dilbert:latest
```

## Deploy to a remote Docker host

If your daemon is reachable over SSH:

```bash
export DOCKER_HOST=ssh://user@remote-host
docker compose up -d --build
```

Or with plain Docker:

```bash
export DOCKER_HOST=ssh://user@remote-host
docker build -t daily-dilbert:latest .
docker run -d --restart unless-stopped -p 80:80 --name daily-dilbert daily-dilbert:latest
```

## Configuration

Environment variables supported by the container:

- `COMICS_ARCHIVE_URL` (default: `https://cds.xocloud.nl/mta1ujxiq8ln1rl/dilbert-comics.tgz`)
- `WEB_ROOT` (default: `/var/www/html`)

Example:

```bash
docker run -d --restart unless-stopped -p 80:80 \
  -e COMICS_ARCHIVE_URL=https://example.org/dilbert-comics.tgz \
  --name daily-dilbert daily-dilbert:latest
```

## Updating comics data

Comics are downloaded only when `/var/www/html/comics` is missing or empty.

To force a fresh download:

1. Remove the running container.
2. Start a new container from the same image.

If you use persistent volumes for `/var/www/html/comics`, clear that volume content first.

## Operational commands

Show logs:

```bash
docker logs -f daily-dilbert
```

Restart service (Compose):

```bash
docker compose restart
```

Rebuild after code changes:

```bash
docker compose up -d --build
```

## Troubleshooting

- `permission denied /var/run/docker.sock`: run commands with a user that has Docker daemon access.
- comics not showing: check container logs and verify outbound access to `cds.xocloud.nl`.
- port already in use: change host binding, for example `-p 8080:80`.

## Server deployment checklist

Use this checklist when deploying to production.

### 1) Host and network

- [ ] Docker Engine and Compose installed and updated.
- [ ] Only required inbound ports are open:
  - `22/tcp` (SSH, restricted)
  - `80/tcp` and `443/tcp` (web)
- [ ] Outbound HTTPS (`443/tcp`) allowed so the container can download the comics archive.
- [ ] Time sync enabled (`systemd-timesyncd`/NTP).

### 2) DNS

- [ ] `A`/`AAAA` record points your domain to the server.
- [ ] DNS propagated before enabling TLS.

### 3) Reverse proxy and TLS

- [ ] Put the app behind a reverse proxy (Nginx, Caddy, or Traefik).
- [ ] Enable HTTPS with a valid certificate (for example Let’s Encrypt).
- [ ] Redirect HTTP to HTTPS.
- [ ] Set `X-Forwarded-Proto` and `Host` headers correctly in proxy config.

### 4) Runtime and persistence

- [ ] Container is started with restart policy (`unless-stopped`).
- [ ] Decide persistence strategy for comics data:
  - no volume: comics downloaded on each fresh container
  - named/bind volume: comics cached across restarts/redeploys
- [ ] Image and deployment commands are documented in your ops runbook.

### 5) Security hardening

- [ ] SSH key auth only; password login disabled.
- [ ] Root login via SSH disabled.
- [ ] Host firewall enabled (`ufw`/`nftables`).
- [ ] Automatic OS security updates enabled.
- [ ] Reverse proxy adds basic security headers (`HSTS`, `X-Content-Type-Options`, `X-Frame-Options`).

### 6) Monitoring and backups

- [ ] Container logs are collected/rotated (`docker logs`, journald, or external logging).
- [ ] Basic uptime check configured (HTTP health endpoint or homepage check).
- [ ] Backups defined for any persistent volumes and proxy config.
- [ ] Restore procedure tested at least once.

### 7) Post-deploy validation

- [ ] `docker compose ps` shows service healthy/running.
- [ ] Homepage loads over HTTPS.
- [ ] Comics list loads and image navigation works.
- [ ] Restart test succeeds (`docker compose restart`).

## Project files

- `Dockerfile`: image definition
- `docker-entrypoint.sh`: startup archive download/extract logic
- `docker-compose.yml`: compose deployment definition
- `index.php`, `get_comics.php`: web application