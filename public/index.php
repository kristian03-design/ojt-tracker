<?php
// public/index.php - front controller
ini_set('display_errors',1);
error_reporting(E_ALL);

// make sure sessions work inside ephemeral containers
$sessionSave = getenv('SESSION_SAVE_PATH') ?: sys_get_temp_dir();
if (!is_dir($sessionSave)) {
    @mkdir($sessionSave, 0755, true);
}
ini_set('session.save_path', $sessionSave);

// secure cookie when behind a proxy (Railway uses HTTPS fronting)
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
ini_set('session.cookie_secure', $isSecure ? '1' : '0');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
session_start();

// ensure uploads folder exists and is writable
$uploads = __DIR__ . '/uploads';
if (!is_dir($uploads)) {
    @mkdir($uploads, 0755, true);
}
// ensure uploads folder is writable in container
if (!is_writable($uploads)) {
    @chmod($uploads, 0755);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/helpers.php';

// simple router based on GET 'p' parameter
$page = $_GET['p'] ?? 'home';

$role = $_SESSION['user']['role'] ?? null;

switch($page) {
    case 'login':
        require __DIR__ . '/../views/login.php';
        break;
    case 'logout':
        session_destroy();
        header('Location: ?p=login');
        break;
    case 'register':
        require __DIR__ . '/../views/register.php';
        break;
    // student pages
    case 'dashboard':
    case 'log':
    case 'history':
    case 'profile':
    case 'report':
        requireRole('student');
        require __DIR__ . '/../views/'.$page.'.php';
        break;
    default:
        header('Location: ?p=login');
}
