<?php
/**
 * config/config.php
 * Configuración general para producción (GoDaddy)
 */

// =======================
// CONFIGURACIÓN BASE
// =======================
define('BASE_URL', '/'); // Como está en la raíz del dominio
define('BASE_DIR', __DIR__ . '/../'); // apunta a la raíz del proyecto

// =======================
// CONFIGURACIÓN BASE DE DATOS
// =======================
define('DB_HOST', 'localhost');      // GoDaddy usa localhost para MySQL
define('DB_NAME', 'mi_tienda');      // Ajusta al nombre real de tu base de datos en GoDaddy
define('DB_USER', 'savora');         // Usuario MySQL en GoDaddy
define('DB_PASS', 'Haruzuzumiya2001'); // Contraseña de MySQL en GoDaddy

// =======================
// INICIO DE SESIÓN GLOBAL
// =======================
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// =======================
// CONEXIÓN A BASE DE DATOS
// =======================
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => true
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die("<h1>Error de conexión a la base de datos</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>");
}

// =======================
// CONFIGURACIÓN PAYPAL
// =======================
define('PAYPAL_CLIENT_ID', 'AeWUPrsBM8rKbDGVw9_6kwH8SOjIomzhrLWtvQJ2CaRENkDD1nJe5rladZGxOlRM9ZOS63mgh0GNyxkk');
define('PAYPAL_SECRET', 'EG6VLXB_ZqSpTRalAoTKzv6IORZrynHzGkc4zGYQrkmXKnUGgec6_XYXyhSNtd2lPfJJVhK_mQn9TocO');
define('PAYPAL_MODE', 'live'); // 'sandbox' o 'live'

