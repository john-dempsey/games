<?php
require_once __DIR__ . '/../etc/config.php';
require_once __DIR__ . '/../etc/utils.php';

startSession();

// Retrieve game by ID
$game = null;
$error_message = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $game = Game::findById($id);

        if (!$game) {
            $error_message = "Game not found with ID: " . h($id);
        }
    } 
    catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
} 
else {
    $error_message = "No game ID provided.";
}

// Retrieve all genres and platforms for the dropdowns/checkboxes
$genres = [];
$platforms = [];
$gamePlatforms = [];

try {
    $genres = Genre::findAll();
    $platforms = Platform::findAll();

    // Get platforms for this game if game was loaded successfully
    if ($game) {
        $gamePlatforms = Platform::findByGame($game->getGameId());
    }
}
catch (PDOException $e) {
    $error_message = "Error loading data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/games.css">
    <title><?php echo $game ? 'Edit ' . h($game->getTitle()) : 'Edit Game'; ?></title>
</head>
<body>
    <div class="game-form-container">
        <?php if ($error_message): ?>
            <div class="error-message">
                <h1>Error</h1>
                <p><?php echo $error_message; ?></p>
                <a href="/v1/" class="back-link">Back to Game List</a>
            </div>
        <?php else: ?>
            <div class="game-form">
                <h1>Edit Game</h1>

                <?php if (error('general')): ?>
                    <div class="alert alert-error">
                        <?php echo error('general'); ?>
                    </div>
                <?php endif; ?>

                <form action="games_update.php" method="POST">
                    <input type="hidden" name="game_id" value="<?php echo h($game->getGameId()); ?>">

                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="<?php echo h(old('title', $game->getTitle())); ?>"
                        >
                        <?php if (error('title')): ?>
                            <span class="error-text"><?php echo error('title'); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="release_date">Release Date:</label>
                        <input
                            type="date"
                            id="release_date"
                            name="release_date"
                            value="<?php echo h(old('release_date', $game->getReleaseDate())); ?>"
                        >
                        <?php if (error('release_date')): ?>
                            <span class="error-text"><?php echo error('release_date'); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="genre_id">Genre:</label>
                        <select id="genre_id" name="genre_id">
                            <option value="">Select a genre</option>
                            <?php foreach ($genres as $genre): ?>
                                <option
                                    value="<?php echo h($genre->getGenreId()); ?>"
                                    <?php echo chosen('genre_id', $genre->getGenreId(), $game->getGenreId()) ? 'selected' : ''; ?>
                                >
                                    <?php echo h($genre->getName()); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (error('genre_id')): ?>
                            <span class="error-text"><?php echo error('genre_id'); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Platforms:</label>
                        <div class="checkbox-group">
                            <?php
                            // Create array of current platform IDs for easier checking
                            $currentPlatformIds = array_map(function($p) { return $p->getPlatformId(); }, $gamePlatforms);
                            ?>
                            <?php foreach ($platforms as $platform): ?>
                                <label class="checkbox-label">
                                    <input
                                        type="checkbox"
                                        name="platform_ids[]"
                                        value="<?php echo h($platform->getPlatformId()); ?>"
                                        <?php echo chosen('platform_ids', $platform->getPlatformId(), $currentPlatformIds) ? 'checked' : ''; ?>
                                    >
                                    <?php echo h($platform->getName()); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (error('platform_ids')): ?>
                            <span class="error-text"><?php echo error('platform_ids'); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="6"
                        ><?php echo h(old('description', $game->getDescription())); ?></textarea>
                        <?php if (error('description')): ?>
                            <span class="error-text"><?php echo error('description'); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Update Game</button>
                        <a href="/v1/" class="cancel-link">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
// Clear form data after displaying
clearFormData();
?>
