<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escuela Deportiva - Inicio</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="img/logo.png" alt="Logo Escuela Deportiva" class="logo">
            <h1>Escuela Deportiva Champions</h1>
        </div>
        <nav>
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="active">Inicio</a></li>
                <li><a href="programas.php">Programas</a></li>
                <li><a href="horarios.php">Horarios</a></li>
                <li><a href="instalaciones.php">Instalaciones</a></li>
                <li><a href="contacto.php">Contacto</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-user"></i> Cuenta <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a></li>
                        <li><a href="registro-deportista.php"><i class="fas fa-user-plus"></i> Registrarse</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h2>Formando campeones para la vida</h2>
            <p>Desarrollamos talento deportivo con valores y disciplina</p>
            <a href="registro.php" class="btn-primary">¡Inscríbete ahora!</a>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2 class="section-title">¿Por qué elegirnos?</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <i class="fas fa-medal feature-icon"></i>
                    <h3>Entrenadores Expertos</h3>
                    <p>Nuestro equipo de entrenadores certificados tiene amplia experiencia en formación deportiva.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-dumbbell feature-icon"></i>
                    <h3>Instalaciones Modernas</h3>
                    <p>Contamos con instalaciones de primer nivel para el desarrollo óptimo de nuestros deportistas.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-heart feature-icon"></i>
                    <h3>Formación Integral</h3>
                    <p>Promovemos valores, disciplina y hábitos saludables además del desarrollo técnico.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-users feature-icon"></i>
                    <h3>Grupos por Categorías</h3>
                    <p>Organizamos los entrenamientos según edad y nivel para un aprendizaje personalizado.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="programs">
        <div class="container">
            <h2 class="section-title">Nuestros Programas</h2>
            <div class="program-slider">
                <div class="program-card">
                    <img src="img/futbol.jpg" alt="Fútbol" class="program-img">
                    <h3>Fútbol</h3>
                    <p>Desarrollo técnico, táctico y físico para todas las edades.</p>
                    <a href="programas.php#futbol" class="btn-secondary">Más información</a>
                </div>
                <div class="program-card">
                    <img src="img/baloncesto.jpg" alt="Baloncesto" class="program-img">
                    <h3>Baloncesto</h3>
                    <p>Entrenamiento completo de habilidades y estrategias de juego.</p>
                    <a href="programas.php#baloncesto" class="btn-secondary">Más información</a>
                </div>
                <div class="program-card">
                    <img src="img/natacion.jpg" alt="Natación" class="program-img">
                    <h3>Natación</h3>
                    <p>Aprende y perfecciona todos los estilos con seguridad.</p>
                    <a href="programas.php#natacion" class="btn-secondary">Más información</a>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials">
        <div class="container">
            <h2 class="section-title">Testimonios</h2>
            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"La escuela ha transformado a mi hijo. Ha mejorado su condición física y su autoestima."</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="img/testimonial1.jpg" alt="Testimonio" class="testimonial-img">
                        <div>
                            <h4>María Rodríguez</h4>
                            <p>Madre de deportista</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"Los entrenadores son excelentes profesionales. Mi hija ha progresado muchísimo en poco tiempo."</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="img/testimonial2.jpg" alt="Testimonio" class="testimonial-img">
                        <div>
                            <h4>Carlos Gómez</h4>
                            <p>Padre de deportista</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="container">
            <h2>¿Listo para unirte a nuestra comunidad?</h2>
            <p>Inscríbete ahora y comienza tu camino hacia la excelencia deportiva</p>
            <a href="registro.php" class="btn-primary">¡Inscríbete ya!</a>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-info">
                    <img src="img/logo.png" alt="Logo Escuela Deportiva" class="footer-logo">
                    <p>Escuela Deportiva Champions: formamos atletas y mejores personas</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
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
                    </ul>
                </div>
                <div class="footer-newsletter">
                    <h3>Boletín informativo</h3>
                    <p>Suscríbete para recibir noticias y promociones</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Tu correo electrónico" required>
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Escuela Deportiva Champions. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
