<?php
require_once __DIR__ . '/../../../etc/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    JsonResponse::error("Invalid request method.", 405);
}

if (!isset($_REQUEST['id'])) {
    JsonResponse::error("No game ID provided.", 400);
}

if (!is_numeric($_REQUEST['id'])) {
    JsonResponse::error("Invalid game ID.", 400);
}

$id = $_REQUEST['id'];

try {
    $game = Game::findById($id);

    if (!$game) {
        JsonResponse::error("Game not found.", 404);
    }

    if ($game->delete()) {
        JsonResponse::success(null, "Game deleted successfully.");
    }
    else {
        JsonResponse::error("Failed to delete the game.", 500);
    }
} 
catch (PDOException $e) {
    JsonResponse::error('Database error: ' . $e->getMessage(), 500);
}
?>