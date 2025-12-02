<?php
/**
 * includes/functions.php
 *
 * Funciones reutilizables para el proyecto:
 * - Sanitización de datos
 * - Manejo de carrito de compras usando $_SESSION
 * - Funciones de ayuda para obtener productos y totales
 */
require_once __DIR__ . '/../config/config.php';



// === FUNCIONES DE SANITIZACIÓN ===

/**
 * Sanitiza texto para prevenir XSS
 */
function sanitize($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// === FUNCIONES DE PRODUCTOS ===

/**
 * Devuelve un producto por ID desde la base de datos.
 */
function getProductById($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

/**
 * Devuelve todos los productos disponibles
 */
function getAllProducts() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM products WHERE estado = 1 ORDER BY id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// === FUNCIONES DE CARRITO ===

/**
 * Agrega un producto al carrito.
 * Si ya existe, incrementa la cantidad.
 */
function addToCart($productId, $quantity = 1)
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

/**
 * Elimina un producto del carrito.
 */
function removeFromCart($productId)
{
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

/**
 * Vacía todo el carrito.
 */
function clearCart()
{
    unset($_SESSION['cart']);
}

/**
 * Devuelve un array de productos con cantidad y subtotal.
 */
function getCartItems()
{
    $items = [];

    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return $items;
    }

    foreach ($_SESSION['cart'] as $productId => $qty) {
        $product = getProductById($productId);
        if ($product) {
            $product['quantity'] = $qty;
            $product['subtotal'] = $product['price'] * $qty;
            $items[] = $product;
        }
    }
    return $items;
}

/**
 * Devuelve el total del carrito (suma de subtotales).
 */
function getCartTotal()
{
    $total = 0;
    $items = getCartItems();
    foreach ($items as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}

/**
 * Devuelve el número total de productos en el carrito.
 */
function getCartCount()
{
    if (!isset($_SESSION['cart'])) return 0;
    return array_sum($_SESSION['cart']);
}


// Verifica si el usuario es administrador
function isAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verifica que exista el role_id y que sea igual a 1
    return isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 1;
}

// Redirige si no es administrador
function requireAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isAdmin()) {
        // Si el usuario no es admin, lo mandamos al login
        header("Location: " . BASE_URL . "auth/login.php?error=forbidden");
        exit;
    }
}

