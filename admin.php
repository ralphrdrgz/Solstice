<?php
session_start();

// --- Config ---
define('ADMIN_PASSWORD', 'solstice2026');
$csv_file = __DIR__ . '/entries/entries.csv';

// --- Handle login ---
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin_auth'] = true;
    } else {
        $login_error = 'Incorrect password. Please try again.';
    }
}

// --- Handle logout ---
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// --- Handle CSV download ---
if (isset($_GET['download']) && ($_SESSION['admin_auth'] ?? false)) {
    if (file_exists($csv_file)) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="entries.csv"');
        readfile($csv_file);
        exit;
    }
}

// --- Read entries ---
$headers = [];
$entries = [];

if ($_SESSION['admin_auth'] ?? false) {
    if (file_exists($csv_file)) {
        $handle = fopen($csv_file, 'r');
        if ($handle) {
            $headers = fgetcsv($handle) ?: [];
            while (($row = fgetcsv($handle)) !== false) {
                $entries[] = $row;
            }
            fclose($handle);
        }
    }
}

$authenticated = $_SESSION['admin_auth'] ?? false;
$total = count($entries);
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Admin — Submitted Entries</title>
        <link rel="stylesheet" href="css/bootstrap.min.css" />
        <style>
            @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap");

            * { box-sizing: border-box; }

            body {
                font-family: "Inter", sans-serif;
                background-color: #f5f4f3;
                color: #786968;
                margin: 0;
            }

            /* --- Topbar --- */
            .admin-topbar {
                background-color: #786968;
                padding: 14px 32px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .admin-topbar h1 {
                color: #fff;
                font-size: 18px;
                font-weight: 600;
                margin: 0;
            }

            .admin-topbar a {
                color: #f5f4f3;
                font-size: 13px;
                text-decoration: none;
                opacity: 0.8;
            }

            .admin-topbar a:hover { opacity: 1; }

            /* --- Login --- */
            .login-wrapper {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .login-card {
                background: #fff;
                padding: 48px 40px;
                width: 100%;
                max-width: 400px;
                box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            }

            .login-card h2 {
                font-size: 22px;
                font-weight: 700;
                margin-bottom: 8px;
                color: #786968;
            }

            .login-card p {
                font-size: 14px;
                color: #84786c;
                margin-bottom: 28px;
            }

            .login-card label {
                font-size: 13px;
                font-weight: 600;
                display: block;
                margin-bottom: 6px;
            }

            .login-card input[type="password"] {
                width: 100%;
                padding: 10px 14px;
                border: 1px solid #ccc;
                font-family: inherit;
                font-size: 14px;
                outline: none;
                margin-bottom: 16px;
                transition: border-color 0.2s;
            }

            .login-card input[type="password"]:focus {
                border-color: #786968;
            }

            .error-msg {
                font-size: 13px;
                color: #9b3a3a;
                margin-bottom: 12px;
            }

            /* --- Buttons --- */
            .btn-primary-custom {
                background-color: #c8820b;
                color: #fff;
                border: none;
                padding: 10px 24px;
                font-family: inherit;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: background-color 0.2s;
                text-decoration: none;
                display: inline-block;
            }

            .btn-primary-custom:hover { background-color: #786968; color: #fff; }

            .btn-outline-custom {
                background-color: transparent;
                color: #786968;
                border: 1px solid #786968;
                padding: 9px 20px;
                font-family: inherit;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                text-decoration: none;
                display: inline-block;
            }

            .btn-outline-custom:hover {
                background-color: #786968;
                color: #fff;
            }

            /* --- Dashboard --- */
            .admin-body {
                padding: 36px 32px;
                max-width: 1200px;
                margin: 0 auto;
            }

            .stat-card {
                background: #fff;
                padding: 24px 28px;
                display: inline-flex;
                flex-direction: column;
                gap: 4px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06);
                min-width: 160px;
                margin-bottom: 28px;
            }

            .stat-card span:first-child {
                font-size: 13px;
                color: #84786c;
                font-weight: 500;
            }

            .stat-card span:last-child {
                font-size: 32px;
                font-weight: 700;
                color: #786968;
            }

            /* --- Table --- */
            .table-wrapper {
                background: #fff;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06);
                overflow-x: auto;
            }

            .table-toolbar {
                padding: 18px 24px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 1px solid #eee;
            }

            .table-toolbar h2 {
                font-size: 16px;
                font-weight: 600;
                margin: 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 14px;
            }

            thead {
                background-color: #f5f4f3;
            }

            thead th {
                padding: 12px 20px;
                text-align: left;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: #84786c;
                white-space: nowrap;
            }

            tbody tr {
                border-bottom: 1px solid #f0eeee;
                transition: background-color 0.15s;
            }

            tbody tr:last-child { border-bottom: none; }
            tbody tr:hover { background-color: #faf9f9; }

            tbody td {
                padding: 14px 20px;
                color: #786968;
                vertical-align: top;
            }

            tbody td.message-col {
                max-width: 300px;
                white-space: pre-wrap;
                word-break: break-word;
            }

            .no-entries {
                padding: 60px 20px;
                text-align: center;
                color: #84786c;
                font-size: 14px;
            }

            .badge {
                display: inline-block;
                padding: 3px 10px;
                font-size: 12px;
                font-weight: 600;
                background-color: #f0eeee;
                color: #786968;
                border-radius: 20px;
            }
        </style>
    </head>
    <body>

    <?php if (!$authenticated): ?>

        <!-- Login -->
        <div class="login-wrapper">
            <div class="login-card">
                <h2>Admin Login</h2>
                <p>Enter your password to view submitted entries.</p>

                <?php if ($login_error): ?>
                    <p class="error-msg"><?= htmlspecialchars($login_error) ?></p>
                <?php endif; ?>

                <form method="post" action="admin.php">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter password"
                        autofocus
                        required
                    />
                    <button type="submit" class="btn-primary-custom" style="width:100%">
                        Log In
                    </button>
                </form>
            </div>
        </div>

    <?php else: ?>

        <!-- Topbar -->
        <div class="admin-topbar">
            <h1>Solstice &mdash; Submitted Entries</h1>
            <a href="admin.php?logout=1">Log out</a>
        </div>

        <!-- Dashboard -->
        <div class="admin-body">

            <!-- Stat -->
            <div class="stat-card">
                <span>Total Entries</span>
                <span><?= $total ?></span>
            </div>

            <!-- Table -->
            <div class="table-wrapper">
                <div class="table-toolbar">
                    <h2>Entries <span class="badge"><?= $total ?></span></h2>
                    <?php if ($total > 0): ?>
                        <a href="admin.php?download=1" class="btn-outline-custom">
                            &#8595; Download CSV
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($total === 0): ?>
                    <div class="no-entries">No entries submitted yet.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <?php foreach ($headers as $header): ?>
                                    <th><?= htmlspecialchars($header) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $row): ?>
                                <tr>
                                    <?php foreach ($row as $i => $cell): ?>
                                        <td <?= $headers[$i] === 'Message' ? 'class="message-col"' : '' ?>>
                                            <?= htmlspecialchars($cell) ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        </div>

    <?php endif; ?>

    </body>
</html>
