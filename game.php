<?php
// game.php - single game detail page
// Sujan - 5CS045

session_start();
require_once 'db.php';
require_once 'TwigHelper.php';

$id = filterInt($_GET['id'] ?? 0);
if (!$id) {
    setFlash('error', 'Invalid game ID.');
    header('Location: index.php');
    exit;
}

$db   = getDB();
$stmt = $db->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$id]);
$game = $stmt->fetch();

if (!$game) {
    setFlash('error', 'Game not found.');
    header('Location: index.php');
    exit;
}

TwigHelper::render('game.html.twig', [
    'game'       => $game,
    'csrf_token' => csrfToken(),
]);
?>
