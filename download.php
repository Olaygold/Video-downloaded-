
<?php
header('Content-Type: application/json');

$url = trim($_POST['url'] ?? '');

if (empty($url)) {
    die(json_encode(['success' => false, 'message' => 'Enter a video URL']));
}

// Use Cobalt API - supports ALL platforms, NO watermark
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.cobalt.tools/api/json',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'url' => $url,
        'vCodec' => 'h264',
        'vQuality' => '720',
        'aFormat' => 'mp3',
        'isAudioOnly' => false,
        'isNoTTWatermark' => true
    ]),
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Content-Type: application/json'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    die(json_encode(['success' => false, 'message' => 'Connection error: ' . $error]));
}

$data = json_decode($response, true);

if (isset($data['url'])) {
    echo json_encode([
        'success' => true,
        'download_url' => $data['url'],
        'title' => $data['filename'] ?? 'Video'
    ]);
} elseif (isset($data['picker'])) {
    // Multiple videos (carousel)
    echo json_encode([
        'success' => true,
        'download_url' => $data['picker'][0]['url'],
        'title' => 'Video'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $data['text'] ?? 'Download failed. Try another link.'
    ]);
}
?>
