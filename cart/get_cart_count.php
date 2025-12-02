<?php
require_once __DIR__ . '/../includes/functions.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

echo json_encode(['count' => getCartCount()]);
