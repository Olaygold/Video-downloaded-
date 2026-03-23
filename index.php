
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Downloader - Direct Download</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
            font-size: 32px;
        }

        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 15px;
            line-height: 1.5;
        }

        .badge {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            color: #999;
        }

        input[type="text"] {
            width: 100%;
            padding: 15px 15px 15px 50px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .result {
            margin-top: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-radius: 12px;
            display: none;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }

        .result.show {
            display: block;
        }

        .result h3 {
            color: #2e7d32;
            margin-bottom: 15px;
            font-size: 20px;
        }

        .download-btn {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            margin-top: 15px;
            font-weight: bold;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.6);
        }

        .error {
            color: #d32f2f;
            padding: 20px;
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            border-radius: 12px;
            margin-top: 20px;
            display: none;
            border-left: 4px solid #d32f2f;
        }

        .error.show {
            display: block;
        }

        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
            display: none;
        }

        .loader.show {
            display: block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .platforms {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 25px;
        }

        .platform {
            padding: 10px 18px;
            background: linear-gradient(135deg, #e8eaf6 0%, #c5cae9 100%);
            border-radius: 25px;
            font-size: 13px;
            color: #3f51b5;
            font-weight: 600;
        }

        .instructions {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            padding: 20px;
            border-radius: 12px;
            margin-top: 25px;
            font-size: 14px;
            color: #e65100;
            border-left: 4px solid #ff9800;
        }

        .instructions strong {
            display: block;
            margin-bottom: 12px;
        }

        .instructions ol {
            margin-left: 20px;
            margin-top: 10px;
        }

        .instructions li {
            margin-bottom: 8px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 25px;
            }
            h1 {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎥 Video Downloader</h1>
        <p class="subtitle">
            Download videos directly to your device<br>
            <span class="badge">✓ DIRECT DOWNLOAD</span>
            <span class="badge">✓ NO WATERMARK</span>
        </p>

        <form id="downloadForm">
            <div class="input-group">
                <span class="input-icon">🔗</span>
                <input 
                    type="text" 
                    id="videoUrl" 
                    placeholder="Paste TikTok, Instagram, Facebook, or Twitter link..."
                    required
                >
            </div>
            <button type="submit" class="btn" id="submitBtn">
                📥 Get Download Link
            </button>
        </form>

        <div class="loader" id="loader"></div>
        <div class="error" id="error"></div>
        <div class="result" id="result"></div>

        <div class="instructions">
            <strong>📱 How to Download:</strong>
            <ol>
                <li>Copy video link from TikTok/Instagram/Facebook/Twitter</li>
                <li>Paste it above and click "Get Download Link"</li>
                <li>Click the green "Download Video" button</li>
                <li>Video will download directly to your device!</li>
            </ol>
        </div>

        <div class="platforms">
            <span class="platform">🎵 TikTok</span>
            <span class="platform">📸 Instagram</span>
            <span class="platform">📘 Facebook</span>
            <span class="platform">🐦 Twitter</span>
        </div>
    </div>

    <script>
        const form = document.getElementById('downloadForm');
        const loader = document.getElementById('loader');
        const error = document.getElementById('error');
        const result = document.getElementById('result');
        const submitBtn = document.getElementById('submitBtn');
        const videoUrlInput = document.getElementById('videoUrl');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const url = videoUrlInput.value.trim();
            
            if (!url) {
                showError('Please enter a video URL');
                return;
            }
            
            // Reset states
            loader.classList.add('show');
            error.classList.remove('show');
            result.classList.remove('show');
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Extracting video...';

            try {
                const response = await fetch('download.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'url=' + encodeURIComponent(url)
                });

                const data = await response.json();

                loader.classList.remove('show');
                submitBtn.disabled = false;
                submitBtn.textContent = '📥 Get Download Link';

                if (data.success) {
                    showDownloadButton(data);
                } else {
                    showError(data.message || 'Failed to extract video. Try another link.');
                }
            } catch (err) {
                loader.classList.remove('show');
                submitBtn.disabled = false;
                submitBtn.textContent = '📥 Get Download Link';
                showError('Connection error. Please try again.');
            }
        });

        function showDownloadButton(data) {
            result.innerHTML = `
                <h3>✅ Video Ready!</h3>
                <p style="margin: 15px 0; color: #555;">${escapeHtml(data.title)}</p>
                <a href="${escapeHtml(data.proxy_url)}" class="download-btn" download>
                    💾 DOWNLOAD VIDEO NOW
                </a>
                <p style="margin-top: 20px; font-size: 13px; color: #666;">
                    ⚡ Click button above - video will download immediately!
                </p>
            `;
            result.classList.add('show');
            videoUrlInput.value = '';
        }

        function showError(message) {
            error.innerHTML = '<strong>❌ Error:</strong><br>' + escapeHtml(message);
            error.classList.add('show');
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        videoUrlInput.addEventListener('input', () => {
            error.classList.remove('show');
        });
    </script>
</body>
</html>
