<?php
/**
 * AJAX: Получить все шоу
 * GET /ajax/get-shows.php
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
    
    $shows = [];
    
    foreach ($xml->show as $show) {
        $shows[] = [
            'id' => (int)$show->id,
            'slug' => (string)$show->slug,
            'title' => (string)$show->title,
            'rating' => (float)$show->rating,
            'image' => (string)$show->image,
            'description' => (string)$show->description,
            'link' => (string)$show->link,
            'channel' => (string)$show->channel,
            'seasons' => (string)$show->seasons,
            'episodes' => (string)$show->episodes
        ];
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($shows),
        'shows' => $shows
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
