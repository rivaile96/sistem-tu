<?php
// Temporary maintenance script — REMOVE AFTER USE
$token = $_GET['token'] ?? '';
if ($token !== 'tu-cache-rebuild-2026') {
    http_response_code(403);
    die('Forbidden');
}

$base = dirname(__DIR__);
chdir($base);

// Remove stale config cache
$cacheFile = $base . '/bootstrap/cache/config.php';
if (file_exists($cacheFile)) {
    unlink($cacheFile);
    echo "Deleted: $cacheFile\n";
} else {
    echo "Cache file not found (already clear)\n";
}

// Rebuild config cache via artisan
$output = shell_exec('cd ' . escapeshellarg($base) . ' && php artisan config:cache 2>&1');
echo "Artisan output:\n" . $output . "\n";

// Self-destruct
unlink(__FILE__);
echo "Self-destructed.\n";
