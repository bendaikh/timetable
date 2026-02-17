<?php

$headers = get_headers('http://localhost:8000/storage/media/1759070649_asdfasd.jpeg');
echo "HTTP Status: " . $headers[0] . PHP_EOL;

foreach ($headers as $header) {
    if (strpos($header, 'Content-Type') !== false) {
        echo "Content-Type: " . $header . PHP_EOL;
        break;
    }
}

echo "File exists locally: " . (file_exists('C:/Users/Espacegamers/Documents/timetable/public/storage/media/1759070649_asdfasd.jpeg') ? 'Yes' : 'No') . PHP_EOL;
