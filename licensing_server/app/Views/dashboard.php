<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SandsLab Key Server - Dashboard</title>
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
            --bg-gradient: radial-gradient(circle at 10% 20%, rgba(220, 225, 235, 0.8) 0%, rgba(243, 244, 246, 0.95) 90%);
            --card-bg: rgba(255, 255, 255, 0.85);
            --border-color: rgba(0, 0, 0, 0.08);
            --text-light: #1F2937;
            --text-muted: #6B7280;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --input-bg: rgba(255, 255, 255, 0.9);
            --input-text: #1F2937;
            --shadow: 0 10px 30px rgba(0,0,0,0.05);
            --table-header-bg: rgba(0, 0, 0, 0.03);
            --row-hover-bg: rgba(0, 0, 0, 0.015);
            --key-display-bg: rgba(28, 141, 205, 0.08);
            --logo-gradient: linear-gradient(135deg, #1F2937 0%, var(--primary) 100%);
            --btn-action-bg: rgba(0, 0, 0, 0.03);
            --btn-action-border: rgba(0, 0, 0, 0.08);
            
            --badge-active-bg: rgba(16, 185, 129, 0.1);
            --badge-active-text: #059669;
            --badge-suspended-bg: rgba(245, 158, 11, 0.1);
            --badge-suspended-text: #D97706;
            --badge-revoked-bg: rgba(239, 68, 68, 0.1);
            --badge-revoked-text: #DC2626;

            --logout-bg: rgba(239, 68, 68, 0.1);
            --logout-border: rgba(239, 68, 68, 0.25);
            --logout-color: #DC2626;
        }

        :root.dark-theme {
            /* Dark Theme */
            --primary-glow: rgba(28, 141, 205, 0.2);
            --bg-color: #0A0E17;
            --bg-gradient: radial-gradient(circle at 10% 20%, rgba(21, 32, 51, 0.5) 0%, rgba(10, 14, 23, 0.8) 90%);
            --card-bg: rgba(17, 24, 39, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-light: #F3F4F6;
            --text-muted: #9CA3AF;
            --input-bg: rgba(0, 0, 0, 0.2);
            --input-text: #FFF;
            --shadow: 0 10px 30px rgba(0,0,0,0.3);
            --table-header-bg: rgba(0, 0, 0, 0.3);
            --row-hover-bg: rgba(255, 255, 255, 0.02);
            --key-display-bg: rgba(0, 0, 0, 0.3);
            --logo-gradient: linear-gradient(135deg, #FFF 0%, var(--primary) 100%);
            --btn-action-bg: rgba(255, 255, 255, 0.05);
            --btn-action-border: rgba(255, 255, 255, 0.1);
            
            --badge-active-bg: rgba(16, 185, 129, 0.15);
            --badge-active-text: #34D399;
            --badge-suspended-bg: rgba(245, 158, 11, 0.15);
            --badge-suspended-text: #FBBF24;
            --badge-revoked-bg: rgba(239, 68, 68, 0.15);
            --badge-revoked-text: #F87171;

            --logout-bg: rgba(239, 68, 68, 0.15);
            --logout-border: rgba(239, 68, 68, 0.3);
            --logout-color: #F87171;
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
            padding: 40px 20px;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Navigation Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 1px;
            background: var(--logo-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            font-size: 14px;
            color: var(--text-muted);
        }

        .user-info strong {
            color: var(--text-light);
        }

        .btn-logout {
            padding: 8px 16px;
            background: var(--logout-bg);
            border: 1px solid var(--logout-border);
            border-radius: 8px;
            color: var(--logout-color);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: var(--danger);
            color: #FFF;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.3);
        }

        /* Theme Toggle in Navbar */
        .theme-toggle-nav {
            background: var(--btn-action-bg);
            border: 1px solid var(--btn-action-border);
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
        }

        .stat-card.active-card::before { background: var(--success); }
        .stat-card.suspended-card::before { background: var(--warning); }

        .stat-title {
            font-size: 14px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
        }

        /* Actions Bar */
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex-grow: 1;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 16px;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-light);
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 10px var(--primary-glow);
        }

        .btn-create {
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--primary) 0%, #106294 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px var(--primary-glow);
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(28, 141, 205, 0.4);
        }

        /* Licenses Table Card */
        .table-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background: var(--table-header-bg);
            padding: 16px 20px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
        }

        td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: var(--row-hover-bg);
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-active {
            background: var(--badge-active-bg);
            color: var(--badge-active-text);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-suspended {
            background: var(--badge-suspended-bg);
            color: var(--badge-suspended-text);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .badge-revoked {
            background: var(--badge-revoked-bg);
            color: var(--badge-revoked-text);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .license-key-display {
            font-family: monospace;
            background: var(--key-display-bg);
            padding: 4px 8px;
            border-radius: 6px;
            color: var(--primary);
            font-size: 13px;
        }

        .action-links {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            background: var(--btn-action-bg);
            border: 1px solid var(--btn-action-border);
            color: var(--text-light);
        }

        .btn-action:hover {
            background: rgba(128, 128, 128, 0.15);
        }

        .btn-view {
            background: rgba(28, 141, 205, 0.1);
            border: 1px solid rgba(28, 141, 205, 0.25);
            color: var(--primary);
        }

        .btn-view:hover {
            background: var(--primary);
            color: white;
            box-shadow: 0 0 10px rgba(28, 141, 205, 0.3);
        }

        .form-toggle {
            display: inline;
        }

        .btn-toggle-status {
            border: none;
            background: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 12px;
            font-family: inherit;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            transition: all 0.2s;
            background: var(--btn-action-bg);
        }

        .btn-toggle-status:hover {
            background: rgba(245, 158, 11, 0.2);
            color: var(--text-light);
        }

        .text-expired {
            color: var(--danger);
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header -->
        <header>
            <div class="logo">SandsLab License Manager</div>
            <div class="user-nav">
                <div class="user-info">Logged in as: <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong></div>
                
                <!-- Theme Toggle Button -->
                <button class="theme-toggle-nav" aria-label="Toggle theme" onclick="toggleTheme()">
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

                <a href="<?= dirname($_SERVER['SCRIPT_NAME']) === '/' ? '/logout' : dirname($_SERVER['SCRIPT_NAME']) . '/logout' ?>" class="btn-logout">Logout</a>
            </div>
        </header>

        <?php
            // Calculate stats
            $total = count($licenses);
            $active = 0;
            $suspended = 0;
            foreach ($licenses as $l) {
                if ($l['status'] === 'active') {
                    $active++;
                } else {
                    $suspended++;
                }
            }
        ?>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">Total Applications Licensed</div>
                <div class="stat-value"><?= $total ?></div>
            </div>
            <div class="stat-card active-card">
                <div class="stat-title">Active Licenses</div>
                <div class="stat-value" style="color: var(--success);"><?= $active ?></div>
            </div>
            <div class="stat-card suspended-card">
                <div class="stat-title">Suspended / Revoked</div>
                <div class="stat-value" style="color: var(--warning);"><?= $suspended ?></div>
            </div>
        </div>

        <!-- Action row -->
        <div class="actions-bar">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by customer, domain, or key..." onkeyup="filterTable()">
            </div>
            <a href="<?= dirname($_SERVER['SCRIPT_NAME']) === '/' ? '/licenses/create' : dirname($_SERVER['SCRIPT_NAME']) . '/licenses/create' ?>" class="btn-create">+ Issue New License</a>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <table id="licensesTable">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>App Name</th>
                        <th>Registered Domain</th>
                        <th>IP Address</th>
                        <th>License Key</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($licenses)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px 0;">
                                No licenses issued yet. Click "+ Issue New License" to get started.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($licenses as $license): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($license['customer_name']) ?></strong></td>
                                <td><?= htmlspecialchars($license['application_name']) ?></td>
                                <td><a href="http://<?= htmlspecialchars($license['domain_name']) ?>" target="_blank" style="color: var(--primary); text-decoration: none;"><?= htmlspecialchars($license['domain_name']) ?></a></td>
                                <td><?= htmlspecialchars($license['ip_address'] ?? 'Any') ?></td>
                                <td>
                                    <span class="license-key-display"><?= htmlspecialchars($license['license_key']) ?></span>
                                </td>
                                <td>
                                    <?php if (empty($license['expiry_date'])): ?>
                                        <span style="color: var(--text-muted); font-size: 13px;">No Expiry</span>
                                    <?php else: ?>
                                        <?php 
                                            $isExpired = date('Y-m-d') > $license['expiry_date'];
                                            $formattedDate = date('M d, Y', strtotime($license['expiry_date']));
                                        ?>
                                        <span class="<?= $isExpired ? 'text-expired' : '' ?>">
                                            <?= $formattedDate ?> <?= $isExpired ? '(Expired)' : '' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= htmlspecialchars($license['status']) ?>">
                                        <?= htmlspecialchars($license['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-links">
                                        <a href="<?= (dirname($_SERVER['SCRIPT_NAME']) === '/' ? '/licenses/view' : dirname($_SERVER['SCRIPT_NAME']) . '/licenses/view') . '?id=' . $license['id'] ?>" class="btn-action btn-view">Keys & Integration</a>
                                        
                                        <form action="<?= dirname($_SERVER['SCRIPT_NAME']) === '/' ? '/licenses/toggle' : dirname($_SERVER['SCRIPT_NAME']) . '/licenses/toggle' ?>" method="POST" class="form-toggle">
                                            <input type="hidden" name="id" value="<?= $license['id'] ?>">
                                            <?php if ($license['status'] === 'active'): ?>
                                                <input type="hidden" name="status" value="suspended">
                                                <button type="submit" class="btn-toggle-status" onclick="return confirm('Suspend this license? Client server logins will be blocked.')">Suspend</button>
                                            <?php else: ?>
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="btn-toggle-status" style="color: var(--success);" onclick="return confirm('Re-activate this license?')">Activate</button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark-theme');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }

        function filterTable() {
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("licensesTable");
            tr = table.getElementsByTagName("tr");

            // Loop through all table rows, skip header
            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = "none"; // Default to hidden
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length - 1; j++) { // Check all columns except actions
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = ""; // Show if match found
                            break; 
                        }
                    }
                }
            }
        }
    </script>
</body>
</html>
