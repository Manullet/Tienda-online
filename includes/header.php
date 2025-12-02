<?php
require_once __DIR__ . '/functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// EVITAR CACHÉ
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Savora</title>

<link rel="icon" href="<?php echo BASE_URL; ?>assets/images/Pestaña.png" type="image/png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">

<style>
/* ====== HEADER FIJO ====== */
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1100;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

/* Fondo transparente al inicio */
.navbar:not(.scrolled) {
    background-color: rgb(2 1 1);
    box-shadow: none;
}

/* Fondo sólido y sombra al hacer scroll */
.navbar.scrolled {
    background-color: rgba(0,0,0,1);
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

/* Ajuste para que el contenido no quede debajo del header */
body {
    padding-top: 70px; /* ajustar según la altura del navbar */
    transition: background-color 0.4s ease, color 0.4s ease;
}

/* ====== ESTILO DEL SWITCH ====== */
.theme-switch-wrapper {
    display: flex;
    align-items: center;
}

.theme-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.theme-switch input { display: none; }

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}

input:checked + .slider { background-color: #ffc107; }
input:checked + .slider:before {
    transform: translateX(26px);
    background-color: #000;
}

.theme-icon {
    font-size: 1rem;
    margin-left: 5px;
    color: #ffc107;
}

/* ====== MODO OSCURO ====== */
html.dark-mode, body.dark-mode {
    background-color: #000 !important;
    color: #fff !important;
}

.dark-mode .navbar { background-color: #000 !important; }
.dark-mode .nav-link { color: #ffc107 !important; }
.dark-mode .navbar-brand img { filter: brightness(1.2); }

/* ====== SWITCH RESPONSIVO ====== */
.switch-mobile-center {
    display: none; /* oculto por defecto */
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    top: 20px;
    align-items: center;
    z-index: 1050;
}

.switch-desktop-right {
    display: flex;
    align-items: center;
}

@media (max-width: 991px) {
    .switch-mobile-center { display: flex; }
    .switch-desktop-right { display: none !important; }
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">
            <img src="<?php echo BASE_URL; ?>assets/images/logoolargo.png" alt="Logo" style="max-height: 50px;">
        </a>

        <!-- Switch móvil (centrado) -->
        <div class="switch-mobile-center">
            <label class="theme-switch">
                <input type="checkbox" id="themeToggleMobile">
                <span class="slider"></span>
            </label>
            <i class="bi bi-brightness-high theme-icon"></i>
        </div>

        <!-- Botón hamburguesa -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menú desplegable -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">

                <!-- Switch escritorio (izquierda de Productos) -->
                <li class="nav-item theme-switch-wrapper me-3 switch-desktop-right">
                    <label class="theme-switch mb-0">
                        <input type="checkbox" id="themeToggle">
                        <span class="slider"></span>
                    </label>
                    <i class="bi bi-brightness-high theme-icon ms-1"></i>
                </li>

                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>products/list.php">Productos</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>cart/view_cart.php">Carrito (<?php echo getCartCount(); ?>)</a></li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role_id'] == 1): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>inventario/inventario.php">Inventario</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>dashboard/top_products.php">Top Productos</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>sales/admin_order_detail.php">Orden</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>auth/profile.php"><?php echo htmlspecialchars($_SESSION['user_name']); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>auth/logout.php">Cerrar sesión</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>auth/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>auth/register.php">Registro</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ====== CAMBIO DE TEMA ======
const themeToggle = document.getElementById('themeToggle');
const themeToggleMobile = document.getElementById('themeToggleMobile');
const htmlElement = document.documentElement;
const bodyElement = document.body;

function setDarkMode(enabled) {
    if(enabled) {
        htmlElement.classList.add('dark-mode');
        bodyElement.classList.add('dark-mode');
        if(themeToggle) themeToggle.checked = true;
        if(themeToggleMobile) themeToggleMobile.checked = true;
        localStorage.setItem('theme', 'dark');
    } else {
        htmlElement.classList.remove('dark-mode');
        bodyElement.classList.remove('dark-mode');
        if(themeToggle) themeToggle.checked = false;
        if(themeToggleMobile) themeToggleMobile.checked = false;
        localStorage.setItem('theme', 'light');
    }
}

// Revisar si hay preferencia guardada
const savedTheme = localStorage.getItem('theme');
if(savedTheme === 'light') {
    setDarkMode(false);
} else {
    setDarkMode(true);
}

// Eventos de cambio de switch
if(themeToggle) themeToggle.addEventListener('change', () => setDarkMode(themeToggle.checked));
if(themeToggleMobile) themeToggleMobile.addEventListener('change', () => setDarkMode(themeToggleMobile.checked));

// ====== SCROLL EFECTO HEADER ======
const navbar = document.querySelector('.navbar');
window.addEventListener('scroll', () => {
    if(window.scrollY > 10) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});
</script>
