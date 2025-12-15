<?php
require_once __DIR__ . '/../../etc/config.php';
require_once __DIR__ . '/responses/JsonResponse.php';
require_once __DIR__ . '/middleware/CorsMiddleware.php';
require_once __DIR__ . '/controllers/BaseController.php';
require_once __DIR__ . '/controllers/GameController.php';
require_once __DIR__ . '/controllers/GenreController.php';
require_once __DIR__ . '/controllers/PlatformController.php';

// Enable CORS
CorsMiddleware::handle();

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove /v2/api prefix from path
$path = preg_replace('#^/v2/api#', '', $path);

// Route matching
try {
    if ($path === '/games' && $method === 'GET') {
        $controller = new GameController();
        $controller->index();
    }
    elseif (preg_match('#^/games/(\d+)$#', $path, $matches) && $method === 'GET') {
        $controller = new GameController();
        $controller->show($matches[1]);
    }
    elseif ($path === '/games' && $method === 'POST') {
        $controller = new GameController();
        $controller->store();
    }
    elseif (preg_match('#^/games/(\d+)$#', $path, $matches) && $method === 'PUT') {
        $controller = new GameController();
        $controller->update($matches[1]);
    }
    elseif (preg_match('#^/games/(\d+)$#', $path, $matches) && $method === 'DELETE') {
        $controller = new GameController();
        $controller->destroy($matches[1]);
    }
    elseif ($path === '/genres' && $method === 'GET') {
        $controller = new GenreController();
        $controller->index();
    }
    elseif ($path === '/platforms' && $method === 'GET') {
        $controller = new PlatformController();
        $controller->index();
    }
    else {
        http_response_code(404);
        JsonResponse::error('Endpoint not found', 'NOT_FOUND', 404);
    }
}
catch (Exception $e) {
    http_response_code(500);
    JsonResponse::error('Internal server error: ' . $e->getMessage(), 'INTERNAL_ERROR', 500);
}
