<?php
require_once __DIR__ . '/../includes/functions.php';

// Iniciar sesión para manejar el token CSRF
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar el token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = "Error de validación de seguridad. Por favor, recargue la página e intente de nuevo.";
    } else {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validación del nombre
        if (empty($name)) {
            $errors[] = "El nombre es obligatorio.";
        } elseif (strlen($name) > 100) { // --- AJUSTE: Coincide con varchar(100) ---
            $errors[] = "El nombre es demasiado largo (máximo 100 caracteres).";
        }

        // Validación del email
        if (empty($email)) {
            $errors[] = "El email es obligatorio.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email no es válido.";
        }

        // Validación de complejidad de contraseña
        if (empty($password)) {
            $errors[] = "La contraseña es obligatoria.";
        } elseif (strlen($password) < 8) {
            $errors[] = "La contraseña debe tener al menos 8 caracteres.";
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra mayúscula.";
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra minúscula.";
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un número.";
        } elseif ($password !== $confirm_password) {
            $errors[] = "Las contraseñas no coinciden.";
        }

        if (empty($errors)) {
            global $pdo;
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                $errors[] = "El email ya está registrado.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // --- AJUSTE: Se incluye role_id en la inserción ---
                $stmt = $pdo->prepare(
                    "INSERT INTO users (name, email, password, role_id) VALUES (:name, :email, :password, :role_id)"
                );
                $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'role_id' => 2 // Asignamos el rol de "Cliente". Cambia este ID si es diferente.
                ]);
                
                unset($_SESSION['csrf_token']);

                $successMessage = "¡Registro exitoso! Tu cuenta ha sido creada correctamente.";
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="login-container">
    <div class="card login-card p-5">
        <h3 class="card-title text-center mb-4">Registrarse</h3>

        <form action="" method="POST" class="login-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="form-group">
                <input type="text" class="form-input" name="name" id="name"
                       value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" placeholder=" " required>
                <label for="name">Nombre</label>
            </div>
            <div class="form-group">
                <input type="email" class="form-input" name="email" id="email"
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" placeholder=" " required>
                <label for="email">Correo electrónico</label>
            </div>
            <div class="form-group">
                <input type="password" class="form-input" name="password" id="password" placeholder=" " required>
                <label for="password">Contraseña</label>
            </div>
            <div class="form-group">
                <input type="password" class="form-input" name="confirm_password" id="confirm_password" placeholder=" " required>
                <label for="confirm_password">Confirmar Contraseña</label>
            </div>
            <button type="submit" class="btn-login">Registrarse</button>
        </form>

        <p class="text-center mt-3">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
        </p>
    </div>
</div>

<style>
/* Tu CSS aquí */
.login-container { margin-top: 120px; margin-bottom: 50px; min-height: auto; display: flex; justify-content: center; align-items: center; padding: 20px; }
.login-card { width: 100%; max-width: 420px; padding: 50px 35px; background: #fff; border-radius: 15px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 0.5px solid #000; }
.login-card h3 { font-weight: 500; font-size: 1.9rem; margin-bottom: 35px; color: #333; }
.form-group { position: relative; margin-bottom: 25px; }
.form-input { width: 100%; padding: 14px; border: 1px solid #ccc; border-radius: 12px; background-color: #f9f9f9; font-size: 1rem; outline: none; transition: all 0.3s ease; }
.form-input:focus { border-color: #000; box-shadow: 0 0 6px #ffc107; }
.form-group label { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #999; font-size: 1rem; pointer-events: none; transition: all 0.3s ease; }
.form-input:focus + label, .form-input:not(:placeholder-shown) + label { top: -10px; font-size: 0.8rem; color: #ffc107; background: #fff; padding: 0 4px; }
.btn-login { width: 100%; padding: 14px; border-radius: 15px; border: 2px solid #000; background-color: #ffc107; color: #000; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
.btn-login:hover { background-color: #0f0f0e; border-color: #ffc107; color: #ffc107; }
.login-card p a { color: #ffc107; text-decoration: none; font-weight: 500; }
.login-card p a:hover { text-decoration: underline; }
.dark-mode .login-card h3 { color: #fff; }
.dark-mode .login-card { background-color: #111; color: #fff; border: 0.5px solid #ffc107; }
.dark-mode .form-input { background-color: #222; border: 1px solid #ffc107; color: #fff; }
.dark-mode .form-input:focus + label, .dark-mode .form-input:not(:placeholder-shown) + label { background: #111; color: #ffc107; }
.dark-mode .btn-login { background-color: #ffc107; color: #000; }
.dark-mode p { color: #ffc107; }
@media (max-width: 768px) { .login-container { margin-top: 90px; } }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
<?php if (!empty($successMessage)): ?>
Swal.fire({ icon: 'success', title: '¡Éxito!', text: <?php echo json_encode($successMessage); ?>, confirmButtonText: 'Iniciar sesión' }).then(() => { window.location.href = 'login.php'; });
<?php endif; ?>
<?php if (!empty($errors)): ?>
Swal.fire({ icon: 'error', title: '¡Error!', html: <?php echo json_encode(implode("<br>", array_map("htmlspecialchars", $errors))); ?> });
<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>