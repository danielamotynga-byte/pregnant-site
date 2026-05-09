<?php
/**
 * AJAX: Поиск шоу по названию
 * GET /ajax/search.php?q=беременна
 */

header('Content-Type: application/json; charset=utf-8');

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([
        'success' => true,
        'results' => [],
        'message' => 'Query too short'
    ]);
    exit;
}

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
    
    $results = [];
    $query_lower = mb_strtolower($query, 'UTF-8');
    
    foreach ($xml->show as $show) {
        $title = (string)$show->title;
        if (mb_stripos($title, $query, 0, 'UTF-8') !== false) {
            $results[] = [
                'id' => (int)$show->id,
                'slug' => (string)$show->slug,
                'title' => $title,
                'rating' => (float)$show->rating,
                'image' => (string)$show->image,
                'link' => (string)$show->link
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($results),
        'results' => $results
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
