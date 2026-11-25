<?php
// delete-game.php
// POST only - deletes a game. Must be logged in.
// sujan - 5CS045

session_start();
require_once 'db.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

checkCsrf();

$id = filterInt($_POST['id'] ?? 0);
if (!$id) {
    setFlash('error', 'Invalid game.');
    header('Location: index.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT title FROM games WHERE id = ?");
$stmt->execute([$id]);
$game = $stmt->fetch();

if (!$game) {
    setFlash('error', 'Game not found.');
    header('Location: index.php');
    exit;
}

$del = $db->prepare("DELETE FROM games WHERE id = ?");
$del->execute([$id]);

setFlash('success', '"' . $game['title'] . '" was deleted.');
header('Location: index.php');
exit;
?>
