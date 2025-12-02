<?php
require_once __DIR__ . '/../../includes/functions.php';
session_start();
requireAdmin();
global $pdo;

// Traer órdenes con productos
$stmt = $pdo->query("
    SELECT o.id AS order_id, o.name AS customer_name, o.total, o.status, o.payment_method, o.created_at
    FROM orders o
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Historial de Ventas</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>ID Orden</th>
        <th>Cliente</th>
        <th>Total</th>
        <th>Estado</th>
        <th>Método de Pago</th>
        <th>Fecha</th>
        <th>Detalles</th>
    </tr>
    <?php foreach($orders as $order): ?>
    <tr>
        <td><?= $order['order_id'] ?></td>
        <td><?= htmlspecialchars($order['customer_name']) ?></td>
        <td><?= number_format($order['total'],2) ?></td>
        <td><?= $order['status'] ?></td>
        <td><?= $order['payment_method'] ?></td>
        <td><?= $order['created_at'] ?></td>
        <td>
            <a href="admin_order_detail.php?id=<?= $order['order_id'] ?>">Ver Productos</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
