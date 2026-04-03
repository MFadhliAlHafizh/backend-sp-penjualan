<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// handle preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/KriteriaModel.php';

// koneksi DB
$database = new Database();
$db = $database->connect();

if (!$db) {
    http_response_code(500);
    response("error", null, "Database connection failed");
    exit();
}

$kriteria = new Kriteria($db);

// ambil method & endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request = $_GET['url'] ?? '';

header("Content-Type: application/json");

// parsing input JSON
$input = json_decode(file_get_contents("php://input"), true);

function response($status, $data = null, $message = "") {
    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data
    ]);
}

// ROUTING
switch ($request) {

    case 'kriteria':
        if ($method === 'GET') {

            $result = $kriteria->getAll();

            if ($result !== false) {
                http_response_code(200);
                response("success", $result, "Data retrieved successfully");
                exit();
            } else {
                http_response_code(500);
                response("error", null, "Failed to retrieve data");
                exit();
            }
        }
        break;

    default:
        http_response_code(404);
        response("error", null, "Route not found");
        break;
}