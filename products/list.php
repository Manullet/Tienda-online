<?php
/**
 * public/products/list.php
 * Lista de productos disponibles (pública)
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// ✅ Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Variables de sesión
$userId   = $_SESSION['user_id']   ?? null;
$userName = $_SESSION['user_name'] ?? null;
$roleId   = $_SESSION['role_id']   ?? null;

// Obtener todos los productos
$products = getAllProducts();

// Mensaje de éxito si se agregó al carrito
$added = isset($_GET['added']) ? true : false;

include __DIR__ . '/../includes/header.php';
?>

<style>
/* ===== PRODUCTOS - Estilo general ===== */
.product-card {
    border: 1px solid transparent;
    transition: all 0.3s ease;
    font-family: 'Roboto', sans-serif;
}

/* Título principal */
#productos-disponibles {
    color: #000;
}

.dark-mode #productos-disponibles {
    color: #ffc107;
}

/* Botones */
.product-card .btn-primary {
    background-color: #ffc107;
    color: #000;
    border-color: #ffc107;
    transition: all 0.3s ease;
}
.product-card .btn-primary:hover {
    background-color: #e0ac00;
    border-color: #e0ac00;
}

/* ===== MODO OSCURO ===== */
.dark-mode .product-card {
    border: 1px solid #ffc107;
    background-color: #111;
    color: #fff;
}
.dark-mode .product-card .btn-primary {
    background-color: #ffc107;
    color: #000;
    border-color: #ffc107;
}
.dark-mode .product-card .btn-primary:hover {
    background-color: #e0ac00;
    border-color: #e0ac00;
}
.dark-mode .text-muted { color: #ffc107; }

/* ===== PRECIO CON DESCUENTO ===== */
.price-container {
    margin-bottom: 10px;
}

.original-price {
    text-decoration: line-through;
    color: #888;
    font-size: 1rem;
    margin-right: 8px;
}

.discounted-price {
    color: #e0ac00;
    font-size: 1.25rem;
    font-weight: bold;
}

.dark-mode .original-price {
    color: #bbb;
}
.dark-mode .discounted-price {
    color: #ffc107;
}

/* Badge descuento */
.discount-badge {
    position: absolute;
    top: 0;
    right: 0;
    background-color: #e63946;
    color: #fff;
    font-weight: bold;
    padding: 5px 10px;
    border-bottom-left-radius: 10px;
    font-size: 0.9rem;
}
</style>

<div class="container my-5">
    <h2 id="productos-disponibles" class="mb-4 text-center">Productos Disponibles</h2>

    <?php if ($added): ?>
        <div class="alert alert-success text-center">
            Producto agregado al carrito.
        </div>
    <?php endif; ?>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($products as $product): 
            $hasDiscount = isset($product['discount']) && $product['discount'] > 0;
            $discountedPrice = $hasDiscount ? ($product['price'] - ($product['price'] * $product['discount'] / 100)) : $product['price'];
        ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0 product-card">
                    <div class="position-relative">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= BASE_URL; ?>assets/images/<?= htmlspecialchars($product['image']); ?>"
                                 class="card-img-top product-img"
                                 alt="<?= htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <img src="<?= BASE_URL; ?>assets/images/no-image.png"
                                 class="card-img-top product-img" alt="No imagen">
                        <?php endif; ?>

                        <?php if ($product['stock'] <= 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">Agotado</span>
                        <?php endif; ?>

                        <?php if ($hasDiscount): ?>
                            <span class="discount-badge">-<?= number_format($product['discount'], 0); ?>%</span>
                        <?php endif; ?>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text text-truncate"><?= htmlspecialchars($product['description']); ?></p>

                        <div class="price-container">
                            <?php if ($hasDiscount): ?>
                                <span class="original-price">L. <?= number_format($product['price'], 2); ?></span>
                                <span class="discounted-price">L. <?= number_format($discountedPrice, 2); ?></span>
                            <?php else: ?>
                                <span class="discounted-price">L. <?= number_format($product['price'], 2); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($product['stock'] > 0): ?>
                            <a href="<?= BASE_URL; ?>products/view.php?id=<?= $product['id']; ?>" 
                               class="btn btn-primary mt-auto w-100">
                                Ver producto
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100 mt-auto" disabled>No disponible</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
