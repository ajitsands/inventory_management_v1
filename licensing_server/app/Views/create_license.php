<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SandsLab Key Server - Issue License</title>
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
            --input-bg: rgba(255, 255, 255, 0.9);
            --input-text: #1F2937;
            --shadow: 0 20px 50px rgba(0, 0, 0, 0.08), 0 0 30px var(--primary-glow);
            --logo-gradient: linear-gradient(135deg, #1F2937 0%, var(--primary) 100%);
            
            --btn-cancel-bg: rgba(0, 0, 0, 0.03);
            --btn-cancel-border: rgba(0, 0, 0, 0.08);
            --btn-cancel-text: #6B7280;
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
            --input-bg: rgba(0, 0, 0, 0.3);
            --input-text: #FFF;
            --shadow: 0 20px 50px rgba(0, 0, 0, 0.4), 0 0 30px var(--primary-glow);
            --logo-gradient: linear-gradient(135deg, #FFF 0%, var(--primary) 100%);
            
            --btn-cancel-bg: rgba(255, 255, 255, 0.05);
            --btn-cancel-border: rgba(255, 255, 255, 0.08);
            --btn-cancel-text: #9CA3AF;
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
            max-width: 600px;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .header {
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: 700;
            background: var(--logo-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 14px;
            color: var(--text-muted);
        }

        /* Theme toggle inside card header */
        .theme-toggle-card {
            background: var(--btn-cancel-bg);
            border: 1px solid var(--btn-cancel-border);
            padding: 8px;
            border-radius: 8px;
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

        .theme-toggle-card:hover {
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

        .error-box {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            padding: 14px;
            border-radius: 10px;
            color: #F87171;
            font-size: 14px;
            margin-bottom: 24px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .full-width {
            grid-column: span 2;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        input, select {
            width: 100%;
            padding: 12px 16px;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--input-text);
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 8px var(--primary-glow);
            background: var(--input-bg);
        }

        .hint {
            display: block;
            margin-top: 6px;
            font-size: 11px;
            color: var(--text-muted);
        }

        .buttons-row {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            border-top: 1px solid var(--border-color);
            padding-top: 24px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-cancel {
            background: var(--btn-cancel-bg);
            border: 1px solid var(--btn-cancel-border);
            color: var(--btn-cancel-text);
        }

        .btn-cancel:hover {
            background: rgba(128, 128, 128, 0.15);
            color: var(--text-light);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, #106294 100%);
            border: none;
            color: white;
            box-shadow: 0 4px 15px var(--primary-glow);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(28, 141, 205, 0.5);
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
            <div>
                <h1 class="title">Issue License Key</h1>
                <p class="subtitle">Enter customer details. A 2048-bit RSA keypair will be created automatically.</p>
            </div>
            
            <!-- Theme Toggle Button -->
            <button class="theme-toggle-card" type="button" aria-label="Toggle theme" onclick="toggleTheme()">
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
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-box">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= dirname($_SERVER['SCRIPT_NAME']) === '/' ? '/licenses/create' : dirname($_SERVER['SCRIPT_NAME']) . '/licenses/create' ?>" method="POST">
            <div class="form-grid">
                
                <div class="form-group full-width">
                    <label for="customer_name">Customer / Client Name</label>
                    <input type="text" id="customer_name" name="customer_name" placeholder="e.g. Apex Outlets Ltd" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="application_name">Application Name</label>
                    <input type="text" id="application_name" name="application_name" value="Inventory Management System" required>
                </div>

                <div class="form-group">
                    <label for="status">Initial Status</label>
                    <select id="status" name="status">
                        <option value="active" selected>Active / Enabled</option>
                        <option value="suspended">Suspended / Blocked</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="domain_name">Registered Domain / Hostname</label>
                    <input type="text" id="domain_name" name="domain_name" placeholder="e.g. inventory.apex.com" required autocomplete="off">
                    <span class="hint">The application domain name that requests will come from.</span>
                </div>

                <div class="form-group">
                    <label for="ip_address">Server IP Address (Optional)</label>
                    <input type="text" id="ip_address" name="ip_address" placeholder="e.g. 192.168.1.100" autocomplete="off">
                    <span class="hint">Restrict queries to a specific server IP address.</span>
                </div>

                <div class="form-group full-width">
                    <label for="expiry_date">Expiration Date (Optional)</label>
                    <input type="date" id="expiry_date" name="expiry_date">
                    <span class="hint">Leave blank for **lifetime** access (no expiration).</span>
                </div>

            </div>

            <div class="buttons-row">
                <a href="<?= dirname($_SERVER['SCRIPT_NAME']) === '/' ? '/' : dirname($_SERVER['SCRIPT_NAME']) . '/' ?>" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn btn-submit">Generate Keys & Save</button>
            </div>
        </form>
    </div>

    <script>
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark-theme');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }
    </script>
</body>
</html>
