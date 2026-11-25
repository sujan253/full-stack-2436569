<?php
// search.php - multi-criteria search
// Sujan - 5CS045

session_start();
require_once 'db.php';
require_once 'TwigHelper.php';

$db = getDB();

// get search params
$q         = trim($_GET['q'] ?? '');
$genre     = trim($_GET['genre'] ?? '');
$platform  = trim($_GET['platform'] ?? '');
$developer = trim($_GET['developer'] ?? '');
$yearFrom  = filter_var($_GET['year_from'] ?? '', FILTER_VALIDATE_INT);
$yearTo    = filter_var($_GET['year_to'] ?? '', FILTER_VALIDATE_INT);
$ratingMin = filter_var($_GET['rating_min'] ?? '', FILTER_VALIDATE_FLOAT);
$sort      = in_array($_GET['sort'] ?? '', ['title','release_year','rating']) ? $_GET['sort'] : 'release_year';
$dir       = ($_GET['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

// only run query if something was searched
$searched = ($q || $genre || $platform || $developer || $yearFrom || $yearTo || $ratingMin !== false);
$results  = [];

if ($searched) {
    $where  = [];
    $params = [];

    if ($q) {
        // search title, description, developer and publisher
        $where[]  = "(title LIKE ? OR description LIKE ? OR developer LIKE ? OR publisher LIKE ?)";
        $like     = "%$q%";
        $params   = array_merge($params, [$like, $like, $like, $like]);
    }
    if ($genre)     { $where[] = "genre = ?";       $params[] = $genre; }
    if ($platform)  { $where[] = "platform LIKE ?"; $params[] = "%$platform%"; }
    if ($developer) { $where[] = "developer LIKE ?"; $params[] = "%$developer%"; }
    if ($yearFrom)  { $where[] = "release_year >= ?"; $params[] = (int)$yearFrom; }
    if ($yearTo)    { $where[] = "release_year <= ?"; $params[] = (int)$yearTo; }
    if ($ratingMin !== false) { $where[] = "rating >= ?"; $params[] = (float)$ratingMin; }

    $sql = "SELECT * FROM games";
    if ($where) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY $sort $dir";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}

// genres for dropdown
$allGenres = $db->query("SELECT DISTINCT genre FROM games ORDER BY genre")->fetchAll(PDO::FETCH_COLUMN);

TwigHelper::render('search.html.twig', [
    'results'    => $results,
    'searched'   => $searched,
    'all_genres' => $allGenres,
    'q'          => e($q),
    'genre'      => e($genre),
    'platform'   => e($platform),
    'developer'  => e($developer),
    'year_from'  => $yearFrom ?: '',
    'year_to'    => $yearTo ?: '',
    'rating_min' => $ratingMin !== false ? $ratingMin : '',
    'sort'       => $sort,
    'dir'        => $dir,
]);
?>
