<?php

class Genre {
    private $genre_id;
    private $name;
    private $description;

    private $db;

    public function __construct($data = []) {
        $this->db = DB::getInstance()->getConnection();

        if (!empty($data)) {
            $this->genre_id = $data['genre_id'] ?? null;
            $this->name = $data['name'] ?? null;
            $this->description = $data['description'] ?? null;
        }
    }

    // Getters
    public function getGenreId() {
        return $this->genre_id;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getGames() {
        return Game::findByGenre($this->genre_id);
    }

    // Setters
    public function setName($name) {
        $this->name = $name;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    // Find all genres
    public static function findAll() {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM Genres ORDER BY name");
        $stmt->execute();

        $genres = [];
        while ($row = $stmt->fetch()) {
            $genres[] = new Genre($row);
        }

        return $genres;
    }

    // Find genre by ID
    public static function findById($id) {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM Genres WHERE genre_id = :id");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        if ($row) {
            return new Genre($row);
        }

        return null;
    }

    // Save (insert or update)
    public function save() {
        if ($this->genre_id) {
            // Update existing record
            $stmt = $this->db->prepare("
                UPDATE Genres
                SET name = :name,
                    description = :description
                WHERE genre_id = :genre_id
            ");

            return $stmt->execute([
                'name' => $this->name,
                'description' => $this->description,
                'genre_id' => $this->genre_id
            ]);
        } else {
            // Insert new record
            $stmt = $this->db->prepare("
                INSERT INTO Genres (name, description)
                VALUES (:name, :description)
            ");

            $result = $stmt->execute([
                'name' => $this->name,
                'description' => $this->description
            ]);

            if ($result) {
                $this->genre_id = $this->db->lastInsertId();
            }

            return $result;
        }
    }

    // Delete
    public function delete() {
        if (!$this->genre_id) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM Genres WHERE genre_id = :genre_id");
        return $stmt->execute(['genre_id' => $this->genre_id]);
    }

    // Convert to array for JSON output
    public function toArray() {
        return [
            'genre_id' => $this->genre_id,
            'name' => $this->name,
            'description' => $this->description
        ];
    }
}
