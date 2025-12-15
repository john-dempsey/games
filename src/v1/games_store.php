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
    'title' => $_POST['title'] ?? null,
    'release_date' => $_POST['release_date'] ?? null,
    'genre_id' => $_POST['genre_id'] ?? null,
    'description' => $_POST['description'] ?? null,
    'platform_ids' => $_POST['platform_ids'] ?? []
];

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
    // Store form data and errors in session
    $_SESSION['form-data'] = $data;
    $_SESSION['form-errors'] = [];

    // Get first error for each field
    foreach ($validator->errors() as $field => $errors) {
        $_SESSION['form-errors'][$field] = $errors[0];
    }

    // Redirect back to create page
    redirect('games_create.php');
}

// Validation passed - create the game
try {
    // Verify genre exists
    $genre = Genre::findById($data['genre_id']);
    if (!$genre) {
        $_SESSION['form-data'] = $data;
        $_SESSION['form-errors'] = ['genre_id' => 'Selected genre does not exist.'];
        redirect('games_create.php');
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
                // Verify platform exists before creating relationship
                if (Platform::findById($platformId)) {
                    GamePlatform::create($game->getGameId(), $platformId);
                }
            }
        }

        // Clear any old form data
        clearFormData();

        // Redirect to game details page
        redirect('games_show.php?id=' . $game->getGameId());
    }
    else {
        // Save failed
        $_SESSION['form-data'] = $data;
        $_SESSION['form-errors'] = ['general' => 'Failed to create game. Please try again.'];
        redirect('games_create.php');
    }

} 
catch (PDOException $e) {
    // Database error
    $_SESSION['form-data'] = $data;
    $_SESSION['form-errors'] = ['general' => 'Database error: ' . $e->getMessage()];
    redirect('games_create.php');
}
