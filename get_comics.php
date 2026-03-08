<?php
// get_comics.php
//ini_set('display_errors', 1); //Uncomment to debug
//error_reporting(E_ALL);  //Uncomment to debug

declare(strict_types=1);

$documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);
if ($documentRoot === false) {
    sendError('Server document root is unavailable.', 500);
}

$comicsRoot = realpath($documentRoot . '/comics');
if ($comicsRoot === false || !is_dir($comicsRoot)) {
    sendError('Comics directory does not exist or is not a directory.', 404);
}

if (strpos($comicsRoot, $documentRoot) !== 0) {
    sendError('Comics directory is outside the allowed path.', 500);
}

$comicFiles = [];
for ($year = 1989; $year <= 2023; $year++) {
    $yearDir = $comicsRoot . '/' . $year;
    if (!is_dir($yearDir)) {
        continue;
    }

    $files = scandir($yearDir);
    if ($files === false) {
        sendError('Failed to read comics directory.', 500);
    }

    foreach ($files as $file) {
        if (preg_match('/^\d{4}-\d{2}-\d{2}_.*\.gif$/i', $file)) {
            $comicFiles[] = $year . '/' . $file;
        }
    }
}

sort($comicFiles, SORT_STRING);

$responsePayload = json_encode(['files' => $comicFiles], JSON_UNESCAPED_SLASHES);
if ($responsePayload === false) {
    sendError('Failed to encode comics response.', 500);
}

$etag = '"' . sha1($responsePayload) . '"';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');
header('ETag: ' . $etag);

$ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
if (trim($ifNoneMatch) === $etag) {
    http_response_code(304);
    exit;
}

http_response_code(200);
echo $responsePayload;

function sendError(string $message, int $statusCode): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['error' => $message], JSON_UNESCAPED_SLASHES);
    exit;
}
?>
