<?php
require_once __DIR__ . '/../includes/functions.php';
session_start();

$cartItems = getCartItems();

// Calcular total considerando descuentos
$total = 0;
foreach($cartItems as $item){
    $unitPrice = (!empty($item['discount']) && $item['discount']>0) 
                 ? $item['price'] * (1 - $item['discount']/100) 
                 : $item['price'];
    $total += $unitPrice * $item['quantity'];
}

include __DIR__ . '/../includes/header.php';
?>

<style>
.cart-container { max-width: 900px; margin: auto; }
.cart-item {
    background: var(--bs-light); border-radius: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 1rem; margin-bottom: 1rem; display: flex; gap: 1rem; align-items: center; position: relative;
    transition: transform 0.25s ease, box-shadow 0.25s ease, opacity 0.25s ease;
}
.cart-item:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.12); }

.cart-image img { width: 80px; height: 80px; object-fit: cover; border-radius: 0.8rem; border: 2px solid #eee; background: #fff; }
.cart-info { flex: 1; display: flex; flex-direction: column; gap: 0.15rem; }
.cart-name { font-size: 1.05rem; font-weight: 700; color: #212529; }
.cart-price, .cart-subtotal { font-size: 0.98rem; font-weight: 600; color: #343a40; }

.remove-btn {
    background: none; border: none; color: #dc3545; font-size: 1.4rem; cursor: pointer;
    position: absolute; top: 10px; right: 10px;
    transition: transform 0.2s ease;
}
.remove-btn:hover { transform: scale(1.3); }

.cart-total-box { background: var(--bs-dark); color: #fff; border-radius: 1rem; padding: 1rem 1.5rem; font-size: 1.2rem; font-weight: 700; text-align: right; box-shadow: 0 3px 10px rgba(0,0,0,0.18); }

/* DARK MODE */
body.dark-mode .cart-item { background: #1e1e1e; color: #e9ecef; box-shadow: 0 2px 10px rgba(0,0,0,0.5); }
body.dark-mode .cart-image img { border: 2px solid rgba(255,255,255,0.06); background: transparent; }
body.dark-mode .cart-name { color: #f8f9fa; }
body.dark-mode .cart-price, body.dark-mode .cart-subtotal { color: #ffd86b; }
body.dark-mode .cart-total-box { background: linear-gradient(90deg,#2e2e2e,#1b1b1b); color: #fff; }

/* TOAST */
.toast-notification {
    position: fixed; top: 20px; right: 20px; background: #198754; color: white;
    padding: 0.75rem 1rem; border-radius: 8px; box-shadow: 0 6px 24px rgba(0,0,0,0.2);
    font-weight: 700; z-index: 9999; opacity: 0; transform: translateY(-14px);
    transition: opacity 0.25s ease, transform 0.25s ease; pointer-events: none;
}
.toast-notification.show { opacity: 1; transform: translateY(0); pointer-events: auto; }
</style>

<div class="container my-5 cart-container">
    <h2 class="mb-4 text-center fw-bold">🛒 Mi Carrito</h2>

    <div id="cart-items">
        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info text-center">
                Tu carrito está vacío. <a href="<?php echo BASE_URL; ?>products/list.php">Ver productos</a>
            </div>
        <?php else: ?>
            <?php foreach ($cartItems as $item): 
                $unitPrice = (!empty($item['discount']) && $item['discount']>0) 
                             ? $item['price'] * (1 - $item['discount']/100) 
                             : $item['price'];
                $subtotal = $unitPrice * $item['quantity'];
            ?>
            <div class="cart-item" data-product-id="<?php echo $item['id']; ?>" data-product-name="<?php echo htmlspecialchars($item['name']); ?>">
                <div class="cart-image">
                    <?php if (!empty($item['image'])): ?>
                        <img src="<?php echo BASE_URL; ?>assets/images/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <?php else: ?>
                        <img src="<?php echo BASE_URL; ?>assets/images/no-image.png" alt="Sin imagen">
                    <?php endif; ?>
                </div>

                <div class="cart-info">
                    <span class="cart-name"><?php echo $item['name']; ?></span>
                    <span class="cart-subtotal">
                        Cantidad: <strong><?php echo $item['quantity']; ?></strong> - 
                        Subtotal: L. <?php echo number_format($subtotal, 2); ?>
                        <?php if(!empty($item['discount']) && $item['discount']>0): ?>
                            <small class="text-muted">(Descuento <?php echo $item['discount']; ?>%)</small>
                        <?php endif; ?>
                    </span>
                </div>

                <button class="remove-btn"><i class="bi bi-trash"></i></button>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if(!empty($cartItems)): ?>
    <div class="cart-total-box mt-4">
        Total: <span id="cart-total-value">L. <?php echo number_format($total, 2); ?></span>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between mt-4 gap-3">
        <a href="<?php echo BASE_URL; ?>products/list.php" class="btn btn-outline-secondary btn-lg"><i class="bi bi-arrow-left"></i> Seguir comprando</a>
        <div class="d-flex gap-2 flex-column flex-md-row">
            <a href="<?php echo BASE_URL; ?>checkout/paypal.php" class="btn btn-primary btn-lg shadow"><i class="bi bi-paypal"></i> Pagar con PayPal</a>
            <a href="<?php echo BASE_URL; ?>checkout/cash.php" class="btn btn-warning btn-lg shadow text-white"><i class="bi bi-cash-coin"></i> Pago contra entrega</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<div id="toast" class="toast-notification"></div>

<script>
// Toast
function showToast(msg) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(()=> toast.classList.remove('show'), 2500);
}

// Actualizar contador del carrito en el header
function updateCartCount() {
    fetch('<?php echo BASE_URL; ?>cart/get_cart_count.php')
        .then(res=>res.json())
        .then(data=>{
            document.querySelectorAll('.nav-link[href$="cart/view_cart.php"]').forEach(link=>{
                link.textContent = `Carrito (${data.count})`;
            });
        });
}

// Botón eliminar producto
document.querySelectorAll('.remove-btn').forEach(btn=>{
    btn.addEventListener('click', async ()=>{
        const cartItem = btn.closest('.cart-item');
        const productId = cartItem.dataset.productId;
        const productName = cartItem.dataset.productName;

        const fd = new FormData();
        fd.append('product_id', productId);
        fd.append('ajax', 1);

        try {
            const resp = await fetch('<?php echo BASE_URL; ?>cart/remove_from_cart.php',{method:'POST',body:fd,credentials:'same-origin'});
            const data = await resp.json();

            if(data.success){
                cartItem.remove();
                document.getElementById('cart-total-value').textContent = 'L.'+Number(data.total).toFixed(2);
                updateCartCount();

                if(data.total === 0){
                    document.getElementById('cart-items').innerHTML = `<div class="alert alert-info text-center">Tu carrito está vacío. <a href="<?php echo BASE_URL; ?>products/list.php">Ver productos</a></div>`;
                }

                showToast(`🗑️ Eliminaste: ${productName}`);
            }

        } catch(err){ console.error(err); }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
