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
        // GET ALL
        if ($method === 'GET') {

            $result = $kriteria->getAll();

            if ($result !== false) {
                http_response_code(200);
                response("success", $result, "Data retrieved successfully");
            } else {
                http_response_code(500);
                response("error", null, "Failed to retrieve data");
            }
            exit();
        }

        // CREATE
        if ($method === 'POST') {

            if (!$input) {
                http_response_code(400);
                response("error", null, "Invalid input");
                exit();
            }

            $result = $kriteria->create($input);

            if ($result) {
                http_response_code(201);
                response("success", $result, "Data created successfully");
            } else {
                http_response_code(500);
                response("error", null, "Failed to create data");
            }
            exit();
        }
        break;

    // GET BY ID
    case 'kriteria/show':
        if ($method === 'GET') {

            $id = $_GET['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                response("error", null, "ID is required");
                exit();
            }

            $result = $kriteria->getById($id);

            if ($result) {
                http_response_code(200);
                response("success", $result, "Data found");
            } else {
                http_response_code(404);
                response("error", null, "Data not found");
            }
            exit();
        }
        break;

    // UPDATE
    case 'kriteria/update':
        if ($method === 'PUT') {

            $id = $_GET['id'] ?? null;

            if (!$id || !$input) {
                http_response_code(400);
                response("error", null, "Invalid ID or input");
                exit();
            }

            $result = $kriteria->update($id, $input);

            if ($result) {
                http_response_code(200);
                response("success", $result, "Data updated successfully");
            } else {
                http_response_code(500);
                response("error", null, "Failed to update data");
            }
            exit();
        }
        break;

    // DELETE
    case 'kriteria/delete':
        if ($method === 'DELETE') {

            $id = $_GET['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                response("error", null, "ID is required");
                exit();
            }

            $result = $kriteria->delete($id);

            if ($result) {
                http_response_code(200);
                response("success", null, "Data deleted successfully");
            } else {
                http_response_code(500);
                response("error", null, "Failed to delete data");
            }
            exit();
        }
        break;

    // DEFAULT
    default:
        http_response_code(404);
        response("error", null, "Route not found");
        break;
}
