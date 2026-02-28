#!/bin/sh
set -eu

COMICS_ARCHIVE_URL="${COMICS_ARCHIVE_URL:-https://cds.xocloud.nl/mta1ujxiq8ln1rl/dilbert-comics.tgz}"
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

    echo "Extracting comics archive into ${WEB_ROOT}"
    tar -xzf "${TMP_ARCHIVE}" -C "${WEB_ROOT}"
    rm -f "${TMP_ARCHIVE}"
fi

exec docker-php-entrypoint "$@"