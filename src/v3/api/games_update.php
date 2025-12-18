<?php
require_once __DIR__ . '/../../etc/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    JsonResponse::error("Invalid request method.", 405);
}

if (!isset($_REQUEST['id'])) {
    JsonResponse::error("No game ID provided.", 400);
}

if (!is_numeric($_REQUEST['id'])) {
    JsonResponse::error("Invalid game ID.", 400);
}

$id = $_REQUEST['id'];

// Get form data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    JsonResponse::error('Invalid JSON', 400);
}

// Define validation rules
$rules = [
    'title' => 'required|notempty|min:1|max:255',
    'release_date' => 'required|notempty',
    'genre_id' => 'required|integer',
    'description' => 'required|notempty|min:10|max:5000',
    'platform_ids' => 'required|array|min:1|max:10'
];

// Validate data
$validator = new Validator($data, $rules);

if ($validator->fails()) {
    $errors = $validator->errors();
    JsonResponse::error('Validation failed', 422, $errors);
}

// Validation passed - update the game
try {
    // Retrieve the game
    $game = Game::findById($id);

    if (!$game) {
        JsonResponse::error("Game not found.", 404);
    }

    // Verify genre exists
    $genre = Genre::findById($data['genre_id']);
    if (!$genre) {
        JsonResponse::error('Genre not found', 422);
    }

    // Verify platforms exist
    foreach ($data['platform_ids'] as $platformId) {
        if (!Platform::findById($platformId)) {
            JsonResponse::error("Platform with ID $platformId not found", 422);
        }
    }

    // Update game properties
    $game->setTitle($data['title']);
    $game->setReleaseDate($data['release_date']);
    $game->setGenreId($data['genre_id']);
    $game->setDescription($data['description']);

    // Save to database
    if ($game->save()) {
        // Sync platform associations
        // Get current platforms for this game
        $currentPlatforms = Platform::findByGame($id);
        $currentPlatformIds = [];
        foreach ($currentPlatforms as $platform) {
            $currentPlatformIds[] = $platform->getPlatformId();
        }
        $submittedPlatformIds = is_array($data['platform_ids']) ? $data['platform_ids'] : [];

        // Remove platforms that are no longer selected
        foreach ($currentPlatformIds as $currentId) {
            if (!in_array($currentId, $submittedPlatformIds)) {
                GamePlatform::remove($id, $currentId);
            }
        }

        // Add newly selected platforms
        foreach ($submittedPlatformIds as $platformId) {
            if (!in_array($platformId, $currentPlatformIds)) {
                GamePlatform::create($id, $platformId);
            }
        }

        // Success
        JsonResponse::success($game->toArray(), 'Game updated successfully', 200);
    }
    else {
        // Save failed
        JsonResponse::error('Failed to update game', 500);
    }

} 
catch (PDOException $e) {
    // Database error
    JsonResponse::error('Database error: ' . $e->getMessage(), 500);
}
