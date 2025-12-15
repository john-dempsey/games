<?php

class Game {
    private $game_id;
    private $title;
    private $release_date;
    private $genre_id;
    private $description;

    private $db;

    public function __construct($data = []) {
        $this->db = DB::getInstance()->getConnection();

        if (!empty($data)) {
            $this->game_id = $data['game_id'] ?? null;
            $this->title = $data['title'] ?? null;
            $this->release_date = $data['release_date'] ?? null;
            $this->genre_id = $data['genre_id'] ?? null;
            $this->description = $data['description'] ?? null;
        }
    }

    // Getters
    public function getGameId() {
        return $this->game_id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getReleaseDate() {
        return $this->release_date;
    }

    public function getGenreId() {
        return $this->genre_id;
    }

    public function getGenre() {
        return Genre::findById($this->genre_id);
    }

    public function getPlatforms() {
        return Platform::findByGame($this->game_id);
    }

    // Setters
    public function setTitle($title) {
        $this->title = $title;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setReleaseDate($release_date) {
        $this->release_date = $release_date;
    }

    public function setGenreId($genre_id) {
        $this->genre_id = $genre_id;
    }

    // Find all games
    public static function findAll() {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM Games ORDER BY title");
        $stmt->execute();

        $games = [];
        while ($row = $stmt->fetch()) {
            $games[] = new Game($row);
        }

        return $games;
    }

    // Find game by ID
    public static function findById($id) {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM Games WHERE game_id = :id");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        if ($row) {
            return new Game($row);
        }

        return null;
    }

    // Find games by genre
    public static function findByGenre($genreId) {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM Games WHERE genre_id = :genre_id ORDER BY title");
        $stmt->execute(['genre_id' => $genreId]);

        $games = [];
        while ($row = $stmt->fetch()) {
            $games[] = new Game($row);
        }

        return $games;
    }

    // Find games by platform (requires JOIN with GamePlatforms table)
    public static function findByPlatform($platformId) {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT g.*
            FROM Games g
            INNER JOIN GamePlatforms gp ON g.game_id = gp.game_id
            WHERE gp.platform_id = :platform_id
            ORDER BY g.title
        ");
        $stmt->execute(['platform_id' => $platformId]);

        $games = [];
        while ($row = $stmt->fetch()) {
            $games[] = new Game($row);
        }

        return $games;
    }

    // Save (insert or update)
    public function save() {
        if ($this->game_id) {
            // Update existing record
            $stmt = $this->db->prepare("
                UPDATE Games
                SET title = :title,
                    release_date = :release_date,
                    genre_id = :genre_id,
                    description = :description
                WHERE game_id = :game_id
            ");

            return $stmt->execute([
                'title' => $this->title,
                'release_date' => $this->release_date,
                'genre_id' => $this->genre_id,
                'description' => $this->description,
                'game_id' => $this->game_id
            ]);
        } else {
            // Insert new record
            $stmt = $this->db->prepare("
                INSERT INTO Games (title, release_date, genre_id, description)
                VALUES (:title, :release_date, :genre_id, :description)
            ");

            $result = $stmt->execute([
                'title' => $this->title,
                'release_date' => $this->release_date,
                'genre_id' => $this->genre_id,
                'description' => $this->description
            ]);

            if ($result) {
                $this->game_id = $this->db->lastInsertId();
            }

            return $result;
        }
    }

    // Delete
    public function delete() {
        if (!$this->game_id) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM Games WHERE game_id = :game_id");
        return $stmt->execute(['game_id' => $this->game_id]);
    }

    // Convert to array for JSON output
    public function toArray() {
        return [
            'game_id' => $this->game_id,
            'title' => $this->title,
            'release_date' => $this->release_date,
            'genre_id' => $this->genre_id,
            'description' => $this->description
        ];
    }
}
