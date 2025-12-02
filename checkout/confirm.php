<?php
/**
 * checkout/confirm.php
 * 
 * Confirmación de compra.
 */

require_once __DIR__ . '/../includes/functions.php';

// Vaciar carrito
unset($_SESSION['cart']);
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container my-5 text-center">
    <h2 class="mb-4">Compra Exitosa!</h2>
    <p class="fs-5">Gracias por tu compra. Tu pedido ha sido procesado correctamente.</p>
    <p class="fs-6 text-muted">Recibirás un correo de confirmación (simulado en este proyecto).</p>

    <a href="<?php echo BASE_URL; ?>products/list.php" class="btn btn-primary btn-lg mt-4">
        Volver a la tienda
    </a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
