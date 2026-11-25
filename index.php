<?php
// index.php - homepage
// Shows all games with genre filter and pagination
// Sujan - 5CS045

session_start();
require_once 'db.php';
require_once 'TwigHelper.php';

$db = getDB();

// pagination
$perPage = 9;
$page = max(1, filterInt($_GET['page'] ?? 1) ?: 1);
$offset = ($page - 1) * $perPage;

// genre filter
$genre = trim($_GET['genre'] ?? '');

// build query
if ($genre) {
    $total = $db->prepare("SELECT COUNT(*) FROM games WHERE genre = ?");
    $total->execute([$genre]);
} else {
    $total = $db->query("SELECT COUNT(*) FROM games");
}
$totalGames = (int)$total->fetchColumn();
$totalPages = max(1, ceil($totalGames / $perPage));

if ($genre) {
    $stmt = $db->prepare("SELECT * FROM games WHERE genre = ? ORDER BY release_year DESC LIMIT ? OFFSET ?");
    $stmt->execute([$genre, $perPage, $offset]);
} else {
    $stmt = $db->prepare("SELECT * FROM games ORDER BY release_year DESC LIMIT ? OFFSET ?");
    $stmt->execute([$perPage, $offset]);
}
$games = $stmt->fetchAll();

// get all genres for filter tabs
$genreRows = $db->query("SELECT DISTINCT genre FROM games ORDER BY genre")->fetchAll(PDO::FETCH_COLUMN);

// total count for stats
$allCount = (int)$db->query("SELECT COUNT(*) FROM games")->fetchColumn();

TwigHelper::render('index.html.twig', [
    'games'        => $games,
    'genres'       => $genreRows,
    'active_genre' => $genre,
    'total_games'  => $allCount,
    'total_genres' => count($genreRows),
    'page'         => $page,
    'total_pages'  => $totalPages,
    'csrf_token'   => csrfToken(),
]);
?>
