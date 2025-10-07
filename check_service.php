<?php

require_once 'vendor/autoload.php';

use App\Services\MediaDisplayService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = new MediaDisplayService();
$info = $service->getSlideshowInfo();

echo "Slideshow info:\n";
echo json_encode($info, JSON_PRETTY_PRINT) . "\n";
