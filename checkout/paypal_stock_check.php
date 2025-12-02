<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$cart = $input['cart'] ?? [];

$errors = [];
global $pdo;

foreach ($cart as $item) {
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = :id");
    $stmt->execute(['id' => $item['id']]);
    $stock = $stmt->fetchColumn();

    if ($stock === false) {
        $errors[] = "El producto " . htmlspecialchars($item['name']) . " no existe.";
    } elseif ($stock < $item['quantity']) {
        $errors[] = "No hay suficiente stock de " . htmlspecialchars($item['name']) . ". Disponible: $stock";
    }
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode('<br>', $errors)
    ]);
} else {
    echo json_encode(['success' => true]);
}
