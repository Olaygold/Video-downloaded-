
<?php
/**
 * 2026 High-Performance Video Downloader Backend
 * Matches perfectly with your provided frontend.
 */

error_reporting(0); // Hide errors for clean JSON output

// --- 1. DIRECT DOWNLOAD PROXY ---
// This bypasses "Access-Control-Allow-Origin" blocks from TikTok/Instagram
if (isset($_GET['file'])) {
    $fileUrl = base64_decode($_GET['file']);
    
    if (!filter_var($fileUrl, FILTER_VALIDATE_URL)) {
        header("HTTP/1.1 400 Bad Request");
        exit("Invalid URL");
    }

    $fileName = "video_" . time() . ".mp4";
    
    header('Content-Type: video/mp4');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    $ch = curl_init($fileUrl);
    curl_setopt_array($ch, [
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => false, // Stream directly to output
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_BUFFERSIZE => 1024 * 8,
    ]);
    
    curl_exec($ch);
    curl_close($ch);
    exit;
}

// --- 2. API ENDPOINT ---
header('Content-Type: application/json');

// Get URL from the POST request sent by your frontend
$targetUrl = trim($_POST['url'] ?? '');

if (empty($targetUrl)) {
    echo json_encode(['success' => false, 'message' => 'URL is missing. Please paste a link.']);
    exit;
}

$downloader = new SocialMediaExtractor();
$response = $downloader->extract($targetUrl);

// If successful, wrap the raw video link in our proxy
if ($response['success'] && isset($response['download_url'])) {
    $response['proxy_url'] = 'download.php?file=' . base64_encode($response['download_url']);
}

echo json_encode($response);

// --- 3. EXTRACTION LOGIC ---
class SocialMediaExtractor {
    
    public function extract($url) {
        $url = strtolower($url);
        
        if (strpos($url, 'tiktok.com') !== false) {
            return $this->getTikTok($url);
        } elseif (strpos($url, 'instagram.com') !== false) {
            return $this->getInstagram($url);
        } elseif (strpos($url, 'twitter.com') !== false || strpos($url, 'x.com') !== false) {
            return $this->getTwitter($url);
        } elseif (strpos($url, 'facebook.com') !== false || strpos($url, 'fb.watch') !== false) {
            return $this->getFacebook($url);
        }
        
        return ['success' => false, 'message' => 'Unsupported platform or invalid link format.'];
    }

    private function getTikTok($url) {
        // TikWM is currently the most stable API for No-Watermark TikToks
        $api = "https://www.tikwm.com/api/?url=" . urlencode($url);
        $data = json_decode($this->fetch($api), true);
        
        if (isset($data['data']['play'])) {
            return [
                'success' => true,
                'title' => $data['data']['title'] ?? 'TikTok Video',
                'download_url' => "https://www.tikwm.com" . $data['data']['play']
            ];
        }
        return ['success' => false, 'message' => 'TikTok video could not be retrieved.'];
    }

    private function getTwitter($url) {
        // Using vxtwitter API for high-speed metadata extraction
        $cleanUrl = str_replace(['x.com', 'twitter.com'], 'vxtwitter.com', $url);
        $data = json_decode($this->fetch($cleanUrl), true);
        
        if (isset($data['media_urls'][0])) {
            return [
                'success' => true,
                'title' => substr($data['text'], 0, 50) . '...',
                'download_url' => $data['media_urls'][0]
            ];
        }
        return ['success' => false, 'message' => 'Twitter/X video not found.'];
    }

    private function getInstagram($url) {
        // IG logic usually requires a logged-in cookie to bypass their "rubbish" login walls
        // This is a fallback attempt:
        $api = "https://ddinstagram.com/videos/" . urlencode($url); 
        // Note: In production, consider using a paid API like RapidAPI for IG.
        return ['success' => false, 'message' => 'Instagram requires session cookies for server-side scraping.'];
    }

    private function getFacebook($url) {
        $html = $this->fetch($url);
        // Look for the HD/SD source in the page source
        if (preg_match('/hd_src:"([^"]+)"/', $html, $matches)) {
            return ['success' => true, 'title' => 'Facebook HD Video', 'download_url' => stripslashes($matches[1])];
        }
        return ['success' => false, 'message' => 'Facebook video is private or unreachable.'];
    }

    private function fetch($url) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_TIMEOUT => 10
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}
