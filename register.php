<?php
// register.php
// New user registration with encrypted passwords
// Sujan - 5CS045

session_start();
require_once 'db.php';
require_once 'TwigHelper.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = null;
$oldUsername = '';
$oldEmail = '';

// generate captcha
if (empty($_SESSION['captcha_a'])) {
    $_SESSION['captcha_a'] = rand(1, 10);
    $_SESSION['captcha_b'] = rand(1, 10);
}
$captchaA = $_SESSION['captcha_a'];
$captchaB = $_SESSION['captcha_b'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $userCaptcha = (int)($_POST['captcha'] ?? -99);

    // validate captcha - compare against the session-stored numbers, never trust client input
    $expectedAnswer = (int)($_SESSION['captcha_a'] ?? -1) + (int)($_SESSION['captcha_b'] ?? -1);
    if ($userCaptcha !== $expectedAnswer) {
        $error = "Incorrect security answer.";
        $_SESSION['captcha_a'] = rand(1, 10);
        $_SESSION['captcha_b'] = rand(1, 10);
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = "Username must be 3-50 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Username can only contain letters, numbers and underscores.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $password2) {
        $error = "Passwords do not match.";
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $db = getDB();

        // check username not taken
        $check = $db->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            $error = "That username is already taken.";
        } else {
            // hash the password with bcrypt - PASSWORD_DEFAULT uses bcrypt
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashedPassword, $email ?: null]);

            // log them in straight away
            session_regenerate_id(true);
            $_SESSION['user_id']  = $db->lastInsertId();
            $_SESSION['username'] = $username;
            unset($_SESSION['captcha_a'], $_SESSION['captcha_b']);

            setFlash('success', 'Account created! Welcome, ' . $username . '!');
            header('Location: index.php');
            exit;
        }
    }

    $oldUsername = e($username);
    $oldEmail    = e($email);
    $captchaA = $_SESSION['captcha_a'];
    $captchaB = $_SESSION['captcha_b'];
}

TwigHelper::render('register.html.twig', [
    'error'          => $error,
    'old_username'   => $oldUsername,
    'old_email'      => $oldEmail,
    'csrf_token'     => csrfToken(),
    'captcha_a'      => $captchaA,
    'captcha_b'      => $captchaB,
]);
?>
