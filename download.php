
<?php
error_reporting(0);

// Handle direct download proxy
if (isset($_GET['file'])) {
    $videoUrl = base64_decode($_GET['file']);
    
    // Add TikTok-specific headers to bypass restrictions
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $videoUrl,
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_HTTPHEADER => [
            'Referer: https://www.tiktok.com/',
            'Range: bytes=0-',
        ],
        CURLOPT_HEADER => false,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_WRITEFUNCTION => function($ch, $data) {
            echo $data;
            return strlen($data);
        }
    ]);
    
    header('Content-Type: video/mp4');
    header('Content-Disposition: attachment; filename="video_' . time() . '.mp4"');
    header('Cache-Control: no-cache');
    
    curl_exec($ch);
    curl_close($ch);
    exit;
}

header('Content-Type: application/json');

$url = trim($_POST['url'] ?? '');

if (empty($url)) {
    die(json_encode(['success' => false, 'message' => 'Enter a video URL']));
}

$platform = detectPlatform($url);

if (!$platform) {
    die(json_encode(['success' => false, 'message' => 'Supported: TikTok, Instagram, Facebook, Twitter, YouTube']));
}

switch($platform) {
    case 'tiktok':
        $result = downloadTikTokWorking($url);
        break;
    case 'instagram':
        $result = downloadInstagram($url);
        break;
    case 'facebook':
        $result = downloadFacebook($url);
        break;
    case 'twitter':
        $result = downloadTwitter($url);
        break;
    case 'youtube':
        $result = downloadYouTube($url);
        break;
    default:
        $result = ['success' => false, 'message' => 'Platform not supported'];
}

if ($result['success'] && isset($result['download_url'])) {
    $result['proxy_url'] = 'download.php?file=' . base64_encode($result['download_url']);
}

echo json_encode($result);

function detectPlatform($url) {
    $url = strtolower($url);
    if (strpos($url, 'tiktok') !== false || strpos($url, 'vt.tiktok') !== false) return 'tiktok';
    if (strpos($url, 'instagram') !== false) return 'instagram';
    if (strpos($url, 'facebook') !== false || strpos($url, 'fb.watch') !== false) return 'facebook';
    if (strpos($url, 'twitter') !== false || strpos($url, 'x.com') !== false) return 'twitter';
    if (strpos($url, 'youtube') !== false || strpos($url, 'youtu.be') !== false) return 'youtube';
    return false;
}

// TIKTOK - Use external API that works
function downloadTikTokWorking($url) {
    // Method 1: Use Musicaldown API (Free, No Key, Works!)
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://musicaldown.com/download',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'url' => $url,
            'submit' => ''
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Origin: https://musicaldown.com',
            'Referer: https://musicaldown.com/'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $html = curl_exec($ch);
    curl_close($ch);
    
    if ($html) {
        // Extract download link
        if (preg_match('/<a[^>]+href="([^"]+)"[^>]*>Download Video<\/a>/i', $html, $match)) {
            return [
                'success' => true,
                'download_url' => $match[1],
                'title' => 'TikTok Video (No Watermark)'
            ];
        }
    }
    
    // Method 2: SnapTik (Backup)
    return downloadTikTokSnapTik($url);
}

function downloadTikTokSnapTik($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://snaptik.app/abc2.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => 'url=' . urlencode($url) . '&lang=en&token=',
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Origin: https://snaptik.app',
            'Referer: https://snaptik.app/en'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        // Look for HD download link
        if (preg_match('/href="(https:\/\/[^"]+\.tikcdn\.io[^"]+)"/i', $response, $match)) {
            return [
                'success' => true,
                'download_url' => $match[1],
                'title' => 'TikTok Video'
            ];
        }
    }
    
    // Method 3: SSSTik
    return downloadTikTokSSSTik($url);
}

function downloadTikTokSSSTik($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://ssstik.io/abc?url=dl',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'id' => $url,
            'locale' => 'en',
            'tt' => 'a0RjZ1Fp'
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Origin: https://ssstik.io',
            'Referer: https://ssstik.io/en',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'HX-Request: true',
            'HX-Target: target',
            'HX-Current-URL: https://ssstik.io/en'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $html = curl_exec($ch);
    curl_close($ch);
    
    if ($html) {
        // Look for download without watermark
        if (preg_match('/<a[^>]+href="([^"]+)"[^>]*>\s*Without Watermark/i', $html, $match)) {
            return [
                'success' => true,
                'download_url' => $match[1],
                'title' => 'TikTok Video (No Watermark)'
            ];
        }
        
        // Alternative pattern
        if (preg_match('/href="([^"]+)"[^>]*download[^>]*>/', $html, $match)) {
            return [
                'success' => true,
                'download_url' => $match[1],
                'title' => 'TikTok Video'
            ];
        }
    }
    
    return ['success' => false, 'message' => 'TikTok download failed. Please try another video.'];
}

function downloadInstagram($url) {
    // Use SaveFrom API
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://v3.savefrom.net/ajax.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => 'url=' . urlencode($url),
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
        
        if (isset($data[0]['url'][0]['url'])) {
            return [
                'success' => true,
                'download_url' => $data[0]['url'][0]['url'],
                'title' => 'Instagram Video'
            ];
        }
    }
    
    return ['success' => false, 'message' => 'Instagram download failed. Make sure profile is public.'];
}

function downloadFacebook($url) {
    // Use GetFVid API
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://getfvid.com/downloader',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => 'url=' . urlencode($url),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $html = curl_exec($ch);
    curl_close($ch);
    
    if ($html) {
        // Look for HD download link
        if (preg_match('/<a[^>]+href="([^"]+)"[^>]*>Download in HD<\/a>/i', $html, $match)) {
            return [
                'success' => true,
                'download_url' => $match[1],
                'title' => 'Facebook Video (HD)'
            ];
        }
        
        // SD quality
        if (preg_match('/<a[^>]+href="([^"]+)"[^>]*>Download in SD<\/a>/i', $html, $match)) {
            return [
                'success' => true,
                'download_url' => $match[1],
                'title' => 'Facebook Video'
            ];
        }
    }
    
    return ['success' => false, 'message' => 'Facebook download failed.'];
}

function downloadTwitter($url) {
    // Extract tweet ID
    if (!preg_match('/status\/(\d+)/', $url, $matches)) {
        return ['success' => false, 'message' => 'Invalid Twitter URL'];
    }
    
    $tweetId = $matches[1];
    
    // Use Twitter syndication API
    $apiUrl = "https://cdn.syndication.twimg.com/tweet-result?id={$tweetId}&lang=en";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 20
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        
        if (isset($data['video']['variants'])) {
            $mp4Videos = array_filter($data['video']['variants'], function($v) {
                return isset($v['type']) && $v['type'] === 'video/mp4';
            });
            
            if (!empty($mp4Videos)) {
                usort($mp4Videos, function($a, $b) {
                    return ($b['bitrate'] ?? 0) - ($a['bitrate'] ?? 0);
                });
                
                return [
                    'success' => true,
                    'download_url' => $mp4Videos[0]['url'],
                    'title' => 'Twitter Video'
                ];
            }
        }
    }
    
    return ['success' => false, 'message' => 'Twitter download failed.'];
}

function downloadYouTube($url) {
    // Extract video ID
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $match)) {
        $videoId = $match[1];
        
        return [
            'success' => true,
            'download_url' => "https://www.y2mate.com/youtube/{$videoId}",
            'title' => 'YouTube Video',
            'external' => true
        ];
    }
    
    return ['success' => false, 'message' => 'Invalid YouTube URL'];
}
?>
