<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SandsLab Key Server - Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script>
        // Immediately apply saved theme to prevent flash
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark-theme');
            } else {
                document.documentElement.classList.remove('dark-theme');
            }
        })();
    </script>
    <style>
        :root {
            /* Light Theme (Default) */
            --primary: #1C8DCD;
            --primary-glow: rgba(28, 141, 205, 0.15);
            --accent: #F68D20;
            --bg-color: #F3F4F6;
            --bg-gradient: radial-gradient(circle at 50% 50%, #E2E8F0 0%, #F3F4F6 100%);
            --card-bg: rgba(255, 255, 255, 0.85);
            --border-color: rgba(0, 0, 0, 0.08);
            --text-light: #1F2937;
            --text-muted: #6B7280;
            --input-bg: rgba(255, 255, 255, 0.9);
            --input-text: #1F2937;
            --input-border: rgba(0, 0, 0, 0.1);
            --shadow: 0 20px 50px rgba(0, 0, 0, 0.08), 0 0 30px var(--primary-glow);
            --logo-gradient: linear-gradient(135deg, #1F2937 0%, var(--primary) 100%);
            --footer-color: rgba(0, 0, 0, 0.4);
            --shadow-btn: rgba(28, 141, 205, 0.2);
        }

        :root.dark-theme {
            /* Dark Theme */
            --primary-glow: rgba(28, 141, 205, 0.4);
            --bg-color: #0A0E17;
            --bg-gradient: radial-gradient(circle at 50% 50%, #152033 0%, #0A0E17 100%);
            --card-bg: rgba(17, 24, 39, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-light: #F3F4F6;
            --text-muted: #9CA3AF;
            --input-bg: rgba(0, 0, 0, 0.3);
            --input-text: #FFF;
            --input-border: rgba(255, 255, 255, 0.1);
            --shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 40px var(--primary-glow);
            --logo-gradient: linear-gradient(135deg, #FFF 0%, var(--primary) 100%);
            --footer-color: rgba(255, 255, 255, 0.2);
            --shadow-btn: rgba(28, 141, 205, 0.4);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-gradient);
            color: var(--text-light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* Glass Card Container */
        .login-container {
            width: 100%;
            max-width: 440px;
            padding: 40px;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: var(--shadow);
            animation: fadeIn 0.8s ease-out;
            transition: all 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-text {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
            background: var(--logo-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 300;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 10px;
            color: var(--input-text);
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 10px var(--primary-glow);
            background: var(--input-bg);
        }

        .error-box {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            padding: 12px;
            border-radius: 10px;
            color: #F87171;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary) 0%, #106294 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px var(--shadow-btn);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(28, 141, 205, 0.5);
            background: linear-gradient(135deg, #259fe0 0%, var(--primary) 100%);
        }

        .footer-note {
            text-align: center;
            font-size: 12px;
            color: var(--footer-color);
            margin-top: 30px;
        }

        /* Floating Theme Toggle Style */
        .theme-toggle-floating {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            cursor: pointer;
            color: var(--text-light);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            z-index: 100;
        }

        .theme-toggle-floating:hover {
            transform: scale(1.08);
            box-shadow: 0 0 15px var(--primary-glow);
        }

        /* SVG Display logic */
        .sun-icon, .moon-icon {
            width: 20px;
            height: 20px;
            display: none;
        }
        :root.dark-theme .sun-icon { display: block !important; }
        :root:not(.dark-theme) .moon-icon { display: block !important; }
    </style>
</head>
<body>

    <!-- Floating Theme Toggle Button -->
    <button class="theme-toggle-floating" aria-label="Toggle theme" onclick="toggleTheme()">
        <!-- Sun Icon (shown in dark theme to switch to light) -->
        <svg class="sun-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="5"></circle>
            <line x1="12" y1="1" x2="12" y2="3"></line>
            <line x1="12" y1="21" x2="12" y2="23"></line>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
            <line x1="1" y1="12" x2="3" y2="12"></line>
            <line x1="21" y1="12" x2="23" y2="12"></line>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
        </svg>
        <!-- Moon Icon (shown in light theme to switch to dark) -->
        <svg class="moon-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
    </button>

    <div class="login-container">
        <div class="header">
            <div class="logo-text">SandsLab Key Server</div>
            <div class="subtitle">Enter credentials to manage licensing keys</div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-box">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= dirname($_SERVER['SCRIPT_NAME']) === '/' ? '/login' : dirname($_SERVER['SCRIPT_NAME']) . '/login' ?>" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="e.g. admin" required autocomplete="off">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit">Sign In</button>
        </form>

        <div class="footer-note">
            &copy; 2026 SandsLab. All Rights Reserved.
        </div>
    </div>

    <script>
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark-theme');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }
    </script>
</body>
</html>
