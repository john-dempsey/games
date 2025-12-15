<?php
require_once __DIR__ . '/../etc/config.php';
require_once __DIR__ . '/../etc/utils.php';

// Retrieve genre by ID
$genre = null;
$games = [];
$error = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $genre = Genre::findById($id);

        if (!$genre) {
            $error = "Genre not found with ID: " . h($id);
        } 
        else {
            // Get all games in this genre
            $games = Game::findByGenre($id);
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
} else {
    $error = "No genre ID provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/games.css">
    <title><?php echo $genre ? h($genre->getName()) : 'Genre Details'; ?></title>
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
                <h1><?php echo h($genre->getName()); ?></h1>

                <div class="game-info">
                    <div class="info-item">
                        <span class="info-label">Genre ID:</span>
                        <span class="info-value"><?php echo h($genre->getGenreId()); ?></span>
                    </div>
                </div>

                <div class="game-description">
                    <h2>Description</h2>
                    <p><?php echo nl2br(h($genre->getDescription())); ?></p>
                </div>

                <div class="game-description">
                    <h2>Games in this Genre</h2>
                    <?php if (empty($games)): ?>
                        <p>No games found in this genre.</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($games as $game): ?>
                                <li>
                                    <a href="games_show.php?id=<?php echo h($game->getGameId()); ?>">
                                        <?php echo h($game->getTitle()); ?>
                                    </a>
                                    - <?php echo h($game->getReleaseDate()); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="game-actions">
                    <a href="/v1/" class="back-link">Back to Game List</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
