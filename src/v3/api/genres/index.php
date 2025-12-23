<?php
require_once __DIR__ . '/../../../etc/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    JsonResponse::error("Invalid request method.", 405);
}
try {
    // Create connection
    $db = DB::getInstance();
    $conn = $db->getConnection();

    $genres = Genre::findAll();
    $data = [];

    foreach ($genres as $genre) {
        $genreData = $genre->toArray();
        $data[] = $genreData;
    }
    http_response_code(200);
    JsonResponse::success($data);
}
catch (PDOException $e) {
    JsonResponse::error('Database error: ' . $e->getMessage(), 500);
}
?>