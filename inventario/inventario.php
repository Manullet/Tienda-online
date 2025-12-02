<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireAdmin();
global $pdo;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $stmt = $pdo->prepare("SELECT * FROM products ORDER BY id ASC");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener productos: " . $e->getMessage());
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Carga Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

<div class="container my-5 inventory-container">
    <div class="inventory-header mb-4 d-flex justify-content-between align-items-center flex-wrap">
        <h2><i class="fa-solid fa-boxes-stacked"></i> Inventario de Productos</h2>
        <div class="d-flex gap-2">
            <a href="add_producto.php" class="btn btn-add">
                <i class="fa-solid fa-plus"></i> Agregar
            </a>
            <a href="products_desactivados.php" class="btn btn-secondary">
                <i class="fa-solid fa-eye-slash"></i> Inactivos
            </a>
        </div>
    </div>

    <div class="table-responsive shadow-lg rounded">
        <table class="inventory-table table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th><i class="fa-solid fa-tag"></i> Nombre</th>
                    <th><i class="fa-solid fa-align-left"></i> Descripción</th>
                    <th><i class="fa-solid fa-dollar-sign"></i> Precio</th>
                    <th><i class="fa-solid fa-cubes"></i> Stock</th>
                    <th><i class="fa-solid fa-circle-check"></i> Estado</th>
                    <th><i class="fa-solid fa-image"></i> Imagen</th>
                    <th><i class="fa-solid fa-gears"></i> Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 1; ?>
                <?php foreach ($productos as $prod): ?>
                <tr>
                    <td><?= $count++ ?></td>
                    <td><?= htmlspecialchars($prod['name']) ?></td>
                    <td><?= htmlspecialchars($prod['description']) ?></td>

                    <!-- ✅ Precio alineado correctamente -->
                    <td><span class="price">L. <?= number_format($prod['price'], 2) ?></span></td>

                    <td><span class="badge bg-info text-dark"><?= $prod['stock'] ?></span></td>

                    <td>
                        <?php if ($prod['estado'] == 1): ?>
                            <span class="badge bg-success"><i class="fa-solid fa-check"></i> Activo</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><i class="fa-solid fa-xmark"></i> Inactivo</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if ($prod['image']): ?>
                            <img src="<?= BASE_URL ?>assets/images/<?= htmlspecialchars($prod['image']) ?>" class="product-img" alt="<?= htmlspecialchars($prod['name']) ?>">
                        <?php else: ?>
                            <img src="<?= BASE_URL ?>assets/images/no-image.png" class="product-img" alt="Sin imagen">
                        <?php endif; ?>
                    </td>

                    <td class="text-center">
                        <a href="edit_producto.php?id=<?= $prod['id'] ?>" class="btn btn-edit btn-sm" title="Editar">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.inventory-container {
    font-family: 'Montserrat', sans-serif;
}

.inventory-header h2 {
    color: #000;
    font-weight: bold;
}

.dark-mode .inventory-header h2 {
    color: #ffc107;
}

.btn {
    padding: 8px 14px;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-add {
    background-color: #ffc107;
    color: #000;
}
.btn-add:hover { background-color: #e0b000; }

.btn-edit, .btn-delete {
    border-radius: 50%;
    padding: 8px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
}
.btn-edit { background-color: #2575fc; color: #fff; }
.btn-edit:hover { background-color: #0f54c2; }

.btn-delete { background-color: #dc3545; color: #fff; }
.btn-delete:hover { background-color: #b02a37; }

.inventory-table {
    border-radius: 12px;
    overflow: hidden;
    background-color: #fff;
}

.inventory-table thead {
    background: linear-gradient(90deg, #ffc107, #ffda47);
    color: #000;
    font-size: 1rem;
}

.inventory-table th, .inventory-table td {
    padding: 14px 16px;
    font-size: 0.95rem;
}

.inventory-table tbody tr {
    transition: box-shadow 0.2s ease, transform 0.1s ease;
}

.inventory-table tbody tr:hover {
    background-color: #f8f9fa;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    transform: scale(1.01);
}

.inventory-table img.product-img {
    width: 55px;
    height: 55px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid #ddd;
}

/* ✅ Precio en una sola línea */
.price {
    display: inline-block;
    white-space: nowrap;
    font-weight: 600;
    font-size: 1rem;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
