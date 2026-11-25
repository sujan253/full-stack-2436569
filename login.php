<?php
// login.php
// Handles user login with session, CSRF and captcha
// Sujan - 5CS045

session_start();
require_once 'db.php';
require_once 'TwigHelper.php';

// already logged in? go home
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = null;
$oldUsername = '';

// generate captcha numbers and store answer in session
if (empty($_SESSION['captcha_a'])) {
    $_SESSION['captcha_a'] = rand(1, 10);
    $_SESSION['captcha_b'] = rand(1, 10);
}
$captchaA = $_SESSION['captcha_a'];
$captchaB = $_SESSION['captcha_b'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $userCaptcha = (int)($_POST['captcha'] ?? -99);

    // check captcha first - compare against the session-stored numbers, never trust client input
    $expectedAnswer = (int)($_SESSION['captcha_a'] ?? -1) + (int)($_SESSION['captcha_b'] ?? -1);
    if ($userCaptcha !== $expectedAnswer) {
        $error = "Incorrect security answer. Please try again.";
        // regenerate captcha
        $_SESSION['captcha_a'] = rand(1, 10);
        $_SESSION['captcha_b'] = rand(1, 10);
    } elseif (empty($username) || empty($password)) {
        $error = "Please enter your username and password.";
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // password_verify checks bcrypt hash - never store plain text passwords!
        if ($user && password_verify($password, $user['password'])) {
            // login success - regenerate session id to prevent session fixation
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            // clear captcha from session
            unset($_SESSION['captcha_a'], $_SESSION['captcha_b']);
            setFlash('success', 'Welcome back, ' . $user['username'] . '!');
            header('Location: index.php');
            exit;
        } else {
            $error = "Incorrect username or password.";
            // regenerate captcha on failed attempt
            $_SESSION['captcha_a'] = rand(1, 10);
            $_SESSION['captcha_b'] = rand(1, 10);
        }
    }

    $oldUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $captchaA = $_SESSION['captcha_a'];
    $captchaB = $_SESSION['captcha_b'];
}

TwigHelper::render('login.html.twig', [
    'error'          => $error,
    'old_username'   => $oldUsername,
    'csrf_token'     => csrfToken(),
    'captcha_a'      => $captchaA,
    'captcha_b'      => $captchaB,
]);
?>
