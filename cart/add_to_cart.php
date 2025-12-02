<?php
/**
 * cart/add_to_cart.php
 * 
 * Agrega un producto al carrito usando $_SESSION
 */

require_once __DIR__ . '/../includes/functions.php';

// Verificar que se haya enviado un producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = (int) $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

    // Validar cantidad mínima
    if ($quantity < 1) $quantity = 1;

    // Verificar que el producto exista y tenga stock
    $product = getProductById($productId);
    if ($product) {
        if ($quantity > $product['stock']) {
            $quantity = $product['stock']; // Limitar a stock disponible
        }

        // Agregar al carrito
        addToCart($productId, $quantity);

        // Redirigir de vuelta con mensaje de éxito
        header("Location: ../products/list.php?added=1");
        exit;
    } else {
        // Producto no encontrado
        header("Location: ../products/list.php?error=1");
        exit;
    }
} else {
    // Si se accede directo a este archivo
    header("Location: ../products/list.php");
    exit;
}
?>