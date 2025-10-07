<?php

require_once 'vendor/autoload.php';

use App\Models\Media;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$media = Media::find(2);
echo "Media title: " . $media->title . PHP_EOL;
echo "Display duration: " . $media->display_duration . " seconds" . PHP_EOL;
echo "File path: " . $media->file_path . PHP_EOL;
echo "File URL: " . $media->file_url . PHP_EOL;
