<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SandsLab Key Server - License Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            --success: #10B981;
            --info-grid-bg: rgba(0, 0, 0, 0.02);
            --shadow: 0 20px 50px rgba(0, 0, 0, 0.08), 0 0 30px var(--primary-glow);
            
            --input-bg: rgba(255, 255, 255, 0.9);
            --textarea-color: #1F2937;
            --key-input-color: #059669;
            --instructions-bg: rgba(246, 141, 32, 0.08);
            --instructions-color: #1F2937;
            
            --btn-back-bg: rgba(0, 0, 0, 0.03);
            --btn-back-border: rgba(0, 0, 0, 0.08);
            --btn-back-text: #6B7280;
        }

        :root.dark-theme {
            /* Dark Theme */
            --primary-glow: rgba(28, 141, 205, 0.3);
            --bg-color: #0A0E17;
            --bg-gradient: radial-gradient(circle at 50% 50%, #152033 0%, #0A0E17 100%);
            --card-bg: rgba(17, 24, 39, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-light: #F3F4F6;
            --text-muted: #9CA3AF;
            --info-grid-bg: rgba(0, 0, 0, 0.2);
            --shadow: 0 20px 50px rgba(0, 0, 0, 0.4), 0 0 30px var(--primary-glow);
            
            --input-bg: rgba(0, 0, 0, 0.3);
            --textarea-color: var(--text-light);
            --key-input-color: #34D399;
            --instructions-bg: rgba(246, 141, 32, 0.08);
            --instructions-color: rgba(255,255,255,0.85);
            
            --btn-back-bg: rgba(255, 255, 255, 0.05);
            --btn-back-border: rgba(255, 255, 255, 0.08);
            --btn-back-text: #9CA3AF;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            background-image: var(--bg-gradient);
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .card {
            width: 100%;
            max-width: 700px;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-light);
        }

        .btn-back {
            padding: 10px 20px;
            background: var(--btn-back-bg);
            border: 1px solid var(--btn-back-border);
            border-radius: 10px;
            color: var(--btn-back-text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: rgba(128, 128, 128, 0.15);
            color: var(--text-light);
        }

        /* Detail Rows */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
            background: var(--info-grid-bg);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 15px;
            font-weight: 500;
        }

        /* Copy Blocks */
        .copy-section {
            margin-bottom: 24px;
        }

        .copy-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .copy-label {
            font-size: 13px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
        }

        .btn-copy {
            padding: 6px 12px;
            background: rgba(28, 141, 205, 0.15);
            border: 1px solid rgba(28, 141, 205, 0.3);
            border-radius: 6px;
            color: var(--primary);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-copy:hover {
            background: var(--primary);
            color: white;
        }

        .key-input {
            width: 100%;
            padding: 14px;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--key-input-color);
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-align: center;
            outline: none;
        }

        .textarea-key {
            width: 100%;
            height: 160px;
            padding: 14px;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--textarea-color);
            font-family: monospace;
            font-size: 11px;
            line-height: 1.5;
            resize: none;
            outline: none;
        }

        .instructions {
            background: var(--instructions-bg);
            border-left: 3px solid var(--accent);
            padding: 15px;
            border-radius: 4px;
            font-size: 13px;
            line-height: 1.6;
            margin-top: 30px;
            color: var(--instructions-color);
        }

        .instructions strong {
            color: var(--accent);
        }

        /* Success message popup */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--success);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Theme Toggle Button inside card header */
        .theme-toggle-nav {
            background: var(--btn-back-bg);
            border: 1px solid var(--btn-back-border);
            padding: 8px;
            border-radius: 10px;
            cursor: pointer;
            color: var(--text-light);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            outline: none;
            width: 38px;
            height: 38px;
        }

        .theme-toggle-nav:hover {
            background: rgba(128, 128, 128, 0.15);
            transform: scale(1.05);
            box-shadow: 0 0 10px var(--primary-glow);
        }

        /* SVG Display logic */
        .sun-icon, .moon-icon {
            width: 18px;
            height: 18px;
            display: none;
        }
        :root.dark-theme .sun-icon { display: block !important; }
        :root:not(.dark-theme) .moon-icon { display: block !important; }
    </style>
</head>
<body>

    <div class="card">
        <div class="header">
            <h1 class="title">License Keys & Integration</h1>
            <div style="display: flex; align-items: center; gap: 15px;">
                <!-- Theme Toggle Button -->
                <button class="theme-toggle-nav" aria-label="Toggle theme" onclick="toggleTheme()">
                    <!-- Sun Icon -->
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
                    <!-- Moon Icon -->
                    <svg class="moon-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
                <a href="<?= dirname($_SERVER['SCRIPT_NAME']) === '/' ? '/' : dirname($_SERVER['SCRIPT_NAME']) . '/' ?>" class="btn-back">Back</a>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Customer</span>
                <span class="info-value"><?= htmlspecialchars($license['customer_name']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Application</span>
                <span class="info-value"><?= htmlspecialchars($license['application_name']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Domain Boundary</span>
                <span class="info-value"><?= htmlspecialchars($license['domain_name']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Status</span>
                <span class="info-value" style="color: <?= $license['status'] === 'active' ? 'var(--success)' : 'var(--accent)' ?>;">
                    <?= strtoupper(htmlspecialchars($license['status'])) ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Expiry Date</span>
                <span class="info-value">
                    <?= empty($license['expiry_date']) ? 'No Expiry (Lifetime)' : date('F d, Y', strtotime($license['expiry_date'])) ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">IP Address Lock</span>
                <span class="info-value"><?= htmlspecialchars($license['ip_address'] ?? 'Disabled (Any IP)') ?></span>
            </div>
        </div>

        <!-- License Key -->
        <div class="copy-section">
            <div class="copy-header">
                <span class="copy-label">License Key</span>
                <button class="btn-copy" onclick="copyText('licenseKeyInput')">Copy Key</button>
            </div>
            <input type="text" id="licenseKeyInput" class="key-input" value="<?= htmlspecialchars($license['license_key']) ?>" readonly>
        </div>

        <!-- Public Key -->
        <div class="copy-section">
            <div class="copy-header">
                <span class="copy-label">RSA 2048-bit Public Key</span>
                <button class="btn-copy" onclick="copyText('publicKeyArea')">Copy Public Key</button>
            </div>
            <textarea id="publicKeyArea" class="textarea-key" readonly><?= htmlspecialchars($license['public_key']) ?></textarea>
        </div>

        <!-- Integration Note -->
        <div class="instructions">
            <strong>Integration Guide:</strong> Provide the <strong>License Key</strong> above to the customer to insert into their local configuration file or settings page. Save the <strong>RSA Public Key</strong> inside their client codebase (e.g. at <code>backend/config/public.key</code>) to verify signed activation tokens offline.
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">Copied to clipboard!</div>

    <script>
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark-theme');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }

        function copyText(elementId) {
            var copyText = document.getElementById(elementId);
            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices
            navigator.clipboard.writeText(copyText.value);

            // Show Toast
            var toast = document.getElementById("toast");
            toast.classList.add("show");
            setTimeout(function() {
                toast.classList.remove("show");
            }, 2000);
        }
    </script>

</body>
</html>
