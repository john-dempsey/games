<?php
require_once __DIR__ . '/../etc/config.php';
require_once __DIR__ . '/../etc/utils.php';

startSession();

try {
    // Create connection
    $db = DB::getInstance();
    $conn = $db->getConnection();

    $games = Game::findAll();
}
catch (PDOException $e) {
    die("<p>PDO Exception: " . $e->getMessage() . "</p>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/games.css">
    <title>Game Shop</title>
</head>
<body>
    <div class="page-header">
        <h1>Game Shop</h1>
        <a href="games_create.php" class="create-btn">Create New Game</a>
    </div>

    <?php include __DIR__ . '/flash_message.php'; ?>

    <?php if (count($games) === 0): ?>
        <p>No games available.</p>
    <?php else: ?>
        <div class="game-list">
            <?php foreach ($games as $game): ?>
                <div class="game-item">
                    <h2><?php echo h($game->getTitle()); ?></h2>
                    <p>Release Date: <?php echo h($game->getReleaseDate()); ?></p>
                    <p>Genre: <?php echo h($game->getGenre()->getName()); ?></p>
                    <p>Description: <?php echo nl2br(h($game->getDescription())); ?></p>
                    <a href="games_show.php?id=<?php echo h($game->getGameId()); ?>" class="view-details-btn">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>