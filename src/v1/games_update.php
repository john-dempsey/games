<?php
require_once __DIR__ . '/../etc/config.php';
require_once __DIR__ . '/../etc/utils.php';

startSession();

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/v1/');
}

// Get form data
$data = [
    'game_id' => $_POST['game_id'] ?? null,
    'title' => $_POST['title'] ?? null,
    'release_date' => $_POST['release_date'] ?? null,
    'genre_id' => $_POST['genre_id'] ?? null,
    'description' => $_POST['description'] ?? null,
    'platform_ids' => $_POST['platform_ids'] ?? []
];

// Define validation rules
$rules = [
    'game_id' => 'required|integer',
    'title' => 'required|notempty|min:1|max:255',
    'release_date' => 'required|notempty',
    'genre_id' => 'required|integer',
    'description' => 'required|notempty|min:10|max:5000',
    'platform_ids' => 'required|array|min:1|max:10'
];

// Validate data
$validator = new Validator($data, $rules);

if ($validator->fails()) {
    // Store form data and errors in session
    $_SESSION['form-data'] = $data;
    $_SESSION['form-errors'] = [];

    // Get first error for each field
    foreach ($validator->errors() as $field => $errors) {
        $_SESSION['form-errors'][$field] = $errors[0];
    }

    // Redirect back to edit page
    redirect('games_edit.php?id=' . $data['game_id']);
}

// Validation passed - update the game
try {
    // Retrieve the game
    $game = Game::findById($data['game_id']);

    if (!$game) {
        $_SESSION['form-data'] = $data;
        $_SESSION['form-errors'] = ['game_id' => 'Game not found.'];
        redirect('games_edit.php?id=' . $data['game_id']);
    }

    // Verify genre exists
    $genre = Genre::findById($data['genre_id']);
    if (!$genre) {
        $_SESSION['form-data'] = $data;
        $_SESSION['form-errors'] = ['genre_id' => 'Selected genre does not exist.'];
        redirect('games_edit.php?id=' . $data['game_id']);
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
        $currentPlatforms = Platform::findByGame($data['game_id']);
        $currentPlatformIds = array_map(function($p) { return $p->getPlatformId(); }, $currentPlatforms);
        $submittedPlatformIds = is_array($data['platform_ids']) ? $data['platform_ids'] : [];

        // Remove platforms that are no longer selected
        foreach ($currentPlatformIds as $currentId) {
            if (!in_array($currentId, $submittedPlatformIds)) {
                GamePlatform::remove($data['game_id'], $currentId);
            }
        }

        // Add newly selected platforms
        foreach ($submittedPlatformIds as $platformId) {
            if (!in_array($platformId, $currentPlatformIds)) {
                // Verify platform exists before creating relationship
                if (Platform::findById($platformId)) {
                    GamePlatform::create($data['game_id'], $platformId);
                }
            }
        }

        // Clear any old form data
        clearFormData();

        // Redirect to game details page
        redirect('games_show.php?id=' . $data['game_id']);
    }
    else {
        // Save failed
        $_SESSION['form-data'] = $data;
        $_SESSION['form-errors'] = ['general' => 'Failed to update game. Please try again.'];
        redirect('games_edit.php?id=' . $data['game_id']);
    }

} 
catch (PDOException $e) {
    // Database error
    $_SESSION['form-data'] = $data;
    $_SESSION['form-errors'] = ['general' => 'Database error: ' . $e->getMessage()];
    redirect('games_edit.php?id=' . $data['game_id']);
}
