<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$errors = [];
$response = ['success' => false, 'message' => ''];

try {
    $cartItems = getCartItems();
    $total = getCartTotal();

    if (empty($cartItems)) {
        echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
        exit;
    }

    // Recibir datos del formulario
    $data = json_decode(file_get_contents('php://input'), true);

    $name = sanitize($data['name'] ?? '');
    $phone = sanitize($data['phone'] ?? '');
    $address = sanitize($data['address'] ?? '');
    $city = sanitize($data['city'] ?? '');

    if (!$name) $errors[] = "El nombre es obligatorio.";
    if (!$phone) $errors[] = "El teléfono es obligatorio.";
    if (!$address) $errors[] = "La dirección es obligatoria.";
    if (!$city) $errors[] = "La ciudad es obligatoria.";

    global $pdo;

    // Validar stock de cada producto
    if (empty($errors)) {
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
    }

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode("\n", $errors)]);
        exit;
    }

    // Procesar pedido
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, name, phone, address, city, total, payment_method) 
                           VALUES (:user_id, :name, :phone, :address, :city, :total, 'cash')");
    $stmt->execute([
        'user_id' => $_SESSION['user_id'] ?? null,
        'name' => $name,
        'phone' => $phone,
        'address' => $address,
        'city' => $city,
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

        $updateStock = $pdo->prepare("UPDATE products SET stock = stock - :quantity WHERE id = :id");
        $updateStock->execute([
            'quantity' => $item['quantity'],
            'id' => $item['id']
        ]);
    }

    $pdo->commit();
    unset($_SESSION['cart']);

    echo json_encode(['success' => true, 'message' => 'Pedido confirmado. ¡Gracias por tu compra!']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
