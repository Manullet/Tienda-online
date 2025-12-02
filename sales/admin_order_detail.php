<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireAdmin();
global $pdo;

// Buscar
$search = $_GET['search'] ?? '';
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE name LIKE :search OR phone LIKE :search OR address LIKE :search OR city LIKE :search";
    $params['search'] = "%$search%";
}

// Paginación
$limit = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

// Contar total de órdenes
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM mi_tienda.orders $where");
$countStmt->execute($params);
$totalOrders = $countStmt->fetchColumn();
$totalPages = ceil($totalOrders / $limit);

// Traer órdenes con límite y offset
$sql = "
    SELECT id, total, status, created_at, name, phone, address, city, payment_method
    FROM mi_tienda.orders
    $where
    ORDER BY created_at DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue(":$k", $v, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Detalle de orden si se solicita
$detail_order_id = $_GET['detail_id'] ?? null;
$detail_items = [];
if ($detail_order_id) {
    $stmtDetail = $pdo->prepare("
        SELECT oi.*, p.name AS product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = :order_id
    ");
    $stmtDetail->execute(['order_id' => $detail_order_id]);
    $detail_items = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);
}

include __DIR__ . '/../includes/header.php';
?>

<h2 class="orders-title" style="text-align:center; margin-bottom:20px;">📦 Listado de Órdenes</h2>

<!-- Buscador y botones exportación alineados -->
<div style="width:95%; margin:0 auto; display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
    <!-- Buscador a la izquierda -->
    <form method="GET" style="display:flex; align-items:center;">
        <input type="text" name="search" placeholder="🔍 Buscar cliente, teléfono, ciudad..."
               value="<?= htmlspecialchars($search) ?>" style="padding:8px; width:250px;">
        <button type="submit" style="padding:8px 12px; margin-left:5px;">Buscar</button>
        <?php if (!empty($search)): ?>
            <a href="?page=1" style="margin-left:10px; text-decoration:none;">❌ Limpiar</a>
        <?php endif; ?>
    </form>

    <!-- Botones exportación a la derecha -->
    <div>
        <a href="export_orders.php?format=pdf&search=<?= urlencode($search) ?>" 
           style="padding:8px 12px; background:#dc3545; color:white; text-decoration:none; border-radius:4px; margin-right:5px;">
           📄 Exportar PDF
        </a>
        <a href="export_orders.php?format=excel&search=<?= urlencode($search) ?>" 
           style="padding:8px 12px; background:#28a745; color:white; text-decoration:none; border-radius:4px;">
           📊 Exportar Excel
        </a>
    </div>
</div>

<style>
.orders-table {
    width: 95%;
    margin: 0 auto;
    border-collapse: collapse;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
    background-color: #fff;
}
.orders-table th {
    background: #1a1a1a;
    color: white;
    text-align: left;
    padding: 12px;
    font-weight: bold;
    text-transform: uppercase;
    font-size: 14px;
}
.orders-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #ddd;
}
.orders-table tr:hover {
    background-color: #f4f4f4;
}
.status-pending {
    background: #fff3cd;
    color: #856404;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: bold;
}
.status-paid {
    background: #d4edda;
    color: #155724;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: bold;
}
.detail-table {
    width: 90%;
    margin: 20px auto;
    border-collapse: collapse;
    background-color: #fff;
}
.detail-table th, .detail-table td {
    padding: 8px;
    border:1px solid #ccc;
}
.detail-table th {
    background:#333;
    color:white;
}
.detail-table .status-pending {
    background: #fff3cd;
    color: #856404;
    font-weight: bold;
}
.detail-table .status-paid {
    background: #d4edda;
    color: #155724;
    font-weight: bold;
}

/* Paginación */
.pagination {
    display:flex;
    justify-content:center;
    align-items:center;
    gap:5px;
    margin-top:20px;
}
.pagination a, .pagination span {
    display:inline-block;
    padding:6px 12px;
    border-radius:5px;
    text-decoration:none;
    color:#333;
    border:1px solid #ddd;
}
.pagination a:hover {
    background:#007bff;
    color:white;
    border-color:#007bff;
}
.pagination .current {
    background:#007bff;
    color:white;
    border-color:#007bff;
    font-weight:bold;
}

