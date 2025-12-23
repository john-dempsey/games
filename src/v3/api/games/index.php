<?php
require_once __DIR__ . '/../../../etc/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    JsonResponse::error("Invalid request method.", 405);
}
try {
    // Create connection
    $db = DB::getInstance();
    $conn = $db->getConnection();

    $games = Game::findAll();
    $data = [];

    foreach ($games as $game) {
        $gameData = $game->toArray();
        $gameData['genre'] = $game->getGenre() ? $game->getGenre()->getName() : null;
        $gameData['platforms'] = [];
        $gameData['platform_ids'] = [];
        $platforms = $game->getPlatforms();
        foreach ($platforms as $platform) {
            $gameData['platforms'][] = $platform->getName();
            $gameData['platform_ids'][] = $platform->getPlatformId();
        }

        $data[] = $gameData;
    }
    http_response_code(200);
    JsonResponse::success($data);
}
catch (PDOException $e) {
    JsonResponse::error('Database error: ' . $e->getMessage(), 500);
}
?>