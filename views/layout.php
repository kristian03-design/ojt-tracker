<?php
// views/layout.php
$currentPage = $_GET['p'] ?? 'login';
$role = $_SESSION['user']['role'] ?? 'student';
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?php echo isset($title) ? htmlspecialchars($title) . ' — OJT Tracker' : 'OJT Tracker'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Geist:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --cream:     #faf8f5;
            --paper:     #f4f1ec;
            --border:    #e8e2d9;
            --border-md: #d4ccbf;
            --ink:       #1a1714;
            --ink-2:     #3d3730;
            --ink-3:     #7a7068;
            --amber:     #c97d2e;
            --amber-lt:  #fef3e2;
            --amber-md:  #f5d49a;
            --teal:      #1a6b6b;
            --teal-lt:   #e6f4f4;
            --red:       #b94040;
            --red-lt:    #fdf0f0;
            --green:     #2d7a4f;
            --green-lt:  #eef7f2;
            --navy:      #1c2e4a;
            --side-w:    260px;
        }

        * { box-sizing: border-box; }
        body {
            font-family: 'Geist', system-ui, sans-serif;
            background: var(--cream);
            color: var(--ink);
            min-height: 100vh;
            font-size: 14px;
            line-height: 1.6;
        }
        h1, h2, h3 {
            font-family: 'Instrument Serif', Georgia, serif;
            font-weight: 400;
            letter-spacing: -0.02em;
        }
        .serif { font-family: 'Instrument Serif', Georgia, serif; }

        /* ─── SIDEBAR ────────────────────────────────── */
        #sidebar {
            position: fixed;
            left: 0; top: 0; bottom: 0;
            width: var(--side-w);
            background: var(--navy);
            display: flex;
            flex-direction: column;
            z-index: 40;
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
        }
        .side-logo {
            padding: 28px 24px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .side-logo h2 {
            font-family: 'Instrument Serif', serif;
            font-size: 20px;
            color: #fff;
            letter-spacing: -0.03em;
            margin: 0;
        }
        .side-logo span {
            font-family: 'Geist', sans-serif;
            font-size: 11px;
            color: rgba(255,255,255,0.4);
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .side-icon {
            width: 34px; height: 34px;
            background: var(--amber);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .side-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }
        .side-section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.25);
            padding: 0 12px;
            margin-bottom: 6px;
            margin-top: 16px;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            color: rgba(255,255,255,0.55);
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.18s;
            position: relative;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.07);
            color: rgba(255,255,255,0.9);
        }
        .nav-link.active {
            background: rgba(201,125,46,0.2);
            color: #f5c87a;
        }
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 6px; bottom: 6px;
            width: 3px;
            background: var(--amber);
            border-radius: 0 3px 3px 0;
        }
        .nav-link svg { flex-shrink: 0; opacity: 0.7; }
        .nav-link.active svg { opacity: 1; }
        .side-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,0.07);
        }
        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(255,255,255,0.05);
        }
        .user-avatar {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: var(--amber);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: var(--navy);
            flex-shrink: 0;
            font-family: 'Geist', sans-serif;
        }
        .user-name { font-size: 13px; color: #fff; font-weight: 500; }
        .user-role { font-size: 11px; color: rgba(255,255,255,0.4); text-transform: capitalize; }

        /* ─── MAIN CONTENT ───────────────────────────── */
        #main-wrap {
            margin-left: var(--side-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            padding: 18px 36px;
            border-bottom: 1px solid var(--border);
            background: var(--cream);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 30;
        }
        .topbar-title h1 {
            font-size: 22px;
            margin: 0;
            color: var(--ink);
        }
        .topbar-title p {
            font-size: 12px;
            color: var(--ink-3);
            margin: 0;
        }
        .content-area { padding: 32px 36px; flex: 1; }

        /* ─── CARDS ──────────────────────────────────── */
        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .card-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-header h3 {
            font-size: 16px;
            margin: 0;
            color: var(--ink);
        }
        .card-body { padding: 24px; }

        /* ─── STAT TILES ─────────────────────────────── */
        .stat-tile {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px 24px;
            position: relative;
            overflow: hidden;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .stat-tile:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.07); transform: translateY(-1px); }
        .stat-tile::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 4px;
            height: 100%;
        }
        .stat-tile.amber::after { background: var(--amber); }
        .stat-tile.teal::after  { background: var(--teal); }
        .stat-tile.navy::after  { background: var(--navy); }
        .stat-tile.green::after { background: var(--green); }
        .tile-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--ink-3);
            margin-bottom: 8px;
        }
        .tile-value {
            font-family: 'Instrument Serif', serif;
            font-size: 36px;
            color: var(--ink);
            line-height: 1;
            margin-bottom: 4px;
        }
        .tile-sub { font-size: 12px; color: var(--ink-3); }

        /* ─── PROGRESS BAR ───────────────────────────── */
        .prog-track {
            height: 8px;
            background: var(--paper);
            border-radius: 99px;
            overflow: hidden;
            border: 1px solid var(--border);
        }
        .prog-fill {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, var(--amber), #e8a63a);
            transition: width 1.2s cubic-bezier(0.16,1,0.3,1);
            position: relative;
        }
        .prog-fill::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        @keyframes shimmer { 0%{transform:translateX(-100%)} 100%{transform:translateX(100%)} }

        /* ─── BADGES ─────────────────────────────────── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 99px;
            letter-spacing: 0.03em;
        }
        .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
        .badge-pending  { background: var(--amber-lt); color: #8a4e10; border: 1px solid var(--amber-md); }
        .badge-pending::before  { background: var(--amber); }
        .badge-approved { background: var(--green-lt); color: #1a5233; border: 1px solid #b2dfc5; }
        .badge-approved::before { background: var(--green); }
        .badge-rejected { background: var(--red-lt);   color: #7a1a1a; border: 1px solid #f0c0c0; }
        .badge-rejected::before { background: var(--red); }

        /* ─── FORM INPUTS ────────────────────────────── */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-2);
            letter-spacing: 0.04em;
            margin-bottom: 6px;
        }
        .form-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-md);
            border-radius: 8px;
            background: var(--paper);
            color: var(--ink);
            font-family: 'Geist', sans-serif;
            font-size: 14px;
            transition: all 0.2s;
            outline: none;
        }
        .form-input:focus {
            border-color: var(--amber);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(201,125,46,0.12);
        }
        .form-input::placeholder { color: var(--ink-3); }
        textarea.form-input { resize: vertical; min-height: 80px; }

        /* ─── BUTTONS ────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 20px;
            border-radius: 8px;
            font-family: 'Geist', sans-serif;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.18s;
            border: none;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn:active { transform: scale(0.97); }
        .btn-primary {
            background: var(--navy);
            color: #fff;
            box-shadow: 0 1px 3px rgba(28,46,74,0.25);
        }
        .btn-primary:hover { background: #233a60; box-shadow: 0 4px 12px rgba(28,46,74,0.3); }
        .btn-amber {
            background: var(--amber);
            color: #fff;
            box-shadow: 0 1px 3px rgba(201,125,46,0.3);
        }
        .btn-amber:hover { background: #b86d22; }
        .btn-ghost {
            background: transparent;
            color: var(--ink-2);
            border: 1px solid var(--border-md);
        }
        .btn-ghost:hover { background: var(--paper); border-color: var(--border-md); }
        .btn-danger {
            background: var(--red-lt);
            color: var(--red);
            border: 1px solid #f0c0c0;
        }
        .btn-danger:hover { background: #fce8e8; }
        .btn-success {
            background: var(--green-lt);
            color: var(--green);
            border: 1px solid #b2dfc5;
        }
        .btn-success:hover { background: #daf2e6; }
        .btn-sm { padding: 6px 14px; font-size: 12px; border-radius: 6px; }

        /* ─── TABLE ──────────────────────────────────── */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead tr { border-bottom: 2px solid var(--border); }
        .data-table th {
            text-align: left;
            padding: 10px 16px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: var(--ink-3);
        }
        .data-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }
        .data-table tbody tr:last-child { border-bottom: none; }
        .data-table tbody tr:hover { background: var(--paper); }
        .data-table td { padding: 13px 16px; color: var(--ink-2); vertical-align: middle; }
        .data-table td.mono { font-variant-numeric: tabular-nums; letter-spacing: 0.03em; }

        /* ─── TOAST ──────────────────────────────────── */
        #toast-container {
            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }
        .toast {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 500;
            box-shadow: 0 8px 32px rgba(0,0,0,0.14), 0 1px 4px rgba(0,0,0,0.06);
            pointer-events: all;
            animation: toastIn 0.35s cubic-bezier(0.16,1,0.3,1) both;
            max-width: 340px;
        }
        .toast.out { animation: toastOut 0.3s ease-in both; }
        .toast-success { background: #fff; border-left: 4px solid var(--green); color: var(--ink); }
        .toast-error   { background: #fff; border-left: 4px solid var(--red);   color: var(--ink); }
        .toast-info    { background: #fff; border-left: 4px solid var(--navy); color: var(--ink); }
        .toast-icon { width: 20px; height: 20px; border-radius: 50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:11px; font-weight:800; }
        .toast-success .toast-icon { background: var(--green-lt); color: var(--green); }
        .toast-error   .toast-icon { background: var(--red-lt);   color: var(--red); }
        .toast-info    .toast-icon { background: var(--ink-3);   color: var(--navy); }
        .toast-close {
            background: transparent;
            border: none;
            font-size: 16px;
            line-height: 1;
            opacity: 0.6;
            cursor: pointer;
            margin-left: auto;
            padding: 0;
        }
        .toast-close:hover { opacity: 1; }
        @keyframes toastIn  { from{opacity:0;transform:translateY(16px) scale(0.95)} to{opacity:1;transform:none} }
        @keyframes toastOut { to{opacity:0;transform:translateY(8px) scale(0.96)} }

        /* ─── MOBILE OVERLAY ─────────────────────────── */
        #mob-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 39;
        }
        .hamburger {
            display: none;
            width: 38px; height: 38px;
            border: 1px solid var(--border-md);
            border-radius: 8px;
            background: var(--paper);
            cursor: pointer;
            align-items: center;
            justify-content: center;
        }

        /* ─── CONFIRM MODAL ─────────────────────────── */
        .confirm-modal {
            position: fixed; inset:0;
            background: rgba(0,0,0,0.4);
            display: none;
            align-items: center; justify-content: center;
            z-index: 1000;
        }
        .confirm-modal.show { display: flex; }
        .confirm-box {
            background:#fff;padding:24px 28px;border-radius:12px;max-width:320px;text-align:center;
            box-shadow:0 12px 32px rgba(0,0,0,0.2);
        }
        .confirm-buttons { margin-top:16px;display:flex;gap:12px;justify-content:center; }

        /* ─── ANIMATIONS ─────────────────────────────── */
        .fade-up {
            opacity: 0;
            transform: translateY(12px);
            animation: fadeUp 0.4s cubic-bezier(0.16,1,0.3,1) both;
        }
        @keyframes fadeUp { to { opacity:1; transform:none; } }
        .delay-1 { animation-delay: 0.05s; }
        .delay-2 { animation-delay: 0.1s; }
        .delay-3 { animation-delay: 0.15s; }
        .delay-4 { animation-delay: 0.2s; }

        /* ─── MISC ───────────────────────────────────── */
        .divider { border: none; border-top: 1px solid var(--border); margin: 0; }
        .empty-state {
            text-align: center;
            padding: 56px 24px;
            color: var(--ink-3);
        }
        .empty-state svg { margin: 0 auto 12px; opacity: 0.35; }
        .empty-state p { font-size: 14px; }
        a { color: inherit; }

        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #mob-overlay.show { display: block; }
            .hamburger { display: flex; }
            #main-wrap { margin-left: 0; }
            .content-area { padding: 20px 16px; }
            .topbar { padding: 14px 16px; }
        }
    </style>
</head>
<body>

<div id="toast-container"></div>

<?php if (isset($_SESSION['user'])): ?>
<!-- SIDEBAR -->
<aside id="sidebar">
    <div class="side-logo">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
            <div class="side-icon">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <h2>OJT Hours Tracker</h2>
        </div>
        <span><?php echo ucfirst($_SESSION['user']['role']); ?> Portal</span>
    </div>

    <nav class="side-nav">
        <?php
        $p = $_GET['p'] ?? '';
        // only student view supported; admin removed
        $links = [
            ['p'=>'dashboard',  'icon'=>'grid',     'label'=>'Dashboard'],
            ['p'=>'log',        'icon'=>'plus',     'label'=>'Log Hours'],
            ['p'=>'history',    'icon'=>'list',     'label'=>'History'],
            ['p'=>'report',     'icon'=>'file',     'label'=>'Report'],
            ['p'=>'profile',    'icon'=>'user',     'label'=>'Profile'],
        ];
        $icons = [
            'grid'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>',
            'plus'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>',
            'list'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
            'file'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
            'user'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>',
            'check' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ];
        // refresh session photo if missing (in case it was updated elsewhere)
        if (isset($_SESSION['user']['id']) && empty($_SESSION['user']['photo'])) {
            require_once __DIR__ . '/../models/User.php';
            $um = new User();
            $dbu = $um->findById($_SESSION['user']['id']);
            if ($dbu && !empty($dbu['photo'])) {
                $_SESSION['user']['photo'] = $dbu['photo'];
            }
        }
        echo '<div class="side-section-label">Navigation</div>';
        foreach ($links as $link):
            $active = ($p === $link['p']) ? 'active' : '';
        ?>
        <a href="index.php?p=<?php echo $link['p']; ?>" class="nav-link <?php echo $active; ?>">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><?php echo $icons[$link['icon']]; ?></svg>
            <?php echo $link['label']; ?>
        </a>
        <?php endforeach; ?>

        <div class="side-section-label" style="margin-top:24px">Account</div>
        <a href="index.php?p=logout" class="nav-link" style="color:rgba(255,100,100,0.65)" onclick="event.preventDefault(); showConfirm('Are you sure you want to sign out?').then(ok=>{ if(ok){ location.href='index.php?p=logout'; }});">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Sign Out
        </a>
    </nav>

    <div class="side-footer">
        <div class="user-chip">
            <?php if (!empty($_SESSION['user']['photo'])): ?>
            <img src="<?php echo htmlspecialchars($_SESSION['user']['photo']); ?>" class="user-avatar" alt="Avatar" style="object-fit:cover;width: 48px;height: 48px;border-radius:8px">
            <?php else: ?>
            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user']['full_name'] ?? 'U', 0, 1)); ?></div>
            <?php endif; ?>
            <div>
                <div class="user-name"><?php echo htmlspecialchars(explode(' ', $_SESSION['user']['full_name'] ?? '')[0]); ?></div>
                <div class="user-role"><?php echo $_SESSION['user']['role'] ?? ''; ?></div>
            </div>
        </div>
    </div>
</aside>
<div id="mob-overlay" onclick="closeSidebar()"></div>

<!-- confirmation modal -->
<div id="confirm-modal" class="confirm-modal">
    <div class="confirm-box">
        <p id="confirm-msg">Are you sure?</p>
        <div class="confirm-buttons">
            <button id="confirm-yes" class="btn btn-primary">Yes</button>
            <button id="confirm-no" class="btn btn-ghost">No</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- MAIN -->
<div id="main-wrap" <?php if (!isset($_SESSION['user'])): ?>style="margin-left:0"<?php endif; ?>>

    <?php if (isset($_SESSION['user'])): ?>
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:14px">
            <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="topbar-title">
                <h1><?php echo $title ?? 'OJT Tracker'; ?></h1>
                <?php if (!empty($subtitle)): ?><p><?php echo htmlspecialchars($subtitle); ?></p><?php endif; ?>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <?php if (!empty($topbarAction)): echo $topbarAction; endif; ?>
            <div style="font-size:12px;color:var(--ink-3);background:var(--paper);border:1px solid var(--border);padding:5px 12px;border-radius:6px">
                <span id="currentDate"></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="content-area">
        <?php echo $content ?? ''; ?>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('mob-overlay').classList.toggle('show');
}

