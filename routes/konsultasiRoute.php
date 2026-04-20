<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../models/KonsultasiModel.php';
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

$konsultasi = new Konsultasi($db);

// ambil user dari token
$user = getUserFromToken();
$id_user = $user['id_user'];

// ambil input
$input = json_decode(file_get_contents("php://input"), true);

$method = $_SERVER['REQUEST_METHOD'];
$request = $_GET['url'] ?? '';
$segments = explode('/', trim($request, '/'));

$resource = $segments[0] ?? null;

if ($resource === 'konsultasi' && $method === 'POST') {

    if (!isset($input['jawaban'])) {
        http_response_code(400);
        response("error", null, "Jawaban tidak ditemukan");
        exit();
    }

    $result = $konsultasi->rekomendasi($id_user, $input['jawaban']);

    if (isset($result['error'])) {
        http_response_code(500);
        response("error", null, $result['error']);
        exit();
    }

    response("success", $result, "Rekomendasi berhasil");
    exit();
}

http_response_code(404);
response("error", null, "Route tidak ditemukan");
