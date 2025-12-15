<?php
require_once __DIR__ . '/../etc/config.php';
require_once __DIR__ . '/../etc/utils.php';

// Retrieve platform by ID
$platform = null;
$games = [];
$error = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $platform = Platform::findById($id);

        if (!$platform) {
            $error = "Platform not found with ID: " . h($id);
        }
        else {
            // Get all games on this platform
            $games = $platform->getGames();
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
} else {
    $error = "No platform ID provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/games.css">
    <title><?php echo $platform ? h($platform->getName()) : 'Platform Details'; ?></title>
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
                <h1><?php echo h($platform->getName()); ?></h1>

                <div class="game-info">
                    <div class="info-item">
                        <span class="info-label">Platform ID:</span>
                        <span class="info-value"><?php echo h($platform->getPlatformId()); ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Manufacturer:</span>
                        <span class="info-value"><?php echo h($platform->getManufacturer()); ?></span>
                    </div>
                </div>

                <div class="game-description">
                    <h2>Games Available on this Platform</h2>
                    <?php if (empty($games)): ?>
                        <p>No games found for this platform.</p>
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
