<?php
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: list.php");
    exit;
}

$product_id = (int) $_GET['id'];
$product = getProductById($product_id);

if (!$product) {
    echo "<div class='alert alert-danger text-center'>Producto no encontrado.</div>";
    exit;
}

$added = isset($_GET['added']) ? true : false;
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container my-5">
    <?php if ($added): ?>
        <div class="alert alert-success text-center">
            Producto agregado al carrito.
        </div>
    <?php endif; ?>

    <div class="product-detail-card shadow-lg">
        <div class="row">
            <!-- Imagen del producto -->
            <div class="col-md-6 text-center d-flex align-items-center justify-content-center">
                <?php if ($product['image']): ?>
                    <img src="<?php echo BASE_URL; ?>assets/images/<?php echo $product['image']; ?>" 
                         class="img-fluid rounded product-image"
                         alt="<?php echo $product['name']; ?>">
                <?php else: ?>
                    <img src="<?php echo BASE_URL; ?>assets/images/no-image.png" 
                         class="img-fluid rounded product-image" alt="No imagen">
                <?php endif; ?>
            </div>

            <!-- Detalles del producto -->
            <div class="col-md-6 d-flex flex-column justify-content-center">
                <h2 class="mb-3 product-title"><?php echo $product['name']; ?></h2>
                <p class="product-description"><?php echo $product['description']; ?></p>

                <!-- Precio con posible descuento -->
                <p class="fw-bold fs-4 price-text">
                    <i class="fa-solid fa-dollar-sign me-1"></i>
                    <?php if (!empty($product['discount']) && $product['discount'] > 0): 
                        $discounted_price = $product['price'] * (1 - $product['discount'] / 100);
                    ?>
                        <span class="text-muted text-decoration-line-through">L. <?php echo number_format($product['price'], 2); ?></span>
                        <span class="text-danger ms-2">L. <?php echo number_format($discounted_price, 2); ?> (-<?php echo $product['discount']; ?>%)</span>
                    <?php else: ?>
                        L. <?php echo number_format($product['price'], 2); ?>
                    <?php endif; ?>
                </p>

                <p class="mb-3">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="badge bg-success stock-badge">
                            <i class="fa-solid fa-cubes me-1"></i> En stock: <?php echo $product['stock']; ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger">
                            <i class="fa-solid fa-ban me-1"></i> Agotado
                        </span>
                    <?php endif; ?>
                </p>

<?php if (!isset($_SESSION['user_id'])): ?>
    <div class="alert alert-warning text-center">
        <p class="mb-2">
            Debes <a href="<?php echo BASE_URL; ?>auth/login.php" class="fw-bold text-decoration-none text-primary">iniciar sesión</a>
            para comprar este producto.
        </p>
        <p class="mb-0">
            ¿No tienes cuenta? 
            <a href="<?php echo BASE_URL; ?>auth/register.php" class="fw-bold text-decoration-none text-success">
                Crea una cuenta
            </a> para poder comprar.
        </p>
    </div>
<?php elseif ($product['stock'] > 0): ?>

                    <!-- Form con control responsivo -->
                    <form action="<?php echo BASE_URL; ?>cart/add_to_cart.php" method="POST" class="quantity-form">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                        <label for="quantity" class="form-label mb-0">Cantidad:</label>
                        <div class="quantity-input-group">
                            <button type="button" class="btn quantity-btn" onclick="changeQty(-1)">-</button>
                            <input type="number" 
                                   name="quantity" 
                                   id="quantity" 
                                   value="1" 
                                   min="1" 
                                   max="<?php echo $product['stock']; ?>" 
                                   class="form-control text-center">
                            <button type="button" class="btn quantity-btn" onclick="changeQty(1)">+</button>
                        </div>
                    </form>

                    <!-- Botón Agregar fuera del form -->
                    <button type="button" class="btn btn-yellow btn-lg w-100 mb-2" onclick="addToCart()">
                        <i class="fa-solid fa-cart-plus me-1"></i> Agregar
                    </button>

                <?php else: ?>
                    <button class="btn btn-secondary btn-sm w-50" disabled>No disponible</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="list.php" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver a productos
        </a>
    </div>
</div>

