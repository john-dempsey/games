<?php

class Platform {
    private $platform_id;
    private $name;
    private $manufacturer;

    private $db;

    public function __construct($data = []) {
        $this->db = DB::getInstance()->getConnection();

        if (!empty($data)) {
            $this->platform_id = $data['platform_id'] ?? null;
            $this->name = $data['name'] ?? null;
            $this->manufacturer = $data['manufacturer'] ?? null;
        }
    }

    // Getters
    public function getPlatformId() {
        return $this->platform_id;
    }

    public function getName() {
        return $this->name;
    }

    public function getManufacturer() {
        return $this->manufacturer;
    }

    public function getGames() {
        return Game::findByPlatform($this->platform_id);
    }

    // Setters
    public function setName($name) {
        $this->name = $name;
    }

    public function setManufacturer($manufacturer) {
        $this->manufacturer = $manufacturer;
    }

    // Find all platforms
    public static function findAll() {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM Platforms ORDER BY name");
        $stmt->execute();

        $platforms = [];
        while ($row = $stmt->fetch()) {
            $platforms[] = new Platform($row);
        }

        return $platforms;
    }

    // Find platform by ID
    public static function findById($id) {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM Platforms WHERE platform_id = :id");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        if ($row) {
            return new Platform($row);
        }

        return null;
    }

    // Find platforms by game (requires JOIN with GamePlatforms table)
    public static function findByGame($gameId) {
        $db = DB::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT p.*
            FROM Platforms p
            INNER JOIN GamePlatforms gp ON p.platform_id = gp.platform_id
            WHERE gp.game_id = :game_id
            ORDER BY p.name
        ");
        $stmt->execute(['game_id' => $gameId]);

        $platforms = [];
        while ($row = $stmt->fetch()) {
            $platforms[] = new Platform($row);
        }

        return $platforms;
    }

    // Save (insert or update)
    public function save() {
        if ($this->platform_id) {
            // Update existing record
            $stmt = $this->db->prepare("
                UPDATE Platforms
                SET name = :name,
                    manufacturer = :manufacturer
                WHERE platform_id = :platform_id
            ");

            return $stmt->execute([
                'name' => $this->name,
                'manufacturer' => $this->manufacturer,
                'platform_id' => $this->platform_id
            ]);
        } 
        else {
            // Insert new record
            $stmt = $this->db->prepare("
                INSERT INTO Platforms (name, manufacturer)
                VALUES (:name, :manufacturer)
            ");

            $result = $stmt->execute([
                'name' => $this->name,
                'manufacturer' => $this->manufacturer
            ]);

            if ($result) {
                $this->platform_id = $this->db->lastInsertId();
            }

            return $result;
        }
    }

    // Delete
    public function delete() {
        if (!$this->platform_id) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM Platforms WHERE platform_id = :platform_id");
        return $stmt->execute(['platform_id' => $this->platform_id]);
    }

    // Convert to array for JSON output
    public function toArray() {
        return [
            'platform_id' => $this->platform_id,
            'name' => $this->name,
            'manufacturer' => $this->manufacturer
        ];
    }
}
