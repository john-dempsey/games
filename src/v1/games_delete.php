<?php
require_once __DIR__ . '/../etc/config.php';
require_once __DIR__ . '/../etc/utils.php';

startSession();

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/v1/');
}

// Get game ID
$gameId = $_POST['game_id'] ?? null;
if (!$gameId) {
    redirect('/v1/');
}

// Delete the game
try {
    // Retrieve the game
    $game = Game::findById($gameId);
    if (!$game) {
        setFlash('error', 'Game not found.');
        redirect('/v1/');
    }

    // Delete the game
    if ($game->delete()) {
        setFlash('success', 'Game deleted successfully.');
        redirect('/v1/');
    }
    else {
        setFlash('error', 'Failed to delete the game.');
        redirect('/v1/');
    }
} 
catch (PDOException $e) {
    setFlash('error', 'Database error: ' . $e->getMessage());
    redirect('/v1/');
}
