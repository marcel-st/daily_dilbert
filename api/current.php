<?php

declare(strict_types=1);

// Native clients can call this endpoint without query params to get the latest comic.
// If callers provide date/latest explicitly, keep those values and delegate unchanged.
if (!isset($_GET['date']) && !isset($_GET['latest'])) {
    $_GET['latest'] = '1';
}

require __DIR__ . '/comic.php';
