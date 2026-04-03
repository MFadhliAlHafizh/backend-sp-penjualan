<?php
// ambil URL
$url = $_GET['url'] ?? '';

// arahkan ke routing
require_once __DIR__ . '/routes/kriteriaRoute.php';