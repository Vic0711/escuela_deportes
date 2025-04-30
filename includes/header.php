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
            <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>Inicio</a></li>
            <li><a href="programas.php" <?php echo basename($_SERVER['PHP_SELF']) == 'programas.php' ? 'class="active"' : ''; ?>>Programas</a></li>
            <li><a href="horarios.php" <?php echo basename($_SERVER['PHP_SELF']) == 'horarios.php' ? 'class="active"' : ''; ?>>Horarios</a></li>
            <li><a href="instalaciones.php" <?php echo basename($_SERVER['PHP_SELF']) == 'instalaciones.php' ? 'class="active"' : ''; ?>>Instalaciones</a></li>
            <li><a href="contacto.php" <?php echo basename($_SERVER['PHP_SELF']) == 'contacto.php' ? 'class="active"' : ''; ?>>Contacto</a></li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">
                    <i class="fas fa-user"></i> Cuenta <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <li><a href="panel_deportista.php"><i class="fas fa-tachometer-alt"></i> Mi Panel</a></li>
                        <li><a href="cerrar_sesion.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a></li>
                        <li><a href="registro_acudiente.php"><i class="fas fa-user-plus"></i> Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php if(isset($_SESSION['es_admin']) && $_SESSION['es_admin']): ?>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle admin-link">
                    <i class="fas fa-cogs"></i> Administración <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="admin/deportistas.php"><i class="fas fa-users"></i> Deportistas</a></li>
                    <li><a href="admin/acudientes.php"><i class="fas fa-user-friends"></i> Acudientes</a></li>
                    <li><a href="admin/entrenadores.php"><i class="fas fa-user-tie"></i> Entrenadores</a></li>
                    <li><a href="admin/categorias.php"><i class="fas fa-list"></i> Categorías</a></li>
                    <li><a href="admin/matriculas.php"><i class="fas fa-clipboard-list"></i> Matrículas</a></li>
                    <li><a href="admin/pagos.php"><i class="fas fa-money-bill-wave"></i> Pagos</a></li>
                    <li><a href="admin/asistencia.php"><i class="fas fa-clipboard-check"></i> Asistencia</a></li>
                </ul>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>