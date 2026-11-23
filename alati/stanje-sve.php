<?php
/** Jedan pregled (samo CLI, samo čitanje): stanje FB/IG objava i Meta tokena. */
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }
echo "===== OBJAVE =====\n"; passthru(PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/dr-stanje.php'));
echo "\n===== TOKENI =====\n"; passthru(PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/meta-dozvole.php'));
