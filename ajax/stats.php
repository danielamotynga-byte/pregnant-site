<?php
/**
 * AJAX: Статистика по шоу
 * GET /ajax/stats.php
 */

header('Content-Type: application/json; charset=utf-8');

$xmlFile = __DIR__ . '/../db/shows.xml';

if (!file_exists($xmlFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'XML file not found']);
    exit;
}

try {
    $xml = simplexml_load_file($xmlFile);
    
    if ($xml === false) {
        throw new Exception('Failed to parse XML');
    }
    
    $total = 0;
    $ratingSum = 0;
    $totalEpisodes = 0;
    
    foreach ($xml->show as $show) {
        $total++;
        $ratingSum += (float)$show->rating;
        
        // Попытка извлечь число из episodes (например "300+")
        $episodes = (string)$show->episodes;
        $episodesNum = (int)filter_var($episodes, FILTER_SANITIZE_NUMBER_INT);
        $totalEpisodes += $episodesNum;
    }
    
    $avgRating = $total > 0 ? round($ratingSum / $total, 1) : 0;
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_shows' => $total,
            'average_rating' => $avgRating,
            'total_episodes_approx' => $totalEpisodes,
            'rating_sum' => $ratingSum
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
