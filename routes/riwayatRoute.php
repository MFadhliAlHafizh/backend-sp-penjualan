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
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function response($status, $data = null, $message = "") {
    header("Content-Type: application/json");
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
$action = $segments[1] ?? null;
$id = $segments[2] ?? null;

switch ($resource) {

    case 'riwayat':

        // GET ALL
        if ($method === 'GET' && !$action) {

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
            exit();
        }

        if ($method === 'GET' && is_numeric($action)) {
            $data = $riwayat->getConsultationDetail($action);

            if ($data) {
                http_response_code(200);
                response("success", $data, "Detail retrieved successfully");
            } else {
                http_response_code(404);
                response("error", null, "Data not found");
            }
            exit();
        }        

        if ($method === 'GET' && $action === 'pdf' && is_numeric($id)) {
            $user = getUserFromToken();

            $id_user = $user['id_user'];
            $peran   = $user['peran'];

            if ($peran !== "admin" && !$riwayat->isOwner($id, $id_user)) {
                http_response_code(403);
                echo "Anda tidak memiliki akses ke data ini.";
                exit();
            }

            $data = $riwayat->getConsultationDetail($id);
            if (!$data) {
                http_response_code(404);
                echo "Data tidak ditemukan";
                exit();
            }

            $profile   = $data['profile'];
            $responses = $data['responses'];
            $results   = $data['results'];
            ob_start();

            require __DIR__ . '/../templates/riwayatPdf.php';
            $html = ob_get_clean();
            $options = new Options();
            $options->setIsRemoteEnabled(true);

            $options->setChroot(realpath(__DIR__ . "/../"));
            
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream(
                "Hasil_Identifikasi_{$id}.pdf", ["Attachment" => false]
            );
            exit();
        }

        // DELETE
        if ($method === 'DELETE') {
            if (!is_numeric($action)) {
                http_response_code(400);
                response("error", null, "ID is required");
                exit();
            }

            $result = $riwayat->delete($action);

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
