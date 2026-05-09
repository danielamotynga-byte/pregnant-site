<?php
/**
 * AJAX: Получить одно шоу по ID или slug
 * GET /ajax/get-show.php?id=1 или GET /ajax/get-show.php?slug=pregnant16
 */

header('Content-Type: application/json; charset=utf-8');

$id = $_GET['id'] ?? null;
$slug = $_GET['slug'] ?? null;

if (!$id && !$slug) {
    http_response_code(400);
    echo json_encode(['error' => 'ID or slug required']);
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
    
    $show = null;
    
    foreach ($xml->show as $item) {
        if (($id && (int)$item->id === (int)$id) || ($slug && (string)$item->slug === $slug)) {
            $show = [
                'id' => (int)$item->id,
                'slug' => (string)$item->slug,
                'title' => (string)$item->title,
                'rating' => (float)$item->rating,
                'image' => (string)$item->image,
                'description' => (string)$item->description,
                'link' => (string)$item->link,
                'channel' => (string)$item->channel,
                'seasons' => (string)$item->seasons,
                'episodes' => (string)$item->episodes
            ];
            break;
        }
    }
    
    if ($show) {
        echo json_encode([
            'success' => true,
            'show' => $show
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Show not found'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
