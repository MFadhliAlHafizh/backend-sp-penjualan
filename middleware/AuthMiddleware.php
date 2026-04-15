<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function getUserFromToken() {
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Token tidak ditemukan"]);
        exit();
    }

    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);

    try {
        $jwtConfig = require __DIR__ . '/../config/jwt.php';

        $decoded = JWT::decode($token, new Key($jwtConfig['key'], 'HS256'));

        return (array) $decoded->data;

    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Token tidak valid"]);
        exit();
    }
}
