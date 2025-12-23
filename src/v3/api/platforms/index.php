<?php
require_once __DIR__ . '/../../../etc/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    JsonResponse::error("Invalid request method.", 405);
}
try {
    // Create connection
    $db = DB::getInstance();
    $conn = $db->getConnection();

    $platfroms = Platform::findAll();
    $data = [];

    foreach ($platfroms as $platfrom) {
        $platfromData = $platfrom->toArray();
        $data[] = $platfromData;
    }
    http_response_code(200);
    JsonResponse::success($data);
}
catch (PDOException $e) {
    JsonResponse::error('Database error: ' . $e->getMessage(), 500);
}
?>