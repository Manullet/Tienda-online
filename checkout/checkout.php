<?php
/**
 * checkout/checkout.php
 * 
 * Muestra el resumen de la compra y permite finalizarla.
 */

require_once __DIR__ . '/../includes/functions.php';

// Obtener items y total
$cartItems = getCartItems();

// Calcular total considerando descuentos
$total = 0;
foreach ($cartItems as &$item) {
    // Precio con descuento si existe
    if (!empty($item['discount']) && $item['discount'] > 0) {
        $item['final_price'] = $item['price'] * (1 - $item['discount'] / 100);
    } else {
        $item['final_price'] = $item['price'];
    }
    // Subtotal = final_price * quantity
    $item['subtotal'] = $item['final_price'] * $item['quantity'];
    $total += $item['subtotal'];
}
unset($item); // limpiar referencia
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4 text-center">Resumen de Compra</h2>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>
                        <?php if (!empty($item['discount']) && $item['discount'] > 0): ?>
                            <span class="text-muted text-decoration-line-through">
                                L. <?php echo number_format($item['price'], 2); ?>
                            </span>
                            <span class="text-danger ms-1">
                                L. <?php echo number_format($item['final_price'], 2); ?>
                            </span>
                        <?php else: ?>
                            L. <?php echo number_format($item['price'], 2); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>L. <?php echo number_format($item['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total:</th>
                    <th>L. <?php echo number_format($total, 2); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="../cart/view_cart.php" class="btn btn-secondary">Volver al carrito</a>
        <form action="confirm.php" method="POST">
            <button type="submit" class="btn btn-success">Finalizar compra</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
