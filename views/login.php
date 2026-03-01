<?php
// views/login.php
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $email = $_POST['email'];
    $password = $_POST['password'];
    $userModel = new User();
    $user = $userModel->findByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header('Location: index.php?p=dashboard');
        exit;
    } else {
        $error = 'Invalid email or password. Please try again.';
    }
}

$title = 'Sign In';
ob_start();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>Sign In — OJT Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Geist:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --cream: #faf8f5; --paper: #f4f1ec; --border: #e8e2d9; --border-md: #d4ccbf;
            --ink: #1a1714; --ink-2: #3d3730; --ink-3: #7a7068;
            --amber: #c97d2e; --amber-lt: #fef3e2; --amber-md: #f5d49a;
            --navy: #1c2e4a; --red: #b94040; --red-lt: #fdf0f0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Geist', system-ui, sans-serif;
            background: var(--cream);
            min-height: 100vh;
            display: flex;
        }
        h1, h2, h3 { font-family: 'Instrument Serif', Georgia, serif; font-weight: 400; }

        .split-left {
            width: 420px;
            flex-shrink: 0;
            background: var(--navy);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .split-left::before {
            content: '';
            position: absolute;
            top: -80px; left: -80px;
            width: 320px; height: 320px;
            background: radial-gradient(circle, rgba(201,125,46,0.18) 0%, transparent 70%);
            pointer-events: none;
        }
        .split-left::after {
            content: '';
            position: absolute;
            bottom: -60px; right: -60px;
            width: 260px; height: 260px;
            background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 70%);
            pointer-events: none;
        }
        .brand-mark {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-icon {
            width: 40px; height: 40px;
            background: var(--amber);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .brand-name {
            font-family: 'Instrument Serif', serif;
            font-size: 20px;
            color: #fff;
        }
        .hero-text { position: relative; z-index: 1; }
        .hero-text h2 {
            font-family: 'Instrument Serif', serif;
            font-size: 38px;
            color: #fff;
            line-height: 1.15;
            letter-spacing: -0.03em;
            margin-bottom: 14px;
        }
        .hero-text h2 em { color: var(--amber-md); font-style: italic; }
        .hero-text p {
            font-size: 14px;
            color: rgba(255,255,255,0.5);
            line-height: 1.7;
        }
        .stat-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            position: relative;
            z-index: 1;
        }
        .stat-box {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 14px 16px;
        }
        .stat-box .num {
            font-family: 'Instrument Serif', serif;
            font-size: 28px;
            color: var(--amber-md);
            line-height: 1;
        }
        .stat-box .lbl { font-size: 11px; color: rgba(255,255,255,0.4); margin-top: 3px; }

        .split-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }
        .form-card {
            width: 100%;
            max-width: 400px;
        }
        .form-card h3 {
            font-family: 'Instrument Serif', serif;
            font-size: 28px;
            color: var(--ink);
            margin-bottom: 6px;
        }
        .form-card .sub {
            font-size: 13px;
            color: var(--ink-3);
            margin-bottom: 32px;
        }
        .tabs {
            display: flex;
            background: var(--paper);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 4px;
            margin-bottom: 28px;
        }
        .tab {
            flex: 1;
            padding: 8px;
            text-align: center;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--ink-3);
            text-decoration: none;
        }
        .tab.active {
            background: #fff;
            color: var(--ink);
            box-shadow: 0 1px 4px rgba(0,0,0,0.09);
        }
        .form-group { margin-bottom: 16px; }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: var(--ink-2);
            margin-bottom: 6px;
        }
        .form-input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--border-md);
            border-radius: 8px;
            background: var(--paper);
            font-family: 'Geist', sans-serif;
            font-size: 14px;
            color: var(--ink);
            outline: none;
            transition: all 0.2s;
        }
        .form-input:focus {
            border-color: var(--amber);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(201,125,46,0.12);
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: 9px;
            font-family: 'Geist', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover { background: #233a60; box-shadow: 0 4px 12px rgba(28,46,74,0.28); }
        .btn-submit:active { transform: scale(0.98); }
        .error-box {
            background: var(--red-lt);
            border: 1px solid #f0c0c0;
            border-radius: 8px;
            padding: 11px 14px;
            font-size: 13px;
            color: #7a1a1a;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .help-text { text-align: center; font-size: 12px; color: var(--ink-3); margin-top: 20px; }
        .help-text a { color: var(--amber); font-weight: 600; text-decoration: none; }
        .help-text a:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .split-left { display: none; }
        }
    </style>
</head>
<body>
    <div class="split-left">
        <div class="brand-mark">
            <div class="brand-icon">
                <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <span class="brand-name">OJT Tracker</span>
        </div>

        <div class="hero-text">
            <h2>Track every hour of your <em>training journey</em></h2>
            <p>A beautiful, personal log for your on-the-job training hours. Track progress, build your record, print your report.</p>
        </div>

        <div class="stat-row">
            <div class="stat-box">
                <div class="num">500</div>
                <div class="lbl">Required Hours</div>
            </div>
            <div class="stat-box">
                <div class="num">100%</div>
                <div class="lbl">Completion Goal</div>
            </div>
        </div>
    </div>

    <div class="split-right">
        <div class="form-card">
            <h3>Welcome back</h3>
            <p class="sub">Sign in to your OJT Tracker account</p>

            <div class="tabs">
                <a href="index.php?p=login" class="tab active">Sign In</a>
                <a href="index.php?p=register" class="tab">Register</a>
            </div>

            <?php if (!empty($error)): ?>
            <div class="error-box">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" required autocomplete="email" placeholder="you@university.edu" class="form-input" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••" class="form-input">
                </div>
                <button type="submit" class="btn-submit">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Sign In
                </button>
            </form>

            <p class="help-text">Don't have an account? <a href="index.php?p=register">Create one →</a></p>
        </div>
    </div>
</body>
</html>
<?php
// Bypass layout for login (it's a standalone page)
echo ob_get_clean();
exit;
