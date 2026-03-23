
<?php
error_reporting(0);

// Check if this is a download request
if (isset($_GET['file'])) {
    $videoUrl = base64_decode($_GET['file']);
    
    // Stream video directly to user's device
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $videoUrl,
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_HEADER => false,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_WRITEFUNCTION => function($ch, $data) {
            echo $data;
            return strlen($data);
        }
    ]);
    
    // Set download headers
    header('Content-Type: video/mp4');
    header('Content-Disposition: attachment; filename="video_' . time() . '.mp4"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');
    header('Expires: 0');
    
    curl_exec($ch);
    curl_close($ch);
    exit;
}

// API endpoint for getting video info
header('Content-Type: application/json');

$url = trim($_POST['url'] ?? '');

if (empty($url)) {
    die(json_encode(['success' => false, 'message' => 'Please enter a URL']));
}

$platform = detectPlatform($url);

if (!$platform) {
    die(json_encode(['success' => false, 'message' => 'Only TikTok, Instagram, Facebook, Twitter supported']));
}

// Extract video based on platform
switch($platform) {
    case 'tiktok':
        $result = extractTikTok($url);
        break;
    case 'instagram':
        $result = extractInstagram($url);
        break;
    case 'facebook':
        $result = extractFacebook($url);
        break;
    case 'twitter':
        $result = extractTwitter($url);
        break;
    default:
        $result = ['success' => false, 'message' => 'Platform not supported'];
}

// If successful, create proxy download link
if ($result['success']) {
    $result['proxy_url'] = 'download.php?file=' . base64_encode($result['download_url']);
}

echo json_encode($result);

function detectPlatform($url) {
    $url = strtolower($url);
    if (strpos($url, 'tiktok') !== false || strpos($url, 'vt.tiktok') !== false) return 'tiktok';
    if (strpos($url, 'instagram') !== false) return 'instagram';
    if (strpos($url, 'facebook') !== false || strpos($url, 'fb.watch') !== false) return 'facebook';
    if (strpos($url, 'twitter') !== false || strpos($url, 'x.com') !== false) return 'twitter';
    return false;
}

function extractTikTok($url) {
    // Use TikTok's oembed API - works without scraping
    $videoId = '';
    if (preg_match('/video\/(\d+)/', $url, $matches)) {
        $videoId = $matches[1];
    }
    
    // Method 1: Use oembed
    $oembedUrl = 'https://www.tiktok.com/oembed?url=' . urlencode($url);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $oembedUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 20
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['thumbnail_url'])) {
            // Got video info, now extract download URL
            return extractTikTokVideo($url);
        }
    }
    
    return extractTikTokVideo($url);
}

function extractTikTokVideo($url) {
    // Follow redirects to get final URL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_8 like Mac OS X) AppleWebKit/605.1.15',
        CURLOPT_TIMEOUT => 30
    ]);
    
    $html = curl_exec($ch);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    
    if (!$html) {
        return ['success' => false, 'message' => 'Cannot access TikTok. Try another video.'];
    }
    
    // Extract video ID
    preg_match('/video\/(\d+)/', $finalUrl, $matches);
    if (!isset($matches[1])) {
        return ['success' => false, 'message' => 'Invalid TikTok URL'];
    }
    
    $videoId = $matches[1];
    
    // Use TikTok mobile API (no auth needed)
    $apiUrl = "https://api16-normal-c-useast1a.tiktokv.com/aweme/v1/feed/?aweme_id={$videoId}";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'com.ss.android.ugc.trill/494+Mozilla/5.0',
        CURLOPT_TIMEOUT => 20
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        
        if (isset($data['aweme_list'][0]['video']['play_addr']['url_list'][0])) {
            $videoUrl = $data['aweme_list'][0]['video']['play_addr']['url_list'][0];
            
            return [
                'success' => true,
                'download_url' => $videoUrl,
                'title' => $data['aweme_list'][0]['desc'] ?? 'TikTok Video'
            ];
        }
    }
    
    // Fallback: Extract from page HTML
    if (preg_match('/"downloadAddr":"(https:[^"]+)"/', $html, $match)) {
        $videoUrl = json_decode('"' . $match[1] . '"');
        return [
            'success' => true,
            'download_url' => $videoUrl,
            'title' => 'TikTok Video'
        ];
    }
    
    return ['success' => false, 'message' => 'Could not extract TikTok video. Try another link.'];
}

function extractInstagram($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url . '?__a=1&__d=dis',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Instagram 76.0.0.15.395 Android (24/7.0; 640dpi; 1440x2560; samsung; SM-G930F; herolte; samsungexynos8890; en_US; 138226743)',
        CURLOPT_HTTPHEADER => [
            'Accept: */*',
            'Accept-Language: en-US,en;q=0.9',
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        
        // Check for video URL in different possible locations
        if (isset($data['items'][0]['video_versions'][0]['url'])) {
            return [
                'success' => true,
                'download_url' => $data['items'][0]['video_versions'][0]['url'],
                'title' => 'Instagram Video'
            ];
        }
        
        if (isset($data['graphql']['shortcode_media']['video_url'])) {
            return [
                'success' => true,
                'download_url' => $data['graphql']['shortcode_media']['video_url'],
                'title' => 'Instagram Video'
            ];
        }
    }
    
    // Fallback: scrape HTML
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_TIMEOUT => 30
    ]);
    
    $html = curl_exec($ch);
    curl_close($ch);
    
    if ($html && preg_match('/"video_url":"(https:\\\\\/\\\\\/[^"]+)"/', $html, $match)) {
        $videoUrl = json_decode('"' . $match[1] . '"');
        return [
            'success' => true,
            'download_url' => $videoUrl,
            'title' => 'Instagram Video'
        ];
    }
    
    return ['success' => false, 'message' => 'Cannot download Instagram video. Make sure profile is public.'];
}

function extractFacebook($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_TIMEOUT => 30
    ]);
    
    $html = curl_exec($ch);
    curl_close($ch);
    
    if (!$html) {
        return ['success' => false, 'message' => 'Cannot access Facebook video'];
    }
    
    // Try HD quality first
    if (preg_match('/"hd_src":"(https:\\\\\/\\\\\/[^"]+)"/', $html, $match)) {
        $videoUrl = json_decode('"' . $match[1] . '"');
        return [
            'success' => true,
            'download_url' => $videoUrl,
            'title' => 'Facebook Video (HD)'
        ];
    }
    
    // Try SD quality
    if (preg_match('/"sd_src":"(https:\\\\\/\\\\\/[^"]+)"/', $html, $match)) {
        $videoUrl = json_decode('"' . $match[1] . '"');
        return [
            'success' => true,
            'download_url' => $videoUrl,
            'title' => 'Facebook Video'
        ];
    }
    
    return ['success' => false, 'message' => 'Cannot extract Facebook video'];
}

function extractTwitter($url) {
    preg_match('/status\/(\d+)/', $url, $matches);
    
    if (!isset($matches[1])) {
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
            $variants = $data['video']['variants'];
            
            // Filter MP4 videos only
            $mp4Videos = array_filter($variants, function($v) {
                return isset($v['type']) && $v['type'] === 'video/mp4';
            });
            
            if (!empty($mp4Videos)) {
                // Sort by bitrate (highest first)
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
    
    return ['success' => false, 'message' => 'Cannot extract Twitter video'];
}
?>
