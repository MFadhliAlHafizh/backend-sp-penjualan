<?php

$url = $_GET['url'] ?? '';
$segments = explode('/', $url);

// ambil prefix
$resource = $segments[0] ?? '';

switch ($resource) {
    case 'kriteria':
        require_once __DIR__ . '/routes/kriteriaRoute.php';
        break;

    case 'platform':
        require_once __DIR__ . '/routes/platformRoute.php';
        break;

    case 'register':
        require_once __DIR__ . '/routes/authenticationRoute.php';
        break;

    default:
        header("Content-Type: application/json");
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Route not found",
            "data" => null
        ]);
        break;
}
