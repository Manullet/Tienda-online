<?php
/**
 * cart/remove_from_cart.php
 * 
 * Elimina un producto completo del carrito usando $_SESSION
 * Soporta AJAX (retorna JSON) y POST normal (redirige)
 */

require_once __DIR__ . '/../includes/functions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = (int) $_POST['product_id'];

    // Guardamos el nombre antes de eliminar para el toast
    $removedName = $_SESSION['cart'][$productId]['name'] ?? '';

    // Eliminamos el producto completo
    if(isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }

    $total = getCartTotal();

    // Si es AJAX, retornamos JSON
    if (!empty($_POST['ajax'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'removed_name' => $removedName,
            'total' => $total
        ]);
        exit;
    }

    // Si no es AJAX, redirigimos
    $removedNameParam = !empty($removedName) ? '&removed_name=' . urlencode($removedName) : '';
    header("Location: view_cart.php?removed=1{$removedNameParam}");
    exit;
} else {
    header("Location: view_cart.php");
    exit;
}
