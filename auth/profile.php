<?php
require_once __DIR__ . '/../includes/functions.php';

if (session_status() == PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
global $pdo;

// Obtener info del usuario
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

// Obtener compras del usuario
$stmt = $pdo->prepare("
    SELECT o.id AS order_id, o.total, o.status, o.created_at, oi.product_id, oi.quantity, oi.price, p.name AS product_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = :user_id
    ORDER BY o.created_at DESC
");
$stmt->execute(['user_id' => $userId]);
$ordersRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por pedido
$ordersData = [];
foreach ($ordersRaw as $item) {
    $ordersData[$item['order_id']][] = $item;
}

// Manejar actualización de contraseña
$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        if (empty($currentPassword)) $errors[] = "Ingresa tu contraseña actual.";
        if (empty($newPassword)) $errors[] = "Ingresa la nueva contraseña.";
        if ($newPassword !== $confirmPassword) $errors[] = "Las nuevas contraseñas no coinciden.";

        // Verificar contraseña actual
        $stmtPass = $pdo->prepare("SELECT password FROM users WHERE id = :id");
        $stmtPass->execute(['id' => $userId]);
        $hashedPassword = $stmtPass->fetchColumn();

        if (!password_verify($currentPassword, $hashedPassword)) {
            $errors[] = "La contraseña actual es incorrecta.";
        }
    }

    if (empty($errors) && !empty($newPassword)) {
        try {
            $stmtUpdate = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmtUpdate->execute(['password' => password_hash($newPassword, PASSWORD_DEFAULT), 'id' => $userId]);
            $successMessage = "Contraseña actualizada correctamente.";
        } catch (Exception $e) {
            $errors[] = "Error al actualizar la contraseña: " . $e->getMessage();
        }
    } elseif(empty($errors)) {
        $successMessage = "No hubo cambios que actualizar.";
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4 text-center profile-title">Mi Perfil</h2>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" id="profileTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button">Información</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button">Mis Compras</button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Información del usuario -->
        <div class="tab-pane fade show active" id="info">
            <div class="card shadow-sm p-4 mb-4">
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Correo:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <button class="btn btn-warning text-dark mt-3" data-bs-toggle="modal" data-bs-target="#updateProfileModal">Cambiar Contraseña</button>
            </div>
        </div>

        <!-- Historial de compras con buscador y filtro -->
        <div class="tab-pane fade" id="orders">
            <div class="mb-3 d-flex justify-content-between flex-wrap">
                <input type="text" id="searchProduct" class="form-control mb-2" placeholder="Buscar producto...">
                <input type="month" id="filterDate" class="form-control mb-2" style="max-width:200px;">
            </div>

            <div id="ordersContainer">
                <?php if ($ordersData): ?>
                    <?php foreach ($ordersData as $orderId => $items): 
                        $status = strtolower($items[0]['status']);
                        $statusClass = "bg-secondary text-white"; // default
                        if ($status === "pending") $statusClass = "bg-warning text-dark";
                        elseif ($status === "paid") $statusClass = "bg-success text-white";
                    ?>
                        <div class="card mb-3 shadow-sm order-card" data-date="<?php echo date('Y-m', strtotime($items[0]['created_at'])); ?>">
                            <div class="card-header d-flex justify-content-between <?php echo $statusClass; ?>">
                                <span>Pedido #<?php echo $orderId; ?></span>
                                <span>Total: $<?php echo number_format($items[0]['total'],2); ?> | Estado: <?php echo htmlspecialchars($items[0]['status']); ?></span>
                            </div>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($items as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span class="product-name"><?php echo htmlspecialchars($item['product_name']); ?> x <?php echo $item['quantity']; ?></span>
                                        <span>$<?php echo number_format($item['price'],2); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="card-footer text-muted">
                                Fecha: <?php echo $items[0]['created_at']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning text-dark">No tienes compras realizadas aún.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal moderno de actualización de contraseña -->
<div class="modal fade" id="updateProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 modal-dark-text">
            <form method="POST">
                <input type="hidden" name="update_profile" value="1">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Cambiar Contraseña</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Contraseña actual</label>
                        <input type="password" name="current_password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar nueva contraseña</label>
                        <input type="password" name="confirm_password" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning text-dark w-100">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
<?php if (!empty($successMessage)): ?>
Swal.fire({
    icon: 'success',
    title: '¡Éxito!',
    text: '<?php echo $successMessage; ?>',
    confirmButtonText: 'Aceptar'
}).then(() => window.location.href = 'profile.php');
<?php endif; ?>

<?php if (!empty($errors)): ?>
Swal.fire({
    icon: 'error',
    title: '¡Error!',
    html: '<?php echo implode("<br>", array_map("htmlspecialchars", $errors)); ?>'
});
<?php endif; ?>

// Buscador y filtro
const searchInput = document.getElementById('searchProduct');
const filterDate = document.getElementById('filterDate');
const orderCards = document.querySelectorAll('.order-card');

searchInput.addEventListener('input', function() {
    const term = this.value.toLowerCase();
    orderCards.forEach(card => {
        const productNames = card.querySelectorAll('.product-name');
        let match = false;
        productNames.forEach(p => {
            if (p.textContent.toLowerCase().includes(term)) match = true;
        });
        card.style.display = match ? 'block' : 'none';
    });
});

filterDate.addEventListener('change', function() {
    const selected = this.value;
    orderCards.forEach(card => {
        const date = card.getAttribute('data-date');
        card.style.display = (selected === "" || selected === date) ? 'block' : 'none';
    });
});
</script>

<!-- Estilos personalizados -->
<style>
    /* Texto negro en dark mode para modal */
    body.dark-mode .modal-dark-text {
        color: #000 !important;
    }

    /* Estilos para las pestañas */
    .nav-tabs .nav-link {
        background-color: #fff !important;
        color: #000 !important;
        border: 1px solid #dee2e6;
        transition: background-color 0.3s ease;
    }

    /* Pestaña activa */
    .nav-tabs .nav-link.active {
        background-color: #ffc107 !important; /* Amarillo */
        color: #000 !important; /* Texto negro */
        font-weight: bold;
    }

    /* Título "Mi Perfil" en dark mode */
    body.dark-mode .profile-title {
        color: #ffc107 !important;
    }

    /* En modo claro, el título sigue siendo negro */
    body:not(.dark-mode) .profile-title {
        color: #000 !important;
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
