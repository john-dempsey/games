<?php

class GameController extends BaseController {
    public function index() {
        try {
            $games = Game::findAll();
            $result = [];

            foreach ($games as $game) {
                $gameData = $game->toArray();
                $gameData['genre'] = $game->getGenre() ? $game->getGenre()->toArray() : null;
                $gameData['platforms'] = array_map(function($p) {
                    return $p->toArray();
                }, $game->getPlatforms());

                $result[] = $gameData;
            }

            $this->sendSuccess($result);
        }
        catch (PDOException $e) {
            $this->sendError('Database error', 'DB_ERROR', 500);
        }
    }

    public function show($id) {
        try {
            $game = Game::findById($id);

            if (!$game) {
                $this->sendError('Game not found', 'GAME_NOT_FOUND', 404);
                return;
            }

            $gameData = $game->toArray();
            $gameData['genre'] = $game->getGenre() ? $game->getGenre()->toArray() : null;
            $gameData['platforms'] = array_map(function($p) {
                return $p->toArray();
            }, $game->getPlatforms());

            $this->sendSuccess($gameData);
        }
        catch (PDOException $e) {
            $this->sendError('Database error', 'DB_ERROR', 500);
        }
    }

    public function store() {
        $data = $this->getJsonInput();

        // Validate
        $rules = [
            'title' => 'required|notempty|min:1|max:255',
            'release_date' => 'required|notempty',
            'genre_id' => 'required|integer',
            'description' => 'required|notempty|min:10|max:5000',
            'platform_ids' => 'required|array|min:1|max:10|integer'
        ];

        $this->validate($data, $rules);

        try {
            // Verify genre exists
            $genre = Genre::findById($data['genre_id']);
            if (!$genre) {
                $this->sendError('Genre not found', 'GENRE_NOT_FOUND', 404);
                return;
            }

            // Create game
            $game = new Game();
            $game->setTitle($data['title']);
            $game->setReleaseDate($data['release_date']);
            $game->setGenreId($data['genre_id']);
            $game->setDescription($data['description']);

            if ($game->save()) {
                // Save platforms
                foreach ($data['platform_ids'] as $platformId) {
                    if (Platform::findById($platformId)) {
                        GamePlatform::create($game->getGameId(), $platformId);
                    }
                }

                // Return created game with relations
                $gameData = $game->toArray();
                $gameData['genre'] = $game->getGenre()->toArray();
                $gameData['platforms'] = array_map(function($p) {
                    return $p->toArray();
                }, $game->getPlatforms());

                $this->sendSuccess($gameData, 'Game created successfully', 201);
            }
            else {
                $this->sendError('Failed to create game', 'CREATE_FAILED', 500);
            }
        }
        catch (PDOException $e) {
            $this->sendError('Database error: ' . $e->getMessage(), 'DB_ERROR', 500);
        }
    }

    public function update($id) {
        $data = $this->getJsonInput();

        // Validate
        $rules = [
            'title' => 'required|notempty|min:1|max:255',
            'release_date' => 'required|notempty',
            'genre_id' => 'required|integer',
            'description' => 'required|notempty|min:10|max:5000',
            'platform_ids' => 'required|array|min:1|max:10|integer'
        ];

        $this->validate($data, $rules);

        try {
            // Retrieve the game
            $game = Game::findById($id);

            if (!$game) {
                $this->sendError('Game not found', 'GAME_NOT_FOUND', 404);
                return;
            }

            // Verify genre exists
            $genre = Genre::findById($data['genre_id']);
            if (!$genre) {
                $this->sendError('Genre not found', 'GENRE_NOT_FOUND', 404);
                return;
            }

            // Update game properties
            $game->setTitle($data['title']);
            $game->setReleaseDate($data['release_date']);
            $game->setGenreId($data['genre_id']);
            $game->setDescription($data['description']);

            if ($game->save()) {
                // Sync platform associations
                // Get current platforms for this game
                $currentPlatforms = Platform::findByGame($id);
                $currentPlatformIds = array_map(function($p) {
                    return $p->getPlatformId();
                }, $currentPlatforms);

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
                        // Verify platform exists before creating relationship
                        if (Platform::findById($platformId)) {
                            GamePlatform::create($id, $platformId);
                        }
                    }
                }

                // Return updated game with relations
                $gameData = $game->toArray();
                $gameData['genre'] = $game->getGenre()->toArray();
                $gameData['platforms'] = array_map(function($p) {
                    return $p->toArray();
                }, $game->getPlatforms());

                $this->sendSuccess($gameData, 'Game updated successfully');
            }
            else {
                $this->sendError('Failed to update game', 'UPDATE_FAILED', 500);
            }
        }
        catch (PDOException $e) {
            $this->sendError('Database error: ' . $e->getMessage(), 'DB_ERROR', 500);
        }
    }

    public function destroy($id) {
        try {
            $game = Game::findById($id);

            if (!$game) {
                $this->sendError('Game not found', 'GAME_NOT_FOUND', 404);
                return;
            }

            if ($game->delete()) {
                $this->sendSuccess(null, 'Game deleted successfully');
            }
            else {
                $this->sendError('Failed to delete game', 'DELETE_FAILED', 500);
            }
        }
        catch (PDOException $e) {
            $this->sendError('Database error', 'DB_ERROR', 500);
        }
    }
}
