<?php

declare(strict_types=1);

$documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);
if ($documentRoot === false) {
    sendJsonError('Server document root is unavailable.', 500);
}

$comicsRoot = realpath($documentRoot . '/comics');
if ($comicsRoot === false || !is_dir($comicsRoot)) {
    sendJsonError('Comics directory not found.', 404);
}

if (strpos($comicsRoot, $documentRoot) !== 0) {
    sendJsonError('Comics path is outside the allowed root.', 500);
}

$dateParam = $_GET['date'] ?? null;
$latestParam = $_GET['latest'] ?? null;
$downloadParam = $_GET['download'] ?? null;
$download = $downloadParam === '1' || $downloadParam === 'true';

if ($dateParam !== null && $latestParam !== null) {
    sendJsonError('Use either date or latest, not both.', 400);
}

if ($dateParam === null && $latestParam === null) {
    sendJsonError('Missing required query: date=YYYY-MM-DD or latest=1', 400);
}

$relativePath = null;

if ($latestParam !== null) {
    $relativePath = findLatestComicPath($comicsRoot);
    if ($relativePath === null) {
        sendJsonError('No comics available.', 404);
    }
} else {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$dateParam)) {
        sendJsonError('Invalid date format. Expected YYYY-MM-DD.', 400);
    }

    $year = substr((string)$dateParam, 0, 4);
    $yearDir = $comicsRoot . '/' . $year;
    if (!is_dir($yearDir)) {
        sendJsonError('Comic not found for specified date.', 404);
    }

    $matches = glob($yearDir . '/' . $dateParam . '_*.gif');
    if ($matches === false || count($matches) === 0) {
        sendJsonError('Comic not found for specified date.', 404);
    }

    sort($matches, SORT_STRING);
    $relativePath = $year . '/' . basename($matches[0]);
}

$absolutePath = realpath($comicsRoot . '/' . $relativePath);
if ($absolutePath === false || strpos($absolutePath, $comicsRoot) !== 0 || !is_file($absolutePath)) {
    sendJsonError('Resolved comic path is invalid.', 500);
}

$lastModified = filemtime($absolutePath);
if ($lastModified !== false) {
    $lastModifiedHttp = gmdate('D, d M Y H:i:s', $lastModified) . ' GMT';
    header('Last-Modified: ' . $lastModifiedHttp);
}

$etag = '"' . sha1($relativePath . '|' . (string)$lastModified) . '"';
header('ETag: ' . $etag);
header('Cache-Control: public, max-age=86400');

$ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
if (trim($ifNoneMatch) === $etag) {
    http_response_code(304);
    exit;
}

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $lastModified !== false) {
    $ifModifiedSince = strtotime((string)$_SERVER['HTTP_IF_MODIFIED_SINCE']);
    if ($ifModifiedSince !== false && $ifModifiedSince >= $lastModified) {
        http_response_code(304);
        exit;
    }
}

$filename = basename($absolutePath);
header('Content-Type: image/gif');
header('Content-Length: ' . (string) filesize($absolutePath));
header('X-Comic-Path: ' . $relativePath);
if ($download) {
    header('Content-Disposition: attachment; filename="' . $filename . '"');
} else {
    header('Content-Disposition: inline; filename="' . $filename . '"');
}

$stream = fopen($absolutePath, 'rb');
if ($stream === false) {
    sendJsonError('Failed to read comic file.', 500);
}

while (!feof($stream)) {
    $chunk = fread($stream, 8192);
    if ($chunk === false) {
        fclose($stream);
        sendJsonError('Failed during comic streaming.', 500);
    }
    echo $chunk;
}

fclose($stream);
exit;

function findLatestComicPath(string $comicsRoot): ?string {
    $latestRelativePath = null;
    $latestDate = null;

    for ($year = 1989; $year <= 2023; $year++) {
        $yearDir = $comicsRoot . '/' . $year;
        if (!is_dir($yearDir)) {
            continue;
        }

        $entries = scandir($yearDir);
        if ($entries === false) {
            continue;
        }

        foreach ($entries as $entry) {
            if (!preg_match('/^(\d{4}-\d{2}-\d{2})_.*\.gif$/i', $entry, $matches)) {
                continue;
            }

            $date = $matches[1];
            if ($latestDate === null || strcmp($date, $latestDate) > 0) {
                $latestDate = $date;
                $latestRelativePath = $year . '/' . $entry;
            }
        }
    }

    return $latestRelativePath;
}

function sendJsonError(string $message, int $statusCode): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['error' => $message], JSON_UNESCAPED_SLASHES);
    exit;
}
