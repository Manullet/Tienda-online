<?php
require_once __DIR__ . '/../../includes/functions.php';
session_start();
requireAdmin();
global $pdo;

$id = $_GET['id'] ?? null;
if (!$id) exit("ID no proporcionado");

// Reactivar producto: cambiar estado a 1
$stmt = $pdo->prepare("UPDATE products SET estado=1 WHERE id=:id");
$stmt->execute(['id'=>$id]);

header("Location: products_desactivados.php");
exit;
