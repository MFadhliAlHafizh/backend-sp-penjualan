<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// handle preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../models/GejalaModel.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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

$gejala = new Gejala($db);

// parsing input JSON
$input = json_decode(file_get_contents("php://input"), true);

// ambil method & endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request = $_GET['url'] ?? '';
$segments = explode('/', trim($request, '/'));
$resource = $segments[0] ?? null;
$action = $segments[1] ?? null;
$id = $segments[2] ?? null;

switch ($resource) {

    case 'gejala':

        // GET ALL atau GET BY ID
        if ($method === 'GET') {
            if ($method === 'GET' && $action === 'pdf') {

                $data = $gejala->getAll();

                if ($data === false) {
                    http_response_code(500);
                    exit("Gagal mengambil data.");
                }

                ob_start();

                require __DIR__ . '/../templates/gejalaPdf.php';

                $html = ob_get_clean();

                $options = new Options();
                $options->setIsRemoteEnabled(true);
                $options->setChroot(realpath(__DIR__ . "/../"));

                $dompdf = new Dompdf($options);

                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');

                $dompdf->render();

                $dompdf->stream(
                    "Laporan_Data_Gejala.pdf",
                    ["Attachment" => false]
                );

                exit();
            }

            if (is_numeric($action)) {
                $result = $gejala->getById($action);

                if ($result) {
                    http_response_code(200);
                    response("success", $result, "Data found");
                } else {
                    http_response_code(404);
                    response("error", null, "Data not found");
                }
            } else {
                $result = $gejala->getAll();

                if ($result !== false) {
                    http_response_code(200);
                    response("success", $result, "Data retrieved successfully");
                } else {
                    http_response_code(500);
                    response("error", null, "Failed to retrieve data");
                }
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

            $result = $gejala->create($input);

            if ($result) {
                http_response_code(201);
                response("success", $result, "Data created successfully");
            } else {
                http_response_code(500);
                response("error", null, "Failed to create data");
            }
            exit();
        }

        // UPDATE
        if ($method === 'PUT') {

            if (!is_numeric($action) || !$input) {
                http_response_code(400);
                response("error", null, "Invalid ID or input");
                exit();
            }

            $result = $gejala->update($action, $input);

            if ($result) {
                http_response_code(200);
                response("success", $result, "Data updated successfully");
            } else {
                http_response_code(500);
                response("error", null, "Failed to update data");
            }
            exit();
        }

        // DELETE
        if ($method === 'DELETE') {

            if (!is_numeric($action)) {
                http_response_code(400);
                response("error", null, "ID is required");
                exit();
            }

            $result = $gejala->delete($action);

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
