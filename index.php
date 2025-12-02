<?php
/**
 * index.php
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Evitar cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Configuración y conexión
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Productos destacados
$sql = "SELECT id, name, description, price, discount, image 
        FROM products 
        WHERE estado = 1 
        ORDER BY id ASC 
        LIMIT 2";
$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Header
include __DIR__ . '/includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

<style>
/* ===== Animaciones globales ===== */
@keyframes fadeInUp {
  0% { opacity: 0; transform: translateY(40px); }
  100% { opacity: 1; transform: translateY(0); }
}
@keyframes glow {
  0% { text-shadow: 0 0 5px #ffc107, 0 0 10px #ffc107; }
  50% { text-shadow: 0 0 15px #ffda47, 0 0 30px #ffc107; }
  100% { text-shadow: 0 0 5px #ffc107, 0 0 10px #ffc107; }
}

/* ===== General ===== */
body {
    font-family: 'Montserrat', sans-serif;
    background-color: #FFF8E7;
    color: #000;
    transition: background-color 0.4s ease, color 0.4s ease;
}
body.dark-mode {
    background-color: #000;
    color: #fff;
}
h2, h3 {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    animation: fadeInUp 1s ease both;
}

/* ===== Hero/Slogan ===== */
.slogan-section {
    position: relative;
    height: 70vh;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    background: url('<?php echo BASE_URL; ?>assets/images/321.jpg') center/cover no-repeat fixed;
    overflow: hidden;
}
.slogan-section::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.55);
    backdrop-filter: blur(8px);
}
.slogan-section h2 {
    position: relative;
    font-size: 3.5rem;
    color: #fff;
    animation: glow 3s infinite ease-in-out;
    padding: 0 1rem;
}

/* ===== Visión y Misión ===== */
.vision-mision-section {
    padding: 4rem 2rem;
    background: linear-gradient(135deg, rgba(255,248,231,0.9), rgba(255,244,208,0.9));
    border-radius: 20px;
    backdrop-filter: blur(10px);
    box-shadow: inset 0 1px 3px rgba(255,255,255,0.6), inset 0 -3px 10px rgba(0,0,0,0.1);
}
body.dark-mode .vision-mision-section {
    background: rgba(20,20,20,0.85);
    box-shadow: 0 0 20px rgba(255,193,7,0.2);
}
.hover-card {
    border-radius: 20px;
    overflow: hidden;
    perspective: 1000px;
    transform-style: preserve-3d;
    transition: transform 0.5s ease;
    height: 350px;
}
.hover-card:hover {
    transform: rotateY(8deg) scale(1.02);
}
.hover-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(0.8);
    transition: filter 0.3s ease;
}
.hover-card:hover img {
    filter: brightness(0.4);
}
.hover-card .card-content {
    position: absolute;
    inset: 0;
    background: rgba(255,193,7,0.85);
    border-radius: 20px;
    color: #000;
    opacity: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 2rem;
    text-align: center;
    transition: opacity 0.4s ease;
}
.hover-card:hover .card-content {
    opacity: 1;
}
body.dark-mode .hover-card .card-content {
    background: rgba(255,193,7,0.75);
}

