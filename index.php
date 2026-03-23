<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Downloader - No Watermark</title>
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
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }

        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        input[type="text"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border 0.3s;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .result {
            margin-top: 30px;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 10px;
            display: none;
        }

        .result.show {
            display: block;
        }

        .download-link {
            display: inline-block;
            padding: 12px 30px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 10px;
            transition: background 0.3s;
        }

        .download-link:hover {
            background: #45a049;
        }

        .error {
            color: #f44336;
            padding: 15px;
            background: #ffebee;
            border-radius: 8px;
            margin-top: 15px;
            display: none;
        }

        .error.show {
            display: block;
        }

        .supported {
            margin-top: 30px;
            text-align: center;
        }

        .supported h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .platforms {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .platform {
            padding: 8px 15px;
            background: #e8eaf6;
            border-radius: 20px;
            font-size: 12px;
            color: #5c6bc0;
        }

        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🎥 Video Downloader</h1>
        <p class="subtitle">Download videos without watermark from any social media</p>

        <form id="downloadForm">
            <div class="input-group">
                <input 
                    type="text" 
                    id="videoUrl" 
                    placeholder="Paste video URL here (TikTok, Instagram, Facebook, etc.)"
                    required
                >
            </div>
            <button type="submit" class="btn" id="submitBtn">Download Video</button>
        </form>

        <div class="loader" id="loader"></div>
        <div class="error" id="error"></div>
        <div class="result" id="result"></div>

        <div class="supported">
            <h3>Supported Platforms:</h3>
            <div class="platforms">
                <span class="platform">TikTok</span>
                <span class="platform">Instagram</span>
                <span class="platform">Facebook</span>
                <span class="platform">Twitter</span>
                <span class="platform">YouTube</span>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('downloadForm');
        const loader = document.getElementById('loader');
        const error = document.getElementById('error');
        const result = document.getElementById('result');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const url = document.getElementById('videoUrl').value.trim();
            
            // Reset states
            loader.classList.add('show');
            error.classList.remove('show');
            result.classList.remove('show');
            submitBtn.disabled = true;

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

                if (data.success) {
                    result.innerHTML = `
                        <h3>✅ Video Ready!</h3>
                        <p><strong>Title:</strong> ${data.title || 'Video'}</p>
                        <a href="${data.download_url}" class="download-link" download>📥 Download Video</a>
                    `;
                    result.classList.add('show');
                } else {
                    error.textContent = '❌ ' + data.message;
                    error.classList.add('show');
                }
            } catch (err) {
                loader.classList.remove('show');
                submitBtn.disabled = false;
                error.textContent = '❌ An error occurred. Please try again.';
                error.classList.add('show');
            }
        });
    </script>
</body>
</html>
