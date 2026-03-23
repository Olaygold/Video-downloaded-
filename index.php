
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Downloader - All Social Media</title>
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

        input[type="text"]::placeholder {
            color: #bbb;
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

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .result.show {
            display: block;
        }

        .result h3 {
            color: #2e7d32;
            margin-bottom: 15px;
            font-size: 20px;
        }

        .result p {
            color: #555;
            margin: 10px 0;
            font-size: 14px;
        }

        .video-info {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: left;
        }

        .video-info strong {
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        .video-info span {
            color: #666;
            font-size: 14px;
        }

        .download-link {
            display: inline-block;
            padding: 14px 35px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            margin-top: 15px;
            transition: all 0.3s;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
        }

        .download-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.6);
        }

        .download-link:active {
            transform: translateY(0);
        }

        .error {
            color: #d32f2f;
            padding: 20px;
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            border-radius: 12px;
            margin-top: 20px;
            display: none;
            animation: shake 0.5s ease;
            border-left: 4px solid #d32f2f;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .error.show {
            display: block;
        }

        .error strong {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .supported {
            margin-top: 35px;
            text-align: center;
        }

        .supported h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .platforms {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .platform {
            padding: 10px 18px;
            background: linear-gradient(135deg, #e8eaf6 0%, #c5cae9 100%);
            border-radius: 25px;
            font-size: 13px;
            color: #3f51b5;
            font-weight: 600;
            transition: all 0.3s;
            cursor: default;
        }

        .platform:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(63, 81, 181, 0.3);
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
            font-size: 15px;
            color: #e65100;
        }

        .instructions ol {
            margin-left: 20px;
            margin-top: 10px;
        }

        .instructions li {
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #999;
            font-size: 12px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .container {
                padding: 25px;
            }

            h1 {
                font-size: 26px;
            }

            .subtitle {
                font-size: 14px;
            }

            input[type="text"] {
                font-size: 15px;
                padding: 13px 13px 13px 45px;
            }

            .btn {
                font-size: 16px;
                padding: 14px;
            }

            .platforms {
                gap: 8px;
            }

            .platform {
                padding: 8px 14px;
                font-size: 12px;
            }
        }

        /* Loading state */
        .loading-text {
            color: #667eea;
            font-size: 14px;
            margin-top: 10px;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Success icon animation */
        .success-icon {
            font-size: 50px;
            animation: bounce 0.6s ease;
        }

        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎥 Video Downloader</h1>
        <p class="subtitle">Download videos from TikTok, Instagram, Facebook, Twitter<br>without watermark - 100% Free</p>

        <form id="downloadForm">
            <div class="input-group">
                <span class="input-icon">🔗</span>
                <input 
                    type="text" 
                    id="videoUrl" 
                    placeholder="Paste your video URL here..."
                    required
                    autocomplete="off"
                >
            </div>
            <button type="submit" class="btn" id="submitBtn">
                📥 Download Video
            </button>
        </form>

        <div class="loader" id="loader"></div>
        <p class="loading-text" id="loadingText" style="display: none;">Extracting video... Please wait</p>
        
        <div class="error" id="error"></div>
        <div class="result" id="result"></div>

        <div class="instructions">
            <strong>📋 How to use:</strong>
            <ol>
                <li>Open TikTok, Instagram, Facebook, or Twitter app</li>
                <li>Find the video you want to download</li>
                <li>Tap <strong>Share</strong> → <strong>Copy Link</strong></li>
                <li>Paste the link above and click <strong>Download Video</strong></li>
                <li>Click the download button to save the video</li>
            </ol>
        </div>

        <div class="supported">
            <h3>✅ Supported Platforms</h3>
            <div class="platforms">
                <span class="platform">🎵 TikTok</span>
                <span class="platform">📸 Instagram</span>
                <span class="platform">📘 Facebook</span>
                <span class="platform">🐦 Twitter/X</span>
            </div>
        </div>

        <div class="footer">
            Made with ❤️ | <a href="#" onclick="alert('Free video downloader - No watermarks, No limits'); return false;">About</a>
        </div>
    </div>

    <script>
        const form = document.getElementById('downloadForm');
        const loader = document.getElementById('loader');
        const loadingText = document.getElementById('loadingText');
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

            // Validate URL format
            if (!isValidUrl(url)) {
                showError('Please enter a valid URL');
                return;
            }
            
            // Reset states
            loader.classList.add('show');
            loadingText.style.display = 'block';
            error.classList.remove('show');
            result.classList.remove('show');
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Processing...';

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
                loadingText.style.display = 'none';
                submitBtn.disabled = false;
                submitBtn.textContent = '📥 Download Video';

                if (data.success) {
                    showResult(data);
                } else {
                    showError(data.message || 'Failed to download video. Please try again.');
                }
            } catch (err) {
                loader.classList.remove('show');
                loadingText.style.display = 'none';
                submitBtn.disabled = false;
                submitBtn.textContent = '📥 Download Video';
                showError('Connection error. Please check your internet and try again.');
                console.error('Error:', err);
            }
        });

        function showResult(data) {
            let videoInfoHtml = '';
            
            if (data.title) {
                videoInfoHtml += `
                    <div class="video-info">
                        <strong>📝 Title:</strong>
                        <span>${escapeHtml(data.title)}</span>
                    </div>
                `;
            }

            if (data.author) {
                videoInfoHtml += `
                    <div class="video-info">
                        <strong>👤 Author:</strong>
                        <span>${escapeHtml(data.author)}</span>
                    </div>
                `;
            }

            result.innerHTML = `
                <div class="success-icon">✅</div>
                <h3>Video Ready to Download!</h3>
                ${videoInfoHtml}
                <a href="${escapeHtml(data.download_url)}" class="download-link" target="_blank" download>
                    📥 Download Now
                </a>
                <p style="margin-top: 15px; font-size: 12px; color: #999;">
                    💡 Tip: If download doesn't start, right-click the button and select "Save link as"
                </p>
            `;
            result.classList.add('show');

            // Clear input after successful download
            videoUrlInput.value = '';
        }

        function showError(message) {
            error.innerHTML = `
                <strong>❌ Error</strong>
                ${escapeHtml(message)}
            `;
            error.classList.add('show');

            // Auto-hide error after 5 seconds
            setTimeout(() => {
                error.classList.remove('show');
            }, 5000);
        }

        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
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

        // Auto-focus input on page load
        window.addEventListener('load', () => {
            videoUrlInput.focus();
        });

        // Clear error when user starts typing
        videoUrlInput.addEventListener('input', () => {
            error.classList.remove('show');
        });

        // Handle paste event
        videoUrlInput.addEventListener('paste', (e) => {
            setTimeout(() => {
                const pastedText = videoUrlInput.value.trim();
                if (pastedText) {
                    // Auto-submit if valid URL is pasted (optional)
                    // form.dispatchEvent(new Event('submit'));
                }
            }, 100);
        });
    </script>
</body>
</html>
