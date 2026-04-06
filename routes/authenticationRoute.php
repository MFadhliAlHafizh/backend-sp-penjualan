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
require_once __DIR__ . '/../models/AuthenticationModel.php';
require_once __DIR__ . '/../Validator.php';

// koneksi DB
$database = new Database();
$db = $database->connect();

if (!$db) {
    http_response_code(500);
    response("error", null, "Database connection failed");
    exit();
}

$register = new Authentication($db);
$validator = new Validator($db);

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

// ambil method & endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request = $_GET['url'] ?? '';
$segments = explode('/', trim($request, '/'));
$resource = $segments[0] ?? null;
$id = $segments[1] ?? null;

switch ($resource) {

    case 'register':

        // GET BY ID
        if ($method === 'GET') {

            $result = $register->getById($id);
            
            if ($result) {
                http_response_code(200);
                response("success", $result, "Data found");
            } else {
                http_response_code(404);
                response("error", null, "Data not found");
            }
            exit();
        }

        // CREATE
        if ($method === 'POST') {

            if ($validator->isExists('akun', 'email', $input['email'])) {
                http_response_code(409);
                response("error", null, "Email already registered");
                exit();
            }

            $result = $register->create($input);

            if ($result) {
                unset($result['password']);
                http_response_code(201);
                response("success", $result, "Data created successfully");
            } else {
                http_response_code(500);
                response("error", null, "Failed to create data");
            }
            exit();
        }

    default:
        http_response_code(404);
        response("error", null, "Route not found");
        break;
}
