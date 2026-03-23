
<?php
header('Content-Type: application/json');
error_reporting(0);

$url = trim($_POST['url'] ?? '');

if (empty($url)) {
    die(json_encode(['success' => false, 'message' => 'Please enter a URL']));
}

$platform = detectPlatform($url);

switch($platform) {
    case 'tiktok':
        echo json_encode(extractTikTok($url));
        break;
    case 'instagram':
        echo json_encode(extractInstagram($url));
        break;
    case 'facebook':
        echo json_encode(extractFacebook($url));
        break;
    case 'twitter':
        echo json_encode(extractTwitter($url));
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Platform not supported']);
}

function detectPlatform($url) {
    if (strpos($url, 'tiktok') !== false) return 'tiktok';
    if (strpos($url, 'instagram') !== false) return 'instagram';
    if (strpos($url, 'facebook') !== false || strpos($url, 'fb.watch') !== false) return 'facebook';
    if (strpos($url, 'twitter') !== false || strpos($url, 'x.com') !== false) return 'twitter';
    return false;
}

// TIKTOK - Direct extraction from page HTML
function extractTikTok($url) {
    // Follow redirects to get real URL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Accept-Encoding: gzip, deflate, br',
            'Cache-Control: no-cache',
        ],
        CURLOPT_ENCODING => '',
        CURLOPT_TIMEOUT => 30
    ]);
    
    $html = curl_exec($ch);
    curl_close($ch);
    
    if (!$html) {
        return ['success' => false, 'message' => 'Could not fetch TikTok page'];
    }
    
    // Method 1: Extract from JSON-LD
    if (preg_match('/<script id="__UNIVERSAL_DATA_FOR_REHYDRATION__" type="application\/json">(.*?)<\/script>/s', $html, $match)) {
        $data = json_decode($match[1], true);
        
        if (isset($data['__DEFAULT_SCOPE__']['webapp.video-detail']['itemInfo']['itemStruct'])) {
            $video = $data['__DEFAULT_SCOPE__']['webapp.video-detail']['itemInfo']['itemStruct'];
            
            // Get video without watermark
            $videoUrl = $video['video']['downloadAddr'] ?? 
                       $video['video']['playAddr'] ?? 
                       null;
            
            if ($videoUrl) {
                return [
                    'success' => true,
                    'download_url' => $videoUrl,
                    'title' => $video['desc'] ?? 'TikTok Video',
                    'thumbnail' => $video['video']['cover'] ?? '',
                    'author' => $video['author']['nickname'] ?? ''
                ];
            }
        }
    }
    
    // Method 2: Extract from NEXT_DATA
    if (preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $match)) {
        $data = json_decode($match[1], true);
        
        if (isset($data['props']['pageProps']['itemInfo']['itemStruct'])) {
            $video = $data['props']['pageProps']['itemInfo']['itemStruct'];
            
            $videoUrl = $video['video']['downloadAddr'] ?? 
                       $video['video']['playAddr'] ?? 
                       null;
            
            if ($videoUrl) {
                return [
                    'success' => true,
                    'download_url' => $videoUrl,
                    'title' => $video['desc'] ?? 'TikTok Video'
                ];
            }
        }
    }
    
    // Method 3: Extract video ID and construct download URL
    if (preg_match('/video\/(\d+)/', $url, $matches)) {
        $videoId = $matches[1];
        
        // Use TikTok CDN direct link
        $apiUrl = "https://api16-normal-c-useast1a.tiktokv.com/aweme/v1/feed/?aweme_id={$videoId}";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'TikTok 26.2.0 rv:262018 (iPhone; iOS 14.4.2; en_US) Cronet',
            CURLOPT_TIMEOUT => 20
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            $data = json_decode($response, true);
            
            if (isset($data['aweme_list'][0]['video']['play_addr']['url_list'][0])) {
                return [
                    'success' => true,
                    'download_url' => $data['aweme_list'][0]['video']['play_addr']['url_list'][0],
                    'title' => 'TikTok Video'
                ];
            }
        }
    }
    
    return ['success' => false, 'message' => 'Could not extract TikTok video. Try another link.'];
}

// INSTAGRAM - Direct extraction
function extractInstagram($url) {
    // Get page content
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Cache-Control: no-cache',
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    
    $html = curl_exec($ch);
    curl_close($ch);
    
    if (!$html) {
        return ['success' => false, 'message' => 'Could not fetch Instagram page'];
    }
    
    // Extract video URL from page data
    if (preg_match('/"video_url":"(https:\\\\\/\\\\\/[^"]+)"/', $html, $match)) {
        $videoUrl = json_decode('"' . $match[1] . '"');
        
        return [
            'success' => true,
            'download_url' => $videoUrl,
            'title' => 'Instagram Video'
        ];
    }
    
    // Alternative: Extract from GraphQL data
    if (preg_match('/window\._sharedData = ({.*?});<\/script>/', $html, $match)) {
        $data = json_decode($match[1], true);
        
        if (isset($data['entry_data']['PostPage'][0]['graphql']['shortcode_media']['video_url'])) {
            return [
                'success' => true,
                'download_url' => $data['entry_data']['PostPage'][0]['graphql']['shortcode_media']['video_url'],
                'title' => 'Instagram Video'
            ];
        }
    }
    
    return ['success' => false, 'message' => 'Could not extract Instagram video. Make sure profile is public.'];
}

// FACEBOOK - Direct extraction
function extractFacebook($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    
    $html = curl_exec($ch);
    curl_close($ch);
    
    if (!$html) {
        return ['success' => false, 'message' => 'Could not fetch Facebook page'];
    }
    
    // Extract HD video URL
    if (preg_match('/"hd_src":"(https:\\\\\/\\\\\/[^"]+)"/', $html, $match)) {
        $videoUrl = json_decode('"' . $match[1] . '"');
        
        return [
            'success' => true,
            'download_url' => $videoUrl,
            'title' => 'Facebook Video (HD)'
        ];
    }
    
    // Extract SD video URL
    if (preg_match('/"sd_src":"(https:\\\\\/\\\\\/[^"]+)"/', $html, $match)) {
        $videoUrl = json_decode('"' . $match[1] . '"');
        
        return [
            'success' => true,
            'download_url' => $videoUrl,
            'title' => 'Facebook Video (SD)'
        ];
    }
    
    return ['success' => false, 'message' => 'Could not extract Facebook video.'];
}

// TWITTER/X - Direct extraction
function extractTwitter($url) {
    // Twitter requires different approach - use their API endpoint
    preg_match('/status\/(\d+)/', $url, $matches);
    
    if (!isset($matches[1])) {
        return ['success' => false, 'message' => 'Invalid Twitter URL'];
    }
    
    $tweetId = $matches[1];
    
    // Use Twitter syndication API (no auth needed)
    $apiUrl = "https://cdn.syndication.twimg.com/tweet-result?id={$tweetId}&lang=en";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_TIMEOUT => 20
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        
        if (isset($data['video']['variants'])) {
            $videos = $data['video']['variants'];
            
            // Get highest quality MP4
            $mp4Videos = array_filter($videos, function($v) {
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
    
    return ['success' => false, 'message' => 'Could not extract Twitter video.'];
}
?>
