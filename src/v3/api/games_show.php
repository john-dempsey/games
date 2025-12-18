<?php
require_once __DIR__ . '/../../etc/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    JsonResponse::error("Invalid request method.", 405);
}

if (!isset($_GET['id'])) {
    JsonResponse::error("No game ID provided.", 400);
}

if (!is_numeric($_GET['id'])) {
    JsonResponse::error("Invalid game ID.", 400);
}

$id = $_GET['id'];

try {
    $game = Game::findById($id);

    if (!$game) {
        JsonResponse::error("Game not found.", 404);
    }

    $data = $game->toArray();
    $data['genre'] = $game->getGenre() ? $game->getGenre()->toArray() : null;
    $data['platforms'] = [];
    $platforms = $game->getPlatforms();
    foreach ($platforms as $platform) {
        $data['platforms'][] = $platform->toArray();
    }

    JsonResponse::success($data);
} 
catch (PDOException $e) {
    JsonResponse::error('Database error: ' . $e->getMessage(), 500);
}
?>
