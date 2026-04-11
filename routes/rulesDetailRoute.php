<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/RulesDetailModel.php';

header("Content-Type: application/json");

function response($status, $data = null, $message = "") {
    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data
    ]);
}

$database = new Database();
$db = $database->connect();

if (!$db) {
    http_response_code(500);
    response("error", null, "Database connection failed");
    exit();
}

$rulesDetail = new RulesDetail($db);

$method = $_SERVER['REQUEST_METHOD'];
$request = $_GET['url'] ?? '';
$segments = explode('/', trim($request, '/'));

$resource = $segments[0] ?? null;    // rules
$subResource = $segments[1] ?? null; // detail
$id = $segments[2] ?? null;          // 1

if ($resource !== 'rules') {
    http_response_code(404);
    response("error", null, "Route not found");
    exit();
}

switch ($subResource) {
    case 'detail':

        if ($method === 'GET') {
            $result = $rulesDetail->getByRuleId($id);

            if ($result && count($result) > 0) {
                http_response_code(200);
                response("success", $result, "Data found");
            } else {
                http_response_code(404);
                response("error", null, "Data not found");
            }
            exit();
        }

        http_response_code(405);
        response("error", null, "Method not allowed");
        exit();

    default:
        http_response_code(404);
        response("error", null, "Sub route not found");
        exit();
}