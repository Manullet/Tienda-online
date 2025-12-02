<?php
/**
 * public/auth/login.php
 * Página de inicio de sesión
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generar token CSRF para el formulario de login
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$successMessage = '';

// Lógica de bloqueo por fuerza bruta
const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_TIME = 900; // 15 minutos en segundos

if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
    if (time() - $_SESSION['last_login_attempt'] < LOCKOUT_TIME) {
        $errors[] = "Has excedido el número de intentos. Por favor, espera 15 minutos.";
    } else {
        // Si ha pasado el tiempo, reseteamos los intentos
        unset($_SESSION['login_attempts']);
        unset($_SESSION['last_login_attempt']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    // Validar el token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = "Error de validación. Inténtalo de nuevo.";
    } else {
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];

        if (empty($email)) $errors[] = "El email es obligatorio.";
        if (empty($password)) $errors[] = "La contraseña es obligatoria.";

        if (empty($errors)) {
            global $pdo;

            $stmt = $pdo->prepare("
                SELECT u.id, u.name, u.email, u.password, u.role_id, r.nombre AS role_nombre
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.email = :email AND u.estado = 1
                LIMIT 1
            ");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Mitigación de ataque de tiempo
            $passwordHash = $user ? $user['password'] : password_hash('dummyPassword', PASSWORD_DEFAULT);

            if ($user && password_verify($password, $passwordHash)) {
                // Éxito: Limpiamos los intentos de login y regeneramos la sesión
                unset($_SESSION['login_attempts']);
                unset($_SESSION['last_login_attempt']);
                unset($_SESSION['csrf_token']); 

                session_regenerate_id(true);

                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role_id']   = $user['role_id'];
                $_SESSION['role_name'] = $user['role_nombre'];

                $successMessage = "¡Bienvenido, {$user['name']}!";
            } else {
                // Fallo: Registramos el intento
                if (!isset($_SESSION['login_attempts'])) {
                    $_SESSION['login_attempts'] = 1;
                } else {
                    $_SESSION['login_attempts']++;
                }
                $_SESSION['last_login_attempt'] = time();

                $errors[] = "Email o contraseña incorrectos.";
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">

<style>
/* Reset básico */
body, html { margin: 0; padding: 0; height: 100%; font-family: 'Montserrat', sans-serif; background-color: #ffffffff; transition: background-color 0.3s ease, color 0.3s ease; }
.login-container { position: relative; display: flex; width: 90%; max-width: 900px; height: 600px; margin: 120px auto; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1); background-color: #ffc107; transition: background-color 0.3s ease, border-color 0.3s ease; }
.login-left { flex: 1; background: url('../assets/images/login-left.jpg') center/cover no-repeat; display: flex; align-items: center; justify-content: center; text-align: center; padding: 30px; transition: background-color 0.3s ease, color 0.3s ease; }
.login-left h1 { font-size: 2.2rem; margin-bottom: 20px; color: #000; }
.login-left p { font-size: 1rem; line-height: 1.5; opacity: 0.9; color: #000; }
.login-right { flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 50px 40px; background-color: #fff; transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease; border-left: 2px solid transparent; }
.login-card { width: 100%; }
.login-card h2 { font-family: 'Roboto', sans-serif; font-weight: 500; color: #333; margin-bottom: 30px; font-size: 1.8rem; transition: color 0.3s ease; }
.login-card input[type="text"], .login-card input[type="email"], .login-card input[type="password"] { width: 100%; padding: 14px 16px; margin-bottom: 20px; border-radius: 12px; border: 1px solid #ccc; background-color: #f9f9f9; font-size: 1rem; transition: all 0.3s ease; }
.login-card input:focus { border-color: #000; box-shadow: 0 0 6px #ffc107; outline: none; }
.login-card input::placeholder { color: #999; }
.login-card button { width: 100%; padding: 14px; border-radius: 15px; border: 2px solid; background-color: #ffc107; color: #fff; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
.login-card button:hover { background-color: #0f0f0e; border-color: #ffc107; }
.login-card .links { margin-top: 20px; font-size: 0.9rem; text-align: center; }
.login-card .links a { color: #000; text-decoration: none; margin: 0 5px; transition: all 0.3s ease; }
.login-card .links a:hover { text-decoration: underline; }
.dark-mode body, .dark-mode html { background-color: #ffffffff; }
.dark-mode .login-container { background-color: #000; border: 0.5px solid #ffc107; }
.dark-mode .login-left { background: none; background-color: #000; color: #ffc107; }
.dark-mode .login-left h1, .dark-mode .login-left p { color: #ffc107; }
.dark-mode .login-right { background-color: #111; border-left: 2px solid #ffc107; color: #fff; }
.dark-mode .login-card h2 { color: #ffc107; }
.dark-mode .login-card input { background-color: #222; border: 1px solid #ffc107; color: #fff; }
.dark-mode .login-card input::placeholder { color: #bbb; }
.dark-mode .login-card button { background-color: #ffc107; color: #000; }
.dark-mode .login-card .links a { color: #ffc107; }

/* Responsivo */
@media (max-width: 768px) {
    .login-left { display: none; }
    .login-container { width: 90%; height: auto; margin: 80px auto; padding: 0; background: none; box-shadow: none; overflow: visible; }
    .login-right { border-radius: 20px; border-left: none; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
    .dark-mode .login-right { border-left: none; border: 0.5px solid #ffc107; }
}
</style>

<div class="login-container" id="loginContainer">
    <div class="login-left">
        <div>
            <h1>Bienvenido de nuevo</h1>
            <p>Inicia sesión para acceder a tu cuenta y disfrutar de todas nuestras funcionalidades de manera rápida y segura.</p>
        </div>
    </div>
    <div class="login-right">
        <div class="login-card">
            <h2>Iniciar Sesión</h2>
            <form action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <input type="email" name="email" placeholder="Correo" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit">Ingresar</button>
            </form>
            <div class="links">
                <a href="<?php echo BASE_URL; ?>auth/register.php">¿No tienes cuenta? Registrate</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// SweetAlert
<?php if (!empty($successMessage)): ?>
Swal.fire({
    icon: 'success',
    title: '¡Éxito!',
    text: <?php echo json_encode($successMessage); ?>,
    showConfirmButton: false,
    timer: 1500
}).then(() => {
    window.location.href = '<?php echo BASE_URL; ?>products/list.php';
});
<?php endif; ?>

<?php if (!empty($errors)): ?>
Swal.fire({
    icon: 'error',
    title: '¡Error!',
    html: <?php echo json_encode(implode("<br>", array_map("htmlspecialchars", $errors))); ?>
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>