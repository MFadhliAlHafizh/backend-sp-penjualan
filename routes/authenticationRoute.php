<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// handle preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../models/AuthenticationModel.php';
require_once __DIR__ . '/../Validator.php';

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

use Firebase\JWT\JWT;
$jwtConfig = require __DIR__ . '/../config/jwt.php';

$auth = new Authentication($db);
$validator = new Validator($db);

// parsing input JSON
$input = json_decode(file_get_contents("php://input"), true);

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

            $result = $auth->getById($id);
            
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

            $result = $auth->create($input);

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
        break;

    case 'login':

        if ($method === 'POST') {

            if (!$input || empty($input['email']) || empty($input['password'])) {
                http_response_code(400);
                response("error", null, "Email dan password wajib diisi");
                exit();
            }

            // cek user berdasarkan email
            $user = $auth->getByEmail($input['email']);

            if (!$user) {
                http_response_code(404);
                response("error", null, "Email tidak ditemukan");
                exit();
            }

            // verifikasi password
            if (!password_verify($input['password'], $user['password'])) {
                http_response_code(401);
                response("error", null, "Password salah");
                exit();
            }

            // 🔐 generate JWT
            $payload = [
                "iss" => $jwtConfig['issuer'],
                "aud" => $jwtConfig['audience'],
                "iat" => time(),
                "exp" => time() + $jwtConfig['expire'],
                "data" => [
                    "id_user" => $user['id_user'],
                    "email" => $user['email'],
                    "peran" => $user['peran']
                ]
            ];

            $jwt = JWT::encode($payload, $jwtConfig['key'], 'HS256');

            // hapus password dari response
            unset($user['password']);

            http_response_code(200);
            response("success", [
                "user" => $user,
                "token" => $jwt
            ], "Login berhasil");
            exit();
        }
        break;

    default:
        http_response_code(404);
        response("error", null, "Route not found");
        break;
}
