<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php'; // PAYPAL_CLIENT_ID y PAYPAL_SECRET

// Verificar carrito
$cartItems = getCartItems();
if (empty($cartItems)) {
    header("Location: ../cart/view_cart.php");
    exit;
}

// Calcular total considerando descuentos
$totalHNL = 0;
foreach ($cartItems as $item) {
    $unitPrice = (!empty($item['discount']) && $item['discount'] > 0)
                 ? $item['price'] * (1 - $item['discount']/100)
                 : $item['price'];
    $totalHNL += $unitPrice * $item['quantity'];
}

// Tipo de cambio HNL → USD
$tipoCambio = 26.18;
$totalUSD = $totalHNL / $tipoCambio;

// Formatear total para PayPal
$totalFormatted = number_format($totalUSD, 2, '.', '');
?>

<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="container my-5">
    <h2 class="mb-4 text-center">Pago con PayPal</h2>
    <p class="text-center fs-5">
        Total a pagar: L. <?php echo number_format($totalHNL, 2); ?> 
        (≈ $<?php echo $totalFormatted; ?> USD)
    </p>

    <div class="d-flex justify-content-center">
        <div id="paypal-button-container" style="max-width: 400px; width: 100%;"></div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=USD"></script>

<script>
paypal.Buttons({
    createOrder: async function(data, actions) {
        // ✅ Validar stock antes de crear la orden
        const stockCheck = await fetch('paypal_stock_check.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cart: <?php echo json_encode($cartItems); ?> })
        }).then(res => res.json());

        if (!stockCheck.success) {
            Swal.fire({
                icon: 'error',
                title: 'Stock insuficiente',
                html: stockCheck.message
            });
            return; // Detener creación de orden
        }

        // Crear orden si todo está disponible
        return actions.order.create({
            purchase_units: [{
                amount: { value: '<?php echo $totalFormatted; ?>' } // TOTAL en USD con descuento
            }]
        });
    },
    onApprove: function(data, actions) {
        return actions.order.capture().then(function(details) {
            fetch('paypal_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    orderID: data.orderID,
                    payerName: details.payer.name.given_name,
                    payerEmail: details.payer.email_address,
                    totalUSD: '<?php echo $totalFormatted; ?>',
                    totalHNL: '<?php echo $totalHNL; ?>'
                })
            })
            .then(res => res.json())
            .then(response => {
                if(response.success){
                    Swal.fire({
                        icon: 'success',
                        title: 'Pago completado!',
                        text: 'Gracias por tu compra, ' + details.payer.name.given_name,
                        confirmButtonText: 'Volver a la tienda'
                    }).then(() => {
                        window.location.href = '../products/list.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al procesar tu orden',
                        text: response.message || 'Intenta de nuevo.'
                    });
                }
            });
        });
    }
}).render('#paypal-button-container');
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
