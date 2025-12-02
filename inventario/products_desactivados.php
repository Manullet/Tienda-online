<?php 
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../includes/functions.php';

// Validar que el usuario sea administrador
requireAdmin();

global $pdo;

// Obtener productos desactivados
$stmt = $pdo->query("SELECT * FROM products WHERE estado = 0 ORDER BY created_at DESC");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<style>
    .desactivos{
        color: #ffc107;
    }

</style>

<div class="container my-5">
    <h2 <h2 class="desactivos mb-4 text-center">Productos Desactivados</h2>
    <div class="text-center mb-4">
        <a href="inventario.php" class="btn btn-secondary">Volver al Inventario</a>
    </div>

    <?php if (empty($productos)): ?>
        <p class="text-center">No hay productos desactivados.</p>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($productos as $prod): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 product-card">
                        <div class="position-relative">
                            <?php if (!empty($prod['image'])): ?>
                                <img src="<?php echo BASE_URL; ?>assets/images/<?= htmlspecialchars($prod['image']); ?>"
                                     class="card-img-top product-img"
                                     alt="<?= htmlspecialchars($prod['name']); ?>">
                            <?php else: ?>
                                <img src="<?php echo BASE_URL; ?>assets/images/no-image.png"
                                     class="card-img-top product-img"
                                     alt="No imagen">
                            <?php endif; ?>

                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">Desactivado</span>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($prod['name']); ?></h5>
                            <p class="card-text text-truncate"><?= htmlspecialchars($prod['description']); ?></p>
                            <p class="card-text fw-bold fs-5">L. <?= number_format($prod['price'], 2); ?></p>

                            <a href="reactivar_producto.php?id=<?= $prod['id']; ?>" 
                               class="btn btn-warning mt-auto" 
                               onclick="return confirm('¿Reactivar producto?')">
                               Reactivar
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