<script>
function changeQty(amount) {
    const input = document.getElementById('quantity');
    let value = parseInt(input.value) || 1;
    const max = parseInt(input.max);
    const min = parseInt(input.min);

    value += amount;
    if (value >= min && value <= max) {
        input.value = value;
    }
}

// Acción del botón Agregar fuera del form
function addToCart() {
    const form = document.querySelector('form');
    form.submit();
}
</script>

<style>
/* ===== Estilos completos de tu view ===== */
.product-detail-card {
    border-radius: 15px;
    padding: 25px;
    background-color: #fff;
    border: 2px solid #ffc107;
    transition: all 0.3s ease, box-shadow 0.3s ease;
}

.product-image {
    max-height: 350px;
    object-fit: contain;
    transition: transform 0.3s ease;
}
.product-image:hover { transform: scale(1.05); }

.product-description { color: #000; }
body.dark-mode .product-description { color: #fff; }

.price-text { cursor: pointer; transition: color 0.3s ease; }
.price-text:hover { animation: pricePulse 0.8s ease-in-out; color: #ff9800; }
@keyframes pricePulse {
    0% { transform: scale(1); text-shadow:0 0 0px rgba(255,193,7,0);}
    50% { transform: scale(1.1); text-shadow:0 0 15px rgba(255,193,7,0.7);}
    100% { transform: scale(1); text-shadow:0 0 0px rgba(255,193,7,0);}
}

.btn-yellow {
    background-color: #ffc107;
    color: #000;
    border: none;
    font-size: 0.85rem;
    padding: 6px 10px;
    transition: all 0.3s ease;
}
.btn-yellow:hover {
    background-color: #e0a800;
    transform: translateY(-1px);
    box-shadow: 0 0 8px rgba(255,193,7,0.8);
}

.quantity-form {
    display: flex;
    align-items: center;
    gap: 10px;
    width: auto;
    margin-bottom: 10px;
}

.quantity-form label {
    font-size: 0.9rem;
    white-space: nowrap;
}

.quantity-input-group {
    display: flex;
    width: 130px;
    border: 2px solid #ffc107;
    border-radius: 5px;
    overflow: hidden;
    transition: all 0.3s ease, box-shadow 0.3s ease;
}
.quantity-input-group:hover { 
    box-shadow: 0 0 6px rgba(255,193,7,0.8); 
}

.quantity-input-group .form-control {
    font-size: 1.1rem;
    padding: 6px 0;
    border: none;
    background-color: #fff;
    color: #000;
    text-align: center;
    flex: 1;
    min-width: 50px;
}

.quantity-btn {
    width: 40px;
    font-size: 1.4rem;
    border: none;
    background-color: #fff;
    color: #000;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: all 0.3s ease, box-shadow 0.2s ease;
}

.quantity-btn:hover {
    background-color: #ffc107;
    color: #000;
    box-shadow: 0 0 4px rgba(255,193,7,0.9);
}

@media (max-width: 768px) {
    .quantity-form {
        width: 100%;
        gap: 8px;
    }
    .quantity-input-group {
        width: 180%;
        max-width: 400px;
    }
    .quantity-btn { width: 35px; font-size: 1.2rem; }
    .quantity-input-group .form-control { font-size: 1rem; }
}

body.dark-mode .product-detail-card {
    background-color: #000;
    border: 2px solid #ffc107;
    box-shadow: 0 0 20px rgba(255,193,7,0.6);
}
body.dark-mode .price-text { color: #ffc107; }
body.dark-mode .price-text:hover { color: #ffcd38; }
body.dark-mode .quantity-input-group { border-color: #ffc107; }
body.dark-mode .form-control { background-color: #111; color: #ffc107; }
body.dark-mode .quantity-btn { background-color: #222; color: #ffc107; }
body.dark-mode .quantity-btn:hover {
    background-color: #ffc107;
    color: #000;
    box-shadow: 0 0 4px rgba(255,193,7,0.9);
}
body.dark-mode .btn-yellow { box-shadow: 0 0 8px rgba(255,193,7,0.8); }
body.dark-mode a.btn-outline-secondary {
    border-color: #ffc107;
    color: #ffc107;
}
body.dark-mode a.btn-outline-secondary:hover {
    background-color: #ffc107;
    color: #000;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
