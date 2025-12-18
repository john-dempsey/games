<?php
require_once __DIR__ . '/../../etc/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    JsonResponse::error("Invalid request method.", 405);
}

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

// Validation passed - create the game
try {
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

    // Create new game instance
    $game = new Game();
    $game->setTitle($data['title']);
    $game->setReleaseDate($data['release_date']);
    $game->setGenreId($data['genre_id']);
    $game->setDescription($data['description']);

    // Save to database
    if ($game->save()) {
        // Save platform associations
        if (!empty($data['platform_ids']) && is_array($data['platform_ids'])) {
            foreach ($data['platform_ids'] as $platformId) {
                GamePlatform::create($game->getGameId(), $platformId);
            }
        }
        // Success
        JsonResponse::success($game->toArray(), 'Game created successfully', 201);
    }
    else {
        // Save failed
        JsonResponse::error('Failed to create game', 500);
    }

} 
catch (PDOException $e) {
    // Database error
    JsonResponse::error('Database error: ' . $e->getMessage(), 500);
}
