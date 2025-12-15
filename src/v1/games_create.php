<?php
require_once __DIR__ . '/../etc/config.php';
require_once __DIR__ . '/../etc/utils.php';

startSession();

// Retrieve all genres for the dropdown
$genres = [];
$platforms = [];
$error_message = null;

try {
    $genres = Genre::findAll();
    $platforms = Platform::findAll();
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
    <title>Create New Game</title>
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
                <h1>Create New Game</h1>

                <?php if (error('general')): ?>
                    <div class="alert alert-error">
                        <?php echo error('general'); ?>
                    </div>
                <?php endif; ?>

                <form action="games_store.php" method="POST">
                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="<?php echo h(old('title')); ?>"
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
                            value="<?php echo h(old('release_date')); ?>"
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
                                    <?php echo chosen('genre_id', $genre->getGenreId()) ? 'selected' : ''; ?>
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
                            <?php foreach ($platforms as $platform): ?>
                                <label class="checkbox-label">
                                    <input
                                        type="checkbox"
                                        name="platform_ids[]"
                                        value="<?php echo h($platform->getPlatformId()); ?>"
                                        <?php echo chosen('platform_ids', $platform->getPlatformId()) ? 'checked' : ''; ?>
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
                        ><?php echo h(old('description')); ?></textarea>
                        <?php if (error('description')): ?>
                            <span class="error-text"><?php echo error('description'); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Create Game</button>
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