/* ----- DARK MODE ----- */
body.dark-mode .orders-table,
body.dark-mode .detail-table {
    background-color: #fff !important;
    color: #000 !important;
}
body.dark-mode .orders-table th,
body.dark-mode .detail-table th {
    background-color: #333 !important;
    color: #fff !important;
}
body.dark-mode .orders-table tr:hover,
body.dark-mode .detail-table tr:hover {
    background-color: #f4f4f4 !important;
}

/* Paginación dark mode */
body.dark-mode .pagination a,
body.dark-mode .pagination span {
    border-color: #ffc107 !important;
    border-radius: 5px;
}
/* Botones inactivos: fondo negro, letra amarilla */
body.dark-mode .pagination a {
    background-color: #000 !important;
    color: #ffc107 !important;
}
/* Hover sobre botones inactivos */
body.dark-mode .pagination a:hover {
    background-color: #000 !important;
    color: #ffc107 !important;
    border-color: #ffc107 !important;
}
/* Botón activo: fondo amarillo, letra negra */
body.dark-mode .pagination .current {
    background-color: #ffc107 !important;
    color: #000 !important;
    border-color: #ffc107 !important;
}
/* Hover sobre botón activo */
body.dark-mode .pagination .current:hover {
    background-color: #ffc107 !important;
    color: #000 !important;
    border-color: #ffc107 !important;
}

/* Título dark mode */
body.dark-mode .orders-title {
    color: #ffc107 !important;
}
</style>

<!-- Tabla principal de órdenes -->
<table class="orders-table">
    <tr>
        <th>ID</th>
        <th>Cliente</th>
        <th>Teléfono</th>
        <th>Dirección</th>
        <th>Ciudad</th>
        <th>Total</th>
        <th>Estado</th>
        <th>Método</th>
        <th>Fecha</th>
    </tr>

    <?php if (!empty($orders)): ?>
        <?php foreach($orders as $order): ?>
            <tr>
                <td>
                    <a href="?page=<?= $page ?>&search=<?= urlencode($search) ?>&detail_id=<?= $order['id'] ?>" 
                       style="color:#007bff; text-decoration:underline;">
                        <?= $order['id'] ?>
                    </a>
                </td>
                <td><?= !empty($order['name']) ? htmlspecialchars($order['name']) : '—' ?></td>
                <td><?= !empty($order['phone']) ? htmlspecialchars($order['phone']) : '—' ?></td>
                <td><?= !empty($order['address']) ? htmlspecialchars($order['address']) : '—' ?></td>
                <td><?= !empty($order['city']) ? htmlspecialchars($order['city']) : '—' ?></td>
                <td><strong>L. <?= number_format($order['total'], 2) ?></strong></td>
                <td>
                    <span class="<?= $order['status'] === 'paid' ? 'status-paid' : 'status-pending' ?>">
                        <?= htmlspecialchars($order['status']) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($order['payment_method']) ?></td>
                <td><?= htmlspecialchars($order['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="9" style="text-align:center; padding:15px;">No hay órdenes registradas.</td></tr>
    <?php endif; ?>
</table>

<!-- Detalle de la orden seleccionada -->
<?php if (!empty($detail_items)): ?>
    <h3 style="text-align:center; margin-top:30px;">Productos de la Orden #<?= htmlspecialchars($detail_order_id) ?></h3>
    <table class="detail-table">
        <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio Unitario</th>
            <th>Subtotal</th>
        </tr>
        <?php foreach($detail_items as $item): ?>
            <tr class="<?= $order['status'] === 'paid' ? 'status-paid' : 'status-pending' ?>">
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>L. <?= number_format($item['price'], 2) ?></td>
                <td>L. <?= number_format($item['quantity'] * $item['price'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<!-- Paginación estilizada -->
<div class="pagination">
    <?php if($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">&laquo; Anterior</a>
    <?php endif; ?>

    <?php for($i=1; $i<=$totalPages; $i++): ?>
        <?php if($i==$page): ?>
            <span class="current"><?= $i ?></span>
        <?php else: ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if($page < $totalPages): ?>
        <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">Siguiente &raquo;</a>
    <?php endif; ?>
</div>

<div style="height:40px;"></div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
