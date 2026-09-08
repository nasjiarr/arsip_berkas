<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$searchTerm = isset($_GET['term']) ? $_GET['term'] : '';

if (empty($searchTerm)) {
    echo json_encode(['results' => [], 'suggestions' => []]);
    exit;
}

// Fungsi untuk menghitung Levenshtein distance dengan batasan
function getLevenshteinDistance($str1, $str2, $threshold = 3)
{
    $distance = levenshtein(strtolower($str1), strtolower($str2));
    return $distance <= $threshold ? $distance : false;
}

// Query untuk pencarian utama (case insensitive)
$query = "SELECT * FROM sop_table WHERE LOWER(judul_sop) LIKE LOWER(?)";
$stmt = $pdo->prepare($query);
$stmt->execute(['%' . $searchTerm . '%']);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk suggestions
$query = "SELECT DISTINCT judul_sop FROM sop_table";
$stmt = $pdo->prepare($query);
$stmt->execute();
$allTitles = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Cari similar keywords
$suggestions = [];
foreach ($allTitles as $title) {
    $words = explode(' ', $title);
    foreach ($words as $word) {
        if (strlen($word) > 3) {
            if (getLevenshteinDistance($word, $searchTerm) !== false) {
                if (!in_array($title, $suggestions)) {
                    $suggestions[] = $title;
                }
            }
        }
    }
}

echo json_encode([
    'results' => $results,
    'suggestions' => array_slice($suggestions, 0, 5)
]);
