<?php
/**
 * auth/logout.php
 * 
 * Cierra la sesión del usuario y muestra un SweetAlert2 antes de redirigir.
 */

// Iniciar sesión lo primero
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Logout</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
Swal.fire({
    icon: 'success',
    title: 'Sesión cerrada',
    text: 'Has cerrado sesión correctamente.',
    timer: 2000,
    timerProgressBar: true,
    showConfirmButton: false
}).then(() => {
    window.location.href = 'login.php';
});
</script>
</body>
</html>
