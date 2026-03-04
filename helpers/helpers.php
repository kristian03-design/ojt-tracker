<?php
// helpers/helpers.php

function requireRole($role) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
        header('Location: ?p=login');
        exit;
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function check_csrf() {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        die('Invalid CSRF token');
    }
}

function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// mask an email address by replacing all but the first local character with asterisks
function mask_email($email) {
    if (strpos($email, '@') === false) return $email;
    list($local, $domain) = explode('@', $email, 2);
    if (strlen($local) <= 1) {
        $localMasked = str_repeat('*', strlen($local));
    } else {
        $localMasked = substr($local, 0, 1) . str_repeat('*', strlen($local) - 1);
    }
    return $localMasked . '@' . $domain;
}
