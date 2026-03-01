<?php
// public/index.php - front controller
ini_set('display_errors',1);
error_reporting(E_ALL);
session_start();
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
        header('Location: index.php?p=login');
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
        header('Location: index.php?p=login');
}
