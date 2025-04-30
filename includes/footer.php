<footer>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-info">
                <img src="img/logo.png" alt="Logo Escuela Deportiva" class="footer-logo">
                <p>Escuela Deportiva Champions: formamos atletas y mejores personas</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-contact">
                <h3>Contacto</h3>
                <p><i class="fas fa-map-marker-alt"></i> Av. Principal #123, Ciudad</p>
                <p><i class="fas fa-phone"></i> +123 456 789</p>
                <p><i class="fas fa-envelope"></i> info@escueladeportiva.com</p>
            </div>
            <div class="footer-links">
                <h3>Enlaces rápidos</h3>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="programas.php">Programas</a></li>
                    <li><a href="horarios.php">Horarios</a></li>
                    <li><a href="instalaciones.php">Instalaciones</a></li>
                    <li><a href="contacto.php">Contacto</a></li>
                    <li><a href="registro_acudiente.php">Inscripciones</a></li>
                </ul>
            </div>
            <div class="footer-newsletter">
                <h3>Boletín informativo</h3>
                <p>Suscríbete para recibir noticias y promociones</p>
                <form class="newsletter-form" action="suscribir_boletin.php" method="POST">
                    <input type="email" name="email" placeholder="Tu correo electrónico" required>
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Escuela Deportiva Champions. Todos los derechos reservados.</p>
            <ul class="footer-legal">
                <li><a href="privacidad.php">Política de Privacidad</a></li>
                <li><a href="terminos.php">Términos y Condiciones</a></li>
            </ul>
        </div>
    </div>
</footer>

<!-- Scripts JS -->
<script src="js/main.js"></script>
<?php if(isset($custom_scripts) && !empty($custom_scripts)): ?>
<?php foreach($custom_scripts as $script): ?>
<script src="<?php echo $script; ?>"></script>
<?php endforeach; ?>
<?php endif; ?>