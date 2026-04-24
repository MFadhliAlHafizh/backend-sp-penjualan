<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/vendor/autoload.php';

$url = $_GET['url'] ?? '';
$segments = explode('/', $url);

// ambil prefix
$resource = $segments[0] ?? '';

switch ($resource) {
    case 'kriteria':
        require_once __DIR__ . '/routes/kriteriaRoute.php';
        break;

    case 'penyebab':
        require_once __DIR__ . '/routes/penyebabRoute.php';
        break;

    case 'register':
    case 'login':
        require_once __DIR__ . '/routes/authenticationRoute.php';
        break;

    case 'akun':
        require_once __DIR__ . '/routes/akunRoute.php';
        break;

    case 'rules':
        if (($segments[1] ?? '') === 'detail') {
            require_once __DIR__ . '/routes/rulesDetailRoute.php';
        } else {
            require_once __DIR__ . '/routes/rulesRoute.php';
        }
        break;

    case 'riwayat':
        require_once __DIR__ . '/routes/riwayatRoute.php';
        break;

    case 'konsultasi':
        require_once __DIR__ . '/routes/konsultasiRoute.php';
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