function showConfirm(message) {
    return new Promise(resolve => {
        const modal = document.getElementById('confirm-modal');
        const msgEl = document.getElementById('confirm-msg');
        msgEl.textContent = message;
        modal.classList.add('show');
        const yes = document.getElementById('confirm-yes');
        const no = document.getElementById('confirm-no');
        function cleanup(result) {
            modal.classList.remove('show');
            yes.removeEventListener('click', onYes);
            no.removeEventListener('click', onNo);
            resolve(result);
        }
        function onYes() { cleanup(true); }
        function onNo() { cleanup(false); }
        yes.addEventListener('click', onYes);
        no.addEventListener('click', onNo);
    });
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('mob-overlay').classList.remove('show');
}

function updateDate() {
    const d = new Date();
    const options = { month: 'short', day: 'numeric', year: 'numeric' };
    const el = document.getElementById('currentDate');
    if (el) {
        el.textContent = d.toLocaleDateString('en-US', options);
    }
}
updateDate();
setInterval(updateDate, 60000);

function showToast(message, type = 'success', duration = 4000) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    let icon;
    switch(type) {
        case 'error': icon = '✗'; break;
        case 'info':  icon = 'ℹ'; break;
        default:      icon = '✓';
    }
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <span class="toast-icon">${icon}</span>
        <span class="toast-msg">${message}</span>
        <button class="toast-close" aria-label="Close">&times;</button>
    `;
    container.appendChild(toast);
    // allow manual dismissal
    toast.querySelector('.toast-close').addEventListener('click', () => toast.remove());
    // automatic removal
    setTimeout(() => {
        toast.classList.add('out');
        setTimeout(() => toast.remove(), 320);
    }, duration);
}

// Animate progress bars on load
window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-progress]').forEach(el => {
        const pct = parseFloat(el.dataset.progress) || 0;
        setTimeout(() => { el.style.width = Math.min(100, pct) + '%'; }, 120);
    });
});

<?php if (!empty($message)): ?>
// JSON_UNESCAPED_UNICODE preserves characters like • instead of \u2022
showToast(<?php echo json_encode($message, JSON_UNESCAPED_UNICODE); ?>, 'success');
<?php endif; ?>
<?php if (!empty($error)): ?>
showToast(<?php echo json_encode($error, JSON_UNESCAPED_UNICODE); ?>, 'error');
<?php endif; ?>
</script>
<?php if (!empty($extraJS)): echo $extraJS; endif; ?>
</body>
</html>