/* ===== Productos Destacados ===== */
#mermeladas {
    padding: 4rem 1rem;
}
.product-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    padding-bottom: 1rem;
    box-shadow: 8px 8px 16px rgba(0,0,0,0.15), -8px -8px 16px rgba(255,255,255,0.8);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
}
body.dark-mode .product-card {
    background: #121212;
    box-shadow: 8px 8px 16px rgba(0,0,0,0.6), -8px -8px 16px rgba(40,40,40,0.6);
}
.product-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 10px 30px rgba(255,193,7,0.4);
}
.product-card img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    border-radius: 16px;
    transition: transform 0.4s ease;
}
.product-card:hover img {
    transform: scale(1.08);
}
.product-card .card-body {
    padding: 1.2rem;
}
.product-card .card-title {
    font-size: 1.3rem;
    font-weight: 600;
}
.product-card .card-text {
    font-size: 0.95rem;
    color: #555;
}
body.dark-mode .product-card .card-text {
    color: #ccc;
}
.btn-warning {
    background: linear-gradient(135deg, #ffc107, #ffda47);
    border: none;
    color: #000;
    padding: 0.7rem 1.8rem;
    border-radius: 50px;
    font-weight: 700;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 10px rgba(255,193,7,0.4);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.btn-warning:hover {
    transform: scale(1.05) translateY(-3px);
    box-shadow: 0 8px 20px rgba(255,193,7,0.6);
}

/* Badge de descuento */
.discount-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #28a745;
    color: #fff;
    padding: 6px 10px;
    font-weight: bold;
    font-size: 0.9rem;
    border-radius: 12px;
    box-shadow: 0 0 6px rgba(0,0,0,0.3);
}

/* Precio con descuento */
.text-decoration-line-through {
    text-decoration: line-through;
}
.price-section .text-danger {
    color: #dc3545 !important;
}
.price-section .badge {
    font-size: 0.85rem;
    padding: 0.4em 0.6em;
    border-radius: 10px;
}

/* ===== Responsive ===== */
@media (max-width: 992px) {
    .slogan-section h2 { font-size: 2.5rem; }
    .hover-card { height: 280px; }
    .product-card img { height: 220px; }
}
@media (max-width: 576px) {
    .slogan-section { height: 40vh; }
    .slogan-section h2 { font-size: 2rem; }
    .hover-card { height: 240px; }
    .product-card img { height: 180px; }
}
</style>

<div class="main-content">

    <!-- Slogan Section -->
    <section class="slogan-section">
        <h2>Sabor que inspira</h2>
    </section>

    <!-- Visión y Misión -->
    <section class="vision-mision-section mt-5">
        <div class="container">
            <div class="row justify-content-center g-4">
                <div class="col-md-6">
                    <div class="hover-card">
                        <img src="<?php echo BASE_URL; ?>assets/images/af.jpg" alt="Visión">
                        <div class="card-content">
                            <h3>Nuestra Visión</h3>
                            <p>Ser en 2028 el referente por excelencia de Tegucigalpa, ofreciendo mermeladas únicas e irresistibles que deleiten el paladar de la población hondureña.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="hover-card">
                        <img src="<?php echo BASE_URL; ?>assets/images/Nae.jpg" alt="Misión">
                        <div class="card-content">
                            <h3>Nuestra Misión</h3>
                            <p>Brindar a la población hondureña de Tegucigalpa y sus alrededores una experiencia única e inigualable mediante la oferta de mermeladas.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Productos Destacados -->
    <section id="mermeladas" class="mt-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold hero-text">Mermeladas Destacadas</h2>
            <div class="row text-center">
                <?php if (!empty($productos)): ?>
                    <?php foreach($productos as $prod): ?>
                        <?php
                        $discount = isset($prod['discount']) ? floatval($prod['discount']) : 0;
                        $hasDiscount = $discount > 0;
                        $discountedPrice = $hasDiscount ? $prod['price'] - ($prod['price'] * ($discount / 100)) : $prod['price'];
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="product-card h-100 hover-scale d-flex flex-column">
                                <?php if ($hasDiscount): ?>
                                    <div class="discount-badge">-<?php echo $discount; ?>%</div>
                                <?php endif; ?>
                                <img src="<?php echo BASE_URL; ?>assets/images/<?php echo htmlspecialchars($prod['image']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($prod['name']); ?>">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <h5 class="card-title"><?php echo htmlspecialchars($prod['name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($prod['description']); ?></p>
                                    </div>
                                    <div class="price-section">
                                        <?php if ($hasDiscount): ?>
                                            <p class="fw-bold mb-1">
                                                <span class="text-decoration-line-through text-muted fs-6">
                                                    L. <?php echo number_format($prod['price'], 2); ?>
                                                </span><br>
                                                <span class="text-danger fs-5 fw-bold">
                                                    L. <?php echo number_format($discountedPrice, 2); ?>
                                                </span>
                                            </p>
                                        <?php else: ?>
                                            <p class="fw-bold mb-2">L. <?php echo number_format($prod['price'], 2); ?></p>
                                        <?php endif; ?>
                                        <a href="<?php echo BASE_URL; ?>products/view.php?id=<?php echo $prod['id']; ?>" class="btn btn-warning">Compra aquí</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">No hay productos destacados en este momento.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
</div>
