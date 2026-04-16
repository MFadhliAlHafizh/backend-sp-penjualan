<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// handle preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../models/RiwayatModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

header("Content-Type: application/json");

function response($status, $data = null, $message = "") {
    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data
    ]);
}

// koneksi DB
$database = new Database();
$db = $database->connect();

if (!$db) {
    http_response_code(500);
    response("error", null, "Database connection failed");
    exit();
}

$riwayat = new Riwayat($db);

// ambil method & endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request = $_GET['url'] ?? '';
$segments = explode('/', trim($request, '/'));

$resource = $segments[0] ?? null;
$id = $segments[1] ?? null;

switch ($resource) {

    case 'riwayat':

        // GET ALL
        if ($method === 'GET' && !$id) {

            $user = getUserFromToken();
            $id_user = $user['id_user'];
            $peran = $user['peran'];

            $result = $riwayat->getAll($id_user, $peran);

            if ($result !== false) {
                http_response_code(200);
                response("success", $result, "Data retrieved successfully");
            } else {
                http_response_code(500);
                response("error", null, "Failed to retrieve data");
            }
        }

        if ($method === 'GET' && $id) {

            $profile = $riwayat->getProfileByConsultationId($id);
            $responses = $riwayat->getResponsesByConsultationId($id);
            $results = $riwayat->getResultsByConsultationId($id);

            if ($profile) {
                http_response_code(200);
                response("success", [
                    "profile" => $profile,
                    "responses" => $responses,
                    "results" => $results
                ], "Detail retrieved successfully");
            } else {
                http_response_code(404);
                response("error", null, "Data not found");
            }
        }        

        // DELETE
        if ($method === 'DELETE') {

            if (!$id) {
                http_response_code(400);
                response("error", null, "ID is required");
                exit();
            }

            $result = $riwayat->delete($id);

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

    default:
        http_response_code(404);
        response("error", null, "Route not found");
        break;
}
