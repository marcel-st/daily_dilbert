#!/bin/sh
set -eu

COMICS_ARCHIVE_URL="${COMICS_ARCHIVE_URL:-https://cds.xocloud.nl/mta1ujxiq8ln1rl/dilbert-comics.tgz}"
COMICS_ARCHIVE_SHA256="${COMICS_ARCHIVE_SHA256:-521749868533259f1a5d78baa594202bc8362cf9b60b35c0d315100f4a41a87e}"
WEB_ROOT="${WEB_ROOT:-/var/www/html}"
COMICS_DIR="${WEB_ROOT}/comics"
TMP_ARCHIVE="/tmp/dilbert-comics.tgz"

needs_download=0

if [ ! -d "${COMICS_DIR}" ]; then
    needs_download=1
elif [ -z "$(find "${COMICS_DIR}" -mindepth 1 -print -quit 2>/dev/null)" ]; then
    needs_download=1
fi

if [ "${needs_download}" -eq 1 ]; then
    echo "Downloading comics archive from ${COMICS_ARCHIVE_URL}"
    curl --fail --show-error --silent --location "${COMICS_ARCHIVE_URL}" --output "${TMP_ARCHIVE}"

    if [ -z "${COMICS_ARCHIVE_SHA256}" ]; then
        echo "COMICS_ARCHIVE_SHA256 is empty; refusing to extract unverified archive." >&2
        rm -f "${TMP_ARCHIVE}"
        exit 1
    fi

    echo "Verifying comics archive SHA-256"
    if ! printf '%s  %s\n' "${COMICS_ARCHIVE_SHA256}" "${TMP_ARCHIVE}" | sha256sum -c - >/dev/null 2>&1; then
        echo "Archive checksum verification failed." >&2
        rm -f "${TMP_ARCHIVE}"
        exit 1
    fi

    echo "Extracting comics archive into ${WEB_ROOT}"
    tar -xzf "${TMP_ARCHIVE}" -C "${WEB_ROOT}"
    rm -f "${TMP_ARCHIVE}"
fi

exec docker-php-entrypoint "$@"