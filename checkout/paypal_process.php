<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

try {
    global $pdo;

    // Obtener datos enviados desde JS
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !isset($data['orderID'])) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
        exit;
    }

    $cartItems = getCartItems();
    $total = getCartTotal();

    if (empty($cartItems)) {
        echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
        exit;
    }

    // ✅ Validar stock antes de procesar
    $errors = [];
    foreach ($cartItems as $item) {
        $checkStock = $pdo->prepare("SELECT stock FROM products WHERE id = :id");
        $checkStock->execute(['id' => $item['id']]);
        $stock = $checkStock->fetchColumn();

        if ($stock === false) {
            $errors[] = "El producto " . htmlspecialchars($item['name']) . " no existe.";
        } elseif ($stock < $item['quantity']) {
            $errors[] = "No hay suficiente stock de " . htmlspecialchars($item['name']) . ". Disponible: $stock";
        }
    }

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(", ", $errors)]);
        exit;
    }

    // ✅ Si hay stock suficiente, registrar orden
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, name, phone, address, city, total, payment_method, status) 
                           VALUES (:user_id, :name, :phone, :address, :city, :total, 'paypal', 'paid')");
    $stmt->execute([
        'user_id' => $_SESSION['user_id'] ?? null,
        'name' => $data['payerName'] ?? 'Cliente PayPal',
        'phone' => '', // PayPal no da teléfono por defecto
        'address' => '', // Podrías agregar dirección si PayPal lo devuelve
        'city' => '',
        'total' => $total
    ]);

    $order_id = $pdo->lastInsertId();

    foreach ($cartItems as $item) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price)
                               VALUES (:order_id, :product_id, :quantity, :price)");
        $stmt->execute([
            'order_id' => $order_id,
            'product_id' => $item['id'],
            'quantity' => $item['quantity'],
            'price' => $item['price']
        ]);

        // ✅ Disminuir stock
        $updateStock = $pdo->prepare("UPDATE products SET stock = stock - :quantity WHERE id = :id");
        $updateStock->execute([
            'quantity' => $item['quantity'],
            'id' => $item['id']
        ]);
    }

    $pdo->commit();

    // Vaciar carrito
    unset($_SESSION['cart']);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => strpos($e->getMessage(), 'stock') !== false
            ? 'No hay suficiente stock para completar la compra.'
            : 'Error al procesar el pedido: ' . $e->getMessage()
    ]);
}
