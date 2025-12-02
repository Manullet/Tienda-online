<?php
/**
 * includes/footer.php
 * Footer adaptado al diseño de landing page.
 */
?>
</div> <!-- Fin container principal -->

<footer style="background-color: #000; color: hsla(46, 99%, 39%, 0.92); ">
    <style>
        /* ======== Estilos para iconos de redes sociales ======== */
        .social-icon img {
            width: 35px; /* tamaño uniforme */
            height: 35px;
            object-fit: contain;
            border-radius: 50%; /* Opcional: hace que se vean más elegantes */
            transition: transform 0.2s ease-in-out;
        }

        .social-icon img:hover {
            transform: scale(1.1);
        }

        /* Espaciado uniforme entre íconos */
        .social-spacing a {
            margin-right: 15px; /* Ajusta el valor si quieres más o menos espacio */
        }

        /* Elimina el margen en el último ícono para que no se descuadre */
        .social-spacing a:last-child {
            margin-right: 0;
        }
    </style>

    <div class="container-footer">
        <div class="row text-center text-md-start">
            
            <!-- Redes Sociales -->
            <div class="col-md-4 mb-4 mb-md-0 d-flex flex-column align-items-center align-items-md-start">
                <h5 class="text-warning mb-4" style="font-family: 'Playfair Display', serif; font-size: 1rem;">
                    Síguenos en nuestras redes
                </h5>
                <div class="d-flex justify-content-center justify-content-md-start social-spacing">
                    <a href="https://www.tiktok.com/@savorahn" target="_blank" class="social-icon">
                        <img src="https://img.freepik.com/vector-premium/logotipo-redes-sociales-vector-tiktok_1093524-451.jpg?semt=ais_incoming&w=740&q=80" alt="TikTok">
                    </a>
                    <a href="https://www.instagram.com/savora.hn/" target="_blank" class="social-icon">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg" alt="Instagram">
                    </a>
                    <a href="https://www.facebook.com/share/14HhYrpizVk/?mibextid=wwXIfr" target="_blank" class="social-icon">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Logo_de_Facebook.png/1028px-Logo_de_Facebook.png" alt="Facebook">
                    </a>
                </div>
            </div>

            <!-- Métodos de Pago -->
            <div class="col-md-4 mb-4 mb-md-0 text-center text-md-start">
                <h5 class="text-warning mb-4" style="font-family: 'Playfair Display', serif; font-size: 1rem;">
                    Método de pago
                </h5>
                <div class="d-flex flex-column align-items-center align-items-md-start footer-links" style="font-size: 1rem; font-family: Arial, sans-serif;">
                    <a href="#" style="color: #FFD700; text-decoration: none; margin-bottom: 5px;">Términos y Condiciones</a> 
                    <a href="#" style="color: #FFD700; text-decoration: none;">Políticas de Privacidad</a>
                </div>
                <div class="mb-3 mt-3">
                    <img src="https://www.paypalobjects.com/webstatic/icon/pp258.png" alt="PayPal" style="width: 50px;">
                </div>
            </div>

            <!-- Logo -->
            <div class="col-md-4 d-flex flex-column align-items-center justify-content-center">
                <img src="<?php echo BASE_URL; ?>assets/images/logoolargo.png" alt="Logo" style="max-width: 180px;">
            </div>
        </div>
    </div>
</footer>