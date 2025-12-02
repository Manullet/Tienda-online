<?php
require_once __DIR__ . '/../../includes/functions.php';
session_start();
requireAdmin();
global $pdo;

$id = $_GET['id'] ?? null;
if (!$id) exit("ID no proporcionado");

// Borrado lógico: cambiar estado a 0
$stmt = $pdo->prepare("UPDATE products SET estado=0 WHERE id=:id");
$stmt->execute(['id'=>$id]);

header("Location: inventario.php");
exit;
