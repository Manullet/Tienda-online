<?php
require_once __DIR__ . '/../includes/functions.php';

$cartItems = getCartItems();
$total = getCartTotal();

// Redirigir si el carrito está vacío
if (empty($cartItems)) {
    header("Location: ../cart/view_cart.php");
    exit;
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
/* Formulario */
#cashForm {
    max-width: 600px;
    margin: 0 auto;
}

#cashForm .form-label {
    color: #000; /* Light mode */
    transition: color 0.3s ease;
}

/* Dark mode */
.dark-mode #cashForm .form-label {
    color: #fff; /* Dark mode labels en blanco */
}

#cashForm input.form-control {
    transition: background-color 0.3s ease, color 0.3s ease;
}

/* Mantener fondo blanco en light y gris oscuro en dark */
#cashForm input.form-control {
    background-color: #fff;
    color: #000;
}

.dark-mode #cashForm input.form-control {
    background-color: #111;
    color: #fff;
}

#cashForm button.btn-warning {
    transition: background-color 0.3s ease, color 0.3s ease;
}

.dark-mode #cashForm button.btn-warning {
    background-color: #ffc107;
    color: #000;
    border-color: #ffc107;
}

.dark-mode #cashForm button.btn-warning:hover {
    background-color: #e0ac00;
    border-color: #e0ac00;
}
</style>

<div class="container my-5">
    <h2 class="mb-4 text-center">Pago contra entrega</h2>

    <form id="cashForm">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Teléfono</label>
            <input type="text" name="phone" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Dirección</label>
            <input type="text" name="address" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Ciudad</label>
            <input type="text" name="city" class="form-control" required>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-warning btn-lg text-white">Confirmar Pedido</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('cashForm').addEventListener('submit', function(e){
    e.preventDefault();

    const formData = {
        name: this.name.value,
        phone: this.phone.value,
        address: this.address.value,
        city: this.city.value
    };

    fetch("cash_process.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData)
    })
    .then(res => res.json())
    .then(response => {
        if(response.success){
            Swal.fire({
                icon: 'success',
                title: '¡Pedido confirmado!',
                text: response.message,
                confirmButtonText: 'Volver a la tienda'
            }).then(() => {
                window.location.href = "../products/list.php";
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error en el pedido',
                text: response.message
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Error del servidor',
            text: 'No se pudo procesar tu pedido. Intenta de nuevo.'
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
