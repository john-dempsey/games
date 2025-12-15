<?php
require_once __DIR__ . '/../etc/config.php';
require_once __DIR__ . '/../etc/utils.php';

// Retrieve game by ID
$game = null;
$platforms = [];
$error = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $game = Game::findById($id);

        if (!$game) {
            $error = "Game not found with ID: " . h($id);
        }
        else {
            // Get all platforms this game is available on
            $platforms = Platform::findByGame($id);
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
} else {
    $error = "No game ID provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/games.css">
    <title><?php echo $game ? h($game->getTitle()) : 'Game Details'; ?></title>
</head>
<body>
    <div class="game-details-container">
        <?php if ($error): ?>
            <div class="error-message">
                <h1>Error</h1>
                <p><?php echo $error; ?></p>
                <a href="/v1/" class="back-link">Back to Game List</a>
            </div>
        <?php else: ?>
            <div class="game-details">
                <h1><?php echo h($game->getTitle()); ?></h1>

                <div class="game-info">
                    <div class="info-item">
                        <span class="info-label">Game ID:</span>
                        <span class="info-value"><?php echo h($game->getGameId()); ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Release Date:</span>
                        <span class="info-value"><?php echo h($game->getReleaseDate()); ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Genre:</span>
                        <span class="info-value">
                            <a href="genres_show.php?id=<?php echo h($game->getGenreId()); ?>">
                                <?php echo h($game->getGenre()->getName()); ?>
                            </a>
                        </span>
                    </div>
                </div>

                <div class="game-description">
                    <h2>Description</h2>
                    <p><?php echo nl2br(h($game->getDescription())); ?></p>
                </div>

                <div class="game-description">
                    <h2>Available Platforms</h2>
                    <?php if (empty($platforms)): ?>
                        <p>No platforms found for this game.</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($platforms as $platform): ?>
                                <li>
                                    <a href="platforms_show.php?id=<?php echo h($platform->getPlatformId()); ?>">
                                        <?php echo h($platform->getName()); ?>
                                    </a>
                                    (<?php echo h($platform->getManufacturer()); ?>)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="game-actions">
                    <a href="games_edit.php?id=<?php echo h($game->getGameId()); ?>" class="edit-btn">Edit Game</a>
                    <form action="games_delete.php" method="POST" style="display: inline;">
                        <input type="hidden" name="game_id" value="<?php echo h($game->getGameId()); ?>">
                        <button type="submit" class="delete-btn">Delete Game</button>
                    </form>
                    <a href="/v1/" class="back-link">Back to Game List</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
