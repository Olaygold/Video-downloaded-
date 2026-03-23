
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$url = $_POST['url'] ?? '';

if (empty($url)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a URL']);
    exit;
}

// Validate URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid URL format']);
    exit;
}

// Detect platform
$platform = detectPlatform($url);

if (!$platform) {
    echo json_encode(['success' => false, 'message' => 'Unsupported platform. Supported: TikTok, Instagram, Facebook, Twitter, YouTube']);
    exit;
}

// Download video
try {
    $result = downloadVideo($url, $platform);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function detectPlatform($url) {
    $url = strtolower($url);
    
    if (strpos($url, 'tiktok.com') !== false || strpos($url, 'vt.tiktok.com') !== false) return 'tiktok';
    if (strpos($url, 'instagram.com') !== false || strpos($url, 'instagr.am') !== false) return 'instagram';
    if (strpos($url, 'facebook.com') !== false || strpos($url, 'fb.watch') !== false || strpos($url, 'fb.com') !== false) return 'facebook';
    if (strpos($url, 'twitter.com') !== false || strpos($url, 'x.com') !== false || strpos($url, 't.co') !== false) return 'twitter';
    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) return 'youtube';
    
    return false;
}

function downloadVideo($url, $platform) {
    switch ($platform) {
        case 'tiktok':
            return downloadTikTok($url);
        case 'instagram':
            return downloadInstagram($url);
        case 'facebook':
            return downloadFacebook($url);
        case 'twitter':
            return downloadTwitter($url);
        case 'youtube':
            return downloadYouTube($url);
        default:
            return ['success' => false, 'message' => 'Platform not supported'];
    }
}

function downloadTikTok($url) {
    // Method 1: TikWM API
    $apiUrl = 'https://www.tikwm.com/api/';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'url' => $url,
            'hd' => 1
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 && $response) {
        $data = json_decode($response, true);
        
        if (isset($data['data']['play'])) {
            return [
                'success' => true,
                'download_url' => $data['data']['play'],
                'title' => $data['data']['title'] ?? 'TikTok Video',
                'thumbnail' => $data['data']['cover'] ?? '',
                'author' => $data['data']['author']['nickname'] ?? ''
            ];
        }
    }

    // Method 2: SnapTik API (fallback)
    return downloadWithSnapTik($url);
}

function downloadWithSnapTik($url) {
    $apiUrl = 'https://snaptik.app/abc2.php';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'url' => $url,
            'lang' => 'en'
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        // Parse HTML response
        preg_match('/<a[^>]+href="([^"]+)"[^>]*>Download Server \d+<\/a>/i', $response, $matches);
        
        if (isset($matches[1])) {
            return [
                'success' => true,
                'download_url' => $matches[1],
                'title' => 'TikTok Video'
            ];
        }
    }

    return ['success' => false, 'message' => 'Could not download TikTok video. Please try another link.'];
}

function downloadInstagram($url) {
    $apiUrl = 'https://v3.igdownloader.app/api/ajaxSearch';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'recaptchaToken' => '',
            'q' => $url,
            't' => 'media',
            'lang' => 'en'
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        
        if (isset($data['data'])) {
            preg_match('/href="([^"]+)"[^>]*class="[^"]*download-items__btn[^"]*"/i', $data['data'], $matches);
            
            if (isset($matches[1])) {
                return [
                    'success' => true,
                    'download_url' => $matches[1],
                    'title' => 'Instagram Video'
                ];
            }
        }
    }

    return ['success' => false, 'message' => 'Could not download Instagram video. Make sure the account is public.'];
}

function downloadFacebook($url) {
    $apiUrl = 'https://fbdownloader.app/ajax';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'url' => $url
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        
        if (isset($data['links']['Download High Quality'])) {
            return [
                'success' => true,
                'download_url' => $data['links']['Download High Quality'],
                'title' => 'Facebook Video'
            ];
        }
    }

    return ['success' => false, 'message' => 'Could not download Facebook video.'];
}

function downloadTwitter($url) {
    $apiUrl = 'https://twitsave.com/info';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'url' => $url
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        preg_match('/<a[^>]+href="([^"]+)"[^>]*download>/i', $response, $matches);
        
        if (isset($matches[1])) {
            return [
                'success' => true,
                'download_url' => $matches[1],
                'title' => 'Twitter Video'
            ];
        }
    }

    return ['success' => false, 'message' => 'Could not download Twitter video.'];
}

function downloadYouTube($url) {
    // Extract video ID
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $match);
    
    if (!isset($match[1])) {
        return ['success' => false, 'message' => 'Invalid YouTube URL'];
    }
    
    $videoId = $match[1];
    
    return [
        'success' => true,
        'download_url' => "https://www.y2mate.com/youtube/{$videoId}",
        'title' => 'YouTube Video',
        'message' => 'Click the link to download from Y2Mate'
    ];
}
?>
