<?php
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireAdmin();
global $pdo;

$errors = [];
$success = false;

$name = $description = $price = $stock = '';
$image = ''; // Imagen seleccionada

// Carpeta de imágenes absoluta
$imagesFolder = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/';

// Obtener imágenes disponibles
$availableImages = [];
if (is_dir($imagesFolder)) {
    $allFiles = scandir($imagesFolder);
    $allowedExt = ['jpg','jpeg','png','gif','webp'];
    foreach ($allFiles as $file) {
        if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $allowedExt)) {
            $availableImages[] = $file;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = !empty($_POST['image_select']) ? sanitize($_POST['image_select']) : '';

    // Validaciones
    if (empty($name)) $errors[] = "El nombre es obligatorio.";
    if (!is_numeric($price) || $price < 0) $errors[] = "Precio inválido.";
    if (!is_numeric($stock) || $stock < 0) $errors[] = "Stock inválido.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, image) 
                               VALUES (:name, :description, :price, :stock, :image)");
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'image' => $image
        ]);
        $success = true;

        // Limpiar campos
        $name = $description = $price = $stock = $image = '';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5 border-container">
    <h2 class="text-center mb-4">Agregar Producto</h2>

    <?php if ($success): ?>
        <div class="alert alert-success text-center">Producto agregado correctamente.</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach($errors as $err) echo "<p>$err</p>"; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="product-form shadow-sm p-4 rounded">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($description) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Precio</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($price) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" name="stock" class="form-control" value="<?= htmlspecialchars($stock) ?>" required>
        </div>

        <!-- Selector de imágenes -->
        <div class="mb-3">
            <label class="form-label">Seleccionar Imagen del Producto</label>
            <div class="image-slider">
                <?php if (!empty($availableImages)): ?>
                    <?php foreach ($availableImages as $img): 
                        $checked = ($image == $img) ? 'checked' : '';
                    ?>
                        <label class="image-option <?= $checked ? 'selected' : '' ?>">
                            <input type="radio" name="image_select" value="<?= $img ?>" <?= $checked ?>>
                            <img src="<?= BASE_URL ?>assets/images/<?= $img ?>" alt="<?= $img ?>">
                        </label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:#FFD700;">No hay imágenes disponibles en la carpeta assets/images/</p>
                <?php endif; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Producto</button>
        <a href="inventario.php" class="btn btn-secondary ms-2">Volver al Inventario</a>
    </form>
</div>

<style>
body.dark-mode, .ml.dark-mode {
    background-color: #000 !important;
    color: #f0f0f5;
}

.border-container {
    border: 2px solid #FFD700;
    padding: 2rem;
    border-radius: 15px;
    max-width: 600px;
    margin: 2rem auto;
}

.product-form {
    max-width: 600px;
    margin: 0 auto;
    background-color: #000;
    padding: 2rem;
    border-radius: 15px;
    color: #FFD700;
}

.form-label { font-weight: bold; color: #FFD700; }
.form-control, .form-select {
    background-color: #111;
    border: 1px solid #FFD700;
    color: #f0f0f5;
    border-radius: 10px;
}
.form-control:focus, .form-select:focus {
    border-color: #FFD700;
    box-shadow: 0 0 5px rgba(255,215,0,0.5);
    outline: none;
}

.btn-primary { 
    background-color: #FFD700; border-color: #FFD700; color: #000; border-radius: 10px; padding: 0.5rem 1.2rem; transition: all 0.3s;
}
.btn-primary:hover { background-color: #e6c200; transform: scale(1.02); }

.btn-secondary { 
    background-color: #00aaff; border-color: #00aaff; color: #000; border-radius: 10px; padding: 0.5rem 1.2rem; transition: all 0.3s;
}
.btn-secondary:hover { background-color: #008ecc; color: #fff; transform: scale(1.02); }

.image-slider {
    display:flex;
    overflow-x:auto;
    gap:10px;
    padding:5px 0;
    scrollbar-width: thin;
    scrollbar-color: #FFD700 transparent;
}
.image-slider::-webkit-scrollbar { height:8px; }
.image-slider::-webkit-scrollbar-thumb { background:#FFD700; border-radius:4px; }
.image-option { flex:0 0 auto; display:flex; flex-direction:column; align-items:center; cursor:pointer; border:2px solid transparent; border-radius:10px; padding:5px; transition: all 0.3s; }
.image-option img { width:80px; height:80px; object-fit:cover; border-radius:10px; }
.image-option input { display:none; }
.image-option.selected { border:2px solid #FFD700; box-shadow:0 0 10px #FFD700; }

@media (max-width: 768px) {
    .product-form { padding: 1.5rem; }
    .image-option img { width:60px; height:60px; }
    .btn-primary, .btn-secondary { width:100%; margin-bottom:0.5rem; }
}

@media (max-width: 480px) {
    .product-form { padding: 1rem; }
    .image-option img { width:50px; height:50px; }
}
</style>

<script>
document.querySelectorAll('.image-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function(){
        document.querySelectorAll('.image-option').forEach(l => l.classList.remove('selected'));
        if(this.checked) this.parentElement.classList.add('selected');
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
