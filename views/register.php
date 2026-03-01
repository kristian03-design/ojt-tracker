<?php
// views/register.php
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $full = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $confirm = $_POST['confirm'];
    if ($pass !== $confirm) {
        $error = "Passwords don't match.";
    } elseif (strlen($pass) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            $error = 'That email is already registered.';
        } else {
            $userModel->create([
                'full_name'=>$full,
                'email'=>$email,
                'password'=>$pass,
                'course'=>trim($_POST['course'] ?? ''),
            ]);
            $message = 'Account created! Please sign in.';
        }
    }
}

ob_start();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>Register — OJT Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Geist:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --cream: #faf8f5; --paper: #f4f1ec; --border: #e8e2d9; --border-md: #d4ccbf;
            --ink: #1a1714; --ink-2: #3d3730; --ink-3: #7a7068;
            --amber: #c97d2e; --amber-lt: #fef3e2; --amber-md: #f5d49a;
            --navy: #1c2e4a; --red: #b94040; --red-lt: #fdf0f0;
            --green: #2d7a4f; --green-lt: #eef7f2;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Geist', system-ui, sans-serif; background: var(--cream); min-height: 100vh; display: flex; }
        h1,h2,h3 { font-family: 'Instrument Serif', Georgia, serif; font-weight: 400; }
        .split-left {
            width: 380px; flex-shrink: 0; background: var(--navy); padding: 48px 40px;
            display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;
        }
        .split-left::before {
            content: ''; position: absolute; bottom: -100px; right: -100px;
            width: 340px; height: 340px;
            background: radial-gradient(circle, rgba(201,125,46,0.15) 0%, transparent 70%);
        }
        .brand-mark { display:flex; align-items:center; gap:12px; }
        .brand-icon { width:40px;height:40px;background:var(--amber);border-radius:10px;display:flex;align-items:center;justify-content:center; }
        .brand-name { font-family:'Instrument Serif',serif;font-size:20px;color:#fff; }
        .steps { position:relative; z-index:1; }
        .steps h3 { font-family:'Instrument Serif',serif;font-size:26px;color:#fff;margin-bottom:24px; }
        .step { display:flex;align-items:flex-start;gap:12px;margin-bottom:18px; }
        .step-num { width:24px;height:24px;border-radius:50%;background:var(--amber);color:var(--navy);font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px; }
        .step-text { font-size:13px;color:rgba(255,255,255,0.65);line-height:1.5; }
        .step-text strong { color:#fff; display:block;font-size:13.5px;margin-bottom:2px; }

        .split-right { flex:1;display:flex;align-items:center;justify-content:center;padding:40px 24px; }
        .form-card { width:100%;max-width:440px; }
        .form-card h3 { font-family:'Instrument Serif',serif;font-size:28px;color:var(--ink);margin-bottom:6px; }
        .form-card .sub { font-size:13px;color:var(--ink-3);margin-bottom:28px; }
        .tabs { display:flex;background:var(--paper);border:1px solid var(--border);border-radius:10px;padding:4px;margin-bottom:24px; }
        .tab { flex:1;padding:8px;text-align:center;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;transition:all 0.2s;color:var(--ink-3);text-decoration:none; }
        .tab.active { background:#fff;color:var(--ink);box-shadow:0 1px 4px rgba(0,0,0,0.09); }
        .grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
        .form-group { margin-bottom:14px; }
        .form-label { display:block;font-size:12px;font-weight:600;letter-spacing:0.04em;color:var(--ink-2);margin-bottom:6px; }
        .form-input { width:100%;padding:10px 14px;border:1px solid var(--border-md);border-radius:8px;background:var(--paper);font-family:'Geist',sans-serif;font-size:14px;color:var(--ink);outline:none;transition:all 0.2s; }
        .form-input:focus { border-color:var(--amber);background:#fff;box-shadow:0 0 0 3px rgba(201,125,46,0.12); }
        .form-input::placeholder { color:var(--ink-3); }
        .btn-submit { width:100%;padding:12px;background:var(--navy);color:#fff;border:none;border-radius:9px;font-family:'Geist',sans-serif;font-size:14px;font-weight:600;cursor:pointer;transition:all 0.2s;margin-top:6px;display:flex;align-items:center;justify-content:center;gap:8px; }
        .btn-submit:hover { background:#233a60;box-shadow:0 4px 12px rgba(28,46,74,0.28); }
        .btn-submit:active { transform:scale(0.98); }
        .alert { padding:11px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px; }
        .alert-error { background:var(--red-lt);border:1px solid #f0c0c0;color:#7a1a1a; }
        .alert-success { background:var(--green-lt);border:1px solid #b2dfc5;color:#1a5233; }
        .help-text { text-align:center;font-size:12px;color:var(--ink-3);margin-top:16px; }
        .help-text a { color:var(--amber);font-weight:600;text-decoration:none; }
        @media (max-width:768px) { .split-left { display:none; } .grid-2 { grid-template-columns:1fr; } }
    </style>
</head>
<body>
    <div class="split-left">
        <div class="brand-mark">
            <div class="brand-icon"><svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
            <span class="brand-name">OJT Tracker</span>
        </div>
        <div class="steps">
            <h3>Get started in minutes</h3>
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-text"><strong>Create your account</strong>Fill in your details below to register.</div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-text"><strong>Log your daily hours</strong>Record time-in and time-out each day.</div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-text"><strong>Start logging</strong>Enter your OJT hours; they count immediately toward your total.</div>
            </div>
            <div class="step">
                <div class="step-num">4</div>
                <div class="step-text"><strong>Track completion</strong>Watch your progress toward 100%.</div>
            </div>
        </div>
        <div style="font-size:12px;color:rgba(255,255,255,0.3)">OJT Tracker © <?php echo date('Y'); ?></div>
    </div>

    <div class="split-right">
        <div class="form-card">
            <h3>Create account</h3>
            <p class="sub">Start tracking your OJT hours today</p>

            <div class="tabs">
                <a href="index.php?p=login" class="tab">Sign In</a>
                <a href="index.php?p=register" class="tab active">Register</a>
            </div>

            <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                <?php echo htmlspecialchars($message); ?> <a href="index.php?p=login" style="font-weight:600;color:#1a5233;text-decoration:underline">Sign in →</a>
            </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" required placeholder="Juan dela Cruz" class="form-input" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" required placeholder="you@university.edu" class="form-input" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Course / Program</label>
                    <input type="text" name="course" class="form-input" placeholder="e.g. BS Computer Science" value="<?php echo htmlspecialchars($_POST['course'] ?? ''); ?>">
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" required placeholder="Min 8 characters" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm" required placeholder="Re-enter" class="form-input">
                    </div>
                </div>
                <button type="submit" class="btn-submit">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Create Account
                </button>
            </form>
            <p class="help-text">Already have an account? <a href="index.php?p=login">Sign in →</a></p>
        </div>
    </div>
</body>
</html>
<?php
echo ob_get_clean();
exit;
