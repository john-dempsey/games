<?php

class GenreController extends BaseController {
    public function index() {
        try {
            $genres = Genre::findAll();
            $result = [];

            foreach ($genres as $genre) {
                $result[] = $genre->toArray();
            }

            $this->sendSuccess($result);
        }
        catch (PDOException $e) {
            $this->sendError('Database error', 'DB_ERROR', 500);
        }
    }
}
