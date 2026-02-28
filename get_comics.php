<?php
// get_comics.php
//ini_set('display_errors', 1); //Uncomment to debug
//error_reporting(E_ALL);  //Uncomment to debug

if (!isset($_GET['root']) || empty($_GET['root'])) {
    sendError('Root directory not specified.');
}

$root = $_GET['root'];

// Sanitize the root path.  This is CRITICAL.
$root = rtrim($root, '/'); // Remove trailing slash
$root = str_replace('../', '', $root); // Prevent directory traversal
if (empty($root)) {
     sendError('Root directory is invalid.');
}
$realRoot = realpath($_SERVER['DOCUMENT_ROOT'] . $root);
if ($realRoot === false) {
    sendError('Root directory does not exist or is not a directory');
}
if (strpos($realRoot, realpath($_SERVER['DOCUMENT_ROOT'])) !== 0) {
    sendError('Root directory is outside the allowed path.');
}


$comicFiles = [];
for ($year = 1989; $year <= 2023; $year++) {
    $yearDir = $realRoot . '/' . $year;
    if (is_dir($yearDir)) {
        $files = scandir($yearDir);
        if ($files !== false) {
            foreach ($files as $file) {
                // Check if the file matches the expected format YYYY-MM-DD-description.gif
                if (preg_match('/^\d{4}-\d{2}-\d{2}_.*\.gif$/i', $file)) {
                    $comicFiles[] = $year . '/' . $file; // Store with year
                }
            }
        }
    }
}


if (empty($comicFiles)) {
    echo json_encode(['files' => []]);
} else {
    echo json_encode(['files' => $comicFiles]);
}

function sendError($message) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $message]);
    exit;
}
?>
