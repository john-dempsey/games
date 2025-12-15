<?php

class PlatformController extends BaseController {
    public function index() {
        try {
            $platforms = Platform::findAll();
            $result = [];

            foreach ($platforms as $platform) {
                $result[] = $platform->toArray();
            }

            $this->sendSuccess($result);
        }
        catch (PDOException $e) {
            $this->sendError('Database error', 'DB_ERROR', 500);
        }
    }
}
