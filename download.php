<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$url = $_POST['url'] ?? '';

if (empty($url)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a URL']);
    exit;
}

// Detect platform
$platform = detectPlatform($url);

if (!$platform) {
    echo json_encode(['success' => false, 'message' => 'Unsupported platform']);
    exit;
}

// Use free API services to download
$result = downloadVideo($url, $platform);

echo json_encode($result);

function detectPlatform($url) {
    if (strpos($url, 'tiktok.com') !== false) return 'tiktok';
    if (strpos($url, 'instagram.com') !== false) return 'instagram';
    if (strpos($url, 'facebook.com') !== false || strpos($url, 'fb.watch') !== false) return 'facebook';
    if (strpos($url, 'twitter.com') !== false || strpos($url, 'x.com') !== false) return 'twitter';
    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) return 'youtube';
    return false;
}

function downloadVideo($url, $platform) {
    // Using free API services
    $apiUrls = [
        'tiktok' => 'https://www.tikwm.com/api/',
        'instagram' => 'https://v3.igdownloader.app/api/ajaxSearch',
        'general' => 'https://ab.cococococ.com/ajax/download.php'
    ];

    try {
        switch ($platform) {
            case 'tiktok':
                return downloadTikTok($url);
            case 'instagram':
                return downloadInstagram($url);
            default:
                return downloadGeneral($url);
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function downloadTikTok($url) {
    $apiUrl = 'https://www.tikwm.com/api/';
    
    $postData = [
        'url' => $url,
        'hd' => 1
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['data']['play'])) {
        return [
            'success' => true,
            'download_url' => $data['data']['play'],
            'title' => $data['data']['title'] ?? 'TikTok Video'
        ];
    }

    return ['success' => false, 'message' => 'Could not fetch TikTok video'];
}

function downloadInstagram($url) {
    $apiUrl = 'https://v3.igdownloader.app/api/ajaxSearch';
    
    $postData = [
        'recaptchaToken' => '',
        'q' => $url,
        't' => 'media',
        'lang' => 'en'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        'Accept: */*'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['data'])) {
        // Parse HTML to get download link
        preg_match('/href="([^"]+)"[^>]*download/', $data['data'], $matches);
        
        if (isset($matches[1])) {
            return [
                'success' => true,
                'download_url' => $matches[1],
                'title' => 'Instagram Video'
            ];
        }
    }

    return ['success' => false, 'message' => 'Could not fetch Instagram video'];
}

function downloadGeneral($url) {
    $apiUrl = 'https://ab.cococococ.com/ajax/download.php';
    
    $postData = [
        'copyright' => 'https://savetik.co/',
        'url' => $url
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['url'])) {
        return [
            'success' => true,
            'download_url' => $data['url'][0]['url'] ?? $data['url'],
            'title' => $data['title'] ?? 'Video'
        ];
    }

    return ['success' => false, 'message' => 'Could not download video'];
}
?>
