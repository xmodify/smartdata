<?php
$base64 = trim(file_get_contents(__DIR__ . '/public/images/logo_base64.txt'));
$data = base64_decode($base64);
file_put_contents(__DIR__ . '/public/favicon.ico', $data);
file_put_contents(__DIR__ . '/public/favicon.png', $data);
echo "Decoded " . strlen($data) . " bytes to favicon.ico and favicon.png successfully!\n";
