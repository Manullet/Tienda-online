<?php
require_once __DIR__ . '/../includes/functions.php';
session_start();

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
global $pdo;

// Obtener datos actuales del usuario
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($name)) $errors[] = "El nombre es obligatorio.";

    // Validar cambio de contraseña solo si se ha llenado el campo
    if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        if (empty($currentPassword)) $errors[] = "Ingresa tu contraseña actual.";
        if (empty($newPassword)) $errors[] = "Ingresa la nueva contraseña.";
        if ($newPassword !== $confirmPassword) $errors[] = "Las nuevas contraseñas no coinciden.";

        // Verificar contraseña actual
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $hashedPassword = $stmt->fetchColumn();

        if (!password_verify($currentPassword, $hashedPassword)) {
            $errors[] = "La contraseña actual es incorrecta.";
        }
    }

    // Si no hay errores, actualizar datos
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE users SET name = :name WHERE id = :id");
            $stmt->execute(['name' => $name, 'id' => $userId]);

            if (!empty($newPassword)) {
                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $stmt->execute(['password' => password_hash($newPassword, PASSWORD_DEFAULT), 'id' => $userId]);
            }

            $pdo->commit();
            $_SESSION['user_name'] = $name;
            $successMessage = "Perfil actualizado correctamente.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Error al actualizar perfil: " . $e->getMessage();
        }
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4 text-center">Actualizar Perfil</h2>

    <form action="" method="POST" style="max-width: 500px; margin:auto;">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
        </div>

        <hr>
        <h5>Cambiar contraseña</h5>
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

        <button type="submit" class="btn btn-success w-100">Actualizar Perfil</button>
    </form>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
<?php if (!empty($successMessage)): ?>
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: '<?php echo $successMessage; ?>',
        confirmButtonText: 'Aceptar'
    }).then(() => {
        window.location.href = 'profile.php';
    });
<?php endif; ?>

<?php if (!empty($errors)): ?>
    Swal.fire({
        icon: 'error',
        title: '¡Error!',
        html: '<?php echo implode("<br>", array_map("htmlspecialchars", $errors)); ?>'
    });
<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
