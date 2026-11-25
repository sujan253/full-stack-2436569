<?php
// db.php - database connection and helper functions
// Sujan - 5CS045 Web Development

// !! Change these to your MI-Linux database details !!
define('DB_HOST', 'localhost');
define('DB_USER', '2436569');       // your MI-Linux username
define('DB_PASS', 'Somethingends9812');  // your MI-Linux password
define('DB_NAME', 'db2436569');       // usually same as username on MI-Linux

// connect using PDO - much safer than mysqli
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // real prepared statements only
                ]
            );
        } catch (PDOException $e) {
            // never show db errors to users!
            error_log("DB error: " . $e->getMessage());
            die("Sorry, could not connect to the database. Please try again later.");
        }
    }
    return $pdo;
}

// output escaping - stops XSS attacks
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// input filtering for integers
function filterInt($val, $min = 1, $max = PHP_INT_MAX) {
    $v = filter_var($val, FILTER_VALIDATE_INT);
    if ($v === false || $v < $min || $v > $max) return false;
    return $v;
}

// check if user is logged in, redirect if not
function requireLogin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php?msg=please_login');
        exit;
    }
}

// get current user id from session
function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// simple flash message helper
function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function getFlash() {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// generate CSRF token
function csrfToken() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

// verify CSRF token - call on POST requests
function checkCsrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Invalid request. Please go back and try again.");
    }
}
?>
