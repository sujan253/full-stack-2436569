<?php
// api/autocomplete.php - AJAX suggestions endpoint
// Sujan - 5CS045

require_once '../db.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// only GET allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['suggestions' => []]);
    exit;
}

$type  = trim($_GET['type'] ?? '');
$query = trim($_GET['q'] ?? '');

// input filtering - whitelist allowed types
$allowed = ['title', 'genre', 'developer'];
if (!in_array($type, $allowed) || strlen($query) < 1 || strlen($query) > 100) {
    echo json_encode(['suggestions' => []]);
    exit;
}

try {
    $db   = getDB();
    $like = "%$query%";

    switch ($type) {
        case 'title':
            // return matching game titles with id so we can link to them
            $stmt = $db->prepare("SELECT id, title, genre, release_year FROM games WHERE title LIKE ? ORDER BY title LIMIT 8");
            $stmt->execute([$like]);
            $rows = $stmt->fetchAll();
            $suggestions = array_map(fn($r) => [
                'label' => $r['title'],
                'meta'  => $r['genre'] . ' · ' . $r['release_year'],
                'url'   => '../game.php?id=' . $r['id'],
            ], $rows);
            break;

        case 'genre':
            $stmt = $db->prepare("SELECT DISTINCT genre FROM games WHERE genre LIKE ? ORDER BY genre LIMIT 6");
            $stmt->execute([$like]);
            $suggestions = array_map(fn($r) => ['label' => $r['genre']], $stmt->fetchAll());
            break;

        case 'developer':
            $stmt = $db->prepare("SELECT DISTINCT developer FROM games WHERE developer LIKE ? ORDER BY developer LIMIT 6");
            $stmt->execute([$like]);
            $suggestions = array_map(fn($r) => ['label' => $r['developer']], $stmt->fetchAll());
            break;

        default:
            $suggestions = [];
    }

    echo json_encode(['suggestions' => $suggestions]);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['suggestions' => []]);
}
?>
