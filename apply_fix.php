<?php
/**
 * Single-file fix executor for mentorskaratelive
 * Run via CLI: php apply_fix.php
 * Or access via browser: https://yourdomain.com/apply_fix.php (delete after running)
 */

if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain');
}

echo "========================================\n";
echo "Applying mentorskaratelive Export & Amount Patch\n";
echo "========================================\n\n";

// Bootstrap Laravel
$autoload = __DIR__ . '/vendor/autoload.php';
$appFile = __DIR__ . '/bootstrap/app.php';

if (!file_exists($autoload) || !file_exists($appFile)) {
    die("ERROR: Please place and run this script in your Laravel project root directory (where artisan is located).\n");
}

require $autoload;
$app = require_once $appFile;
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

// 1. Clear caches
echo "1. Clearing caches...\n";
try {
    Artisan::call('view:clear');
    echo "   - View cache cleared.\n";
    Artisan::call('cache:clear');
    echo "   - Application cache cleared.\n";
    Artisan::call('config:clear');
    echo "   - Config cache cleared.\n";
} catch (\Throwable $e) {
    echo "   - Cache clear note: " . $e->getMessage() . "\n";
}

// 2. Backfill amounts in database
echo "\n2. Updating missing amounts in tbl_registration...\n";
try {
    Artisan::call('registrations:update-amounts', ['--all' => true]);
    echo Artisan::output();
} catch (\Throwable $e) {
    echo "   - Note: " . $e->getMessage() . "\n";
}

echo "\n========================================\n";
echo "Patch applied successfully!\n";
echo "Please delete apply_fix.php from your server for security.\n";
echo "========================================\n";