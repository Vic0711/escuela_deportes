<?php
// Iniciar sesión
session_start();

// Si ya hay una sesión activa, redirigir al panel correspondiente
if (isset($_SESSION['usuario_id'])) {
    // Redirigir según el rol
    switch ($_SESSION['rol']) {
        case 'administrador':
            header("Location: admin/dashboard.php");
            break;
        case 'entrenador':
            header("Location: entrenador/dashboard.php");
            break;
        case 'deportista':
            header("Location: deportista/dashboard.php");
            break;
        case 'acudiente':
            header("Location: acudiente/dashboard.php");
            break;
        default:
            header("Location: index.php");
    }
    exit();
}

// Incluir archivo de conexión a la base de datos
require_once 'config/db.php';

// Variable para mensajes
$mensaje = '';

// Procesar formulario de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $documento = trim($_POST['documento']);
    $contrasena = $_POST['contrasena'];
    
    // Validar que no estén vacíos
    if (empty($documento) || empty($contrasena)) {
        $mensaje = '<div class="alert alert-danger">Por favor, complete todos los campos.</div>';
    } else {
        // Verificar en tablas existentes
        $usuario_encontrado = false;
        $tipo_usuario = '';
        $rol = '';
        $id_usuario = 0;
        $nombre_completo = '';
        
        // Verificar qué tablas existen
        $tablas_existentes = [];
        $check_tables = $conn->query("SHOW TABLES");
        while ($row = $check_tables->fetch_array()) {
            $tablas_existentes[] = $row[0];
        }
        
        // Buscar en tabla deportista
        if (in_array('deportista', $tablas_existentes)) {
            $stmt = $conn->prepare("SELECT id_deportista, numero_documento, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, contrasena, rol, estado FROM deportista WHERE numero_documento = ?");
            $stmt->bind_param("s", $documento);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $usuario = $result->fetch_assoc();
                $usuario_encontrado = true;
                $tipo_usuario = 'deportista';
                $id_usuario = $usuario['id_deportista'];
                $rol = isset($usuario['rol']) ? $usuario['rol'] : 'deportista';
                $nombre_completo = $usuario['primer_nombre'] . ' ' . $usuario['primer_apellido'];
                
                // Verificar contraseña
                if (!isset($usuario['contrasena']) || empty($usuario['contrasena'])) {
                    // Si no tiene contraseña, verificar si la contraseña ingresada es igual al número de documento (primer ingreso)
                    if ($contrasena === $documento) {
                        // Establecer una contraseña por defecto (igual al documento por ahora)
                        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                        $update_stmt = $conn->prepare("UPDATE deportista SET contrasena = ? WHERE id_deportista = ?");
                        $update_stmt->bind_param("si", $contrasena_hash, $id_usuario);
                        $update_stmt->execute();
                        
                        // Marcar como primer ingreso para cambiar contraseña
                        $_SESSION['primer_ingreso'] = true;
                    } else {
                        $mensaje = '<div class="alert alert-danger">Contraseña incorrecta. Para el primer ingreso, utilice su número de documento.</div>';
                        $usuario_encontrado = false;
                    }
                } else {
                    // Verificar si la contraseña coincide
                    if (password_verify($contrasena, $usuario['contrasena']) || $contrasena === $usuario['contrasena']) {
                        // Si la contraseña está en texto plano, actualizarla a hash
                        if ($contrasena === $usuario['contrasena']) {
                            $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                            $update_stmt = $conn->prepare("UPDATE deportista SET contrasena = ? WHERE id_deportista = ?");
                            $update_stmt->bind_param("si", $contrasena_hash, $id_usuario);
                            $update_stmt->execute();
                        }
                    } else {
                        $mensaje = '<div class="alert alert-danger">Contraseña incorrecta.</div>';
                        $usuario_encontrado = false;
                    }
                }
                
                // Verificar si el estado es activo
                if ($usuario_encontrado && isset($usuario['estado']) && $usuario['estado'] != 'activo') {
                    $mensaje = '<div class="alert alert-danger">Su cuenta está inactiva. Contacte al administrador.</div>';
                    $usuario_encontrado = false;
                }
            }
        }
        
        // Si no se encontró en deportista, buscar en padre (acudiente)
        if (!$usuario_encontrado && in_array('padre', $tablas_existentes)) {
            $stmt = $conn->prepare("SELECT id_padre, numero_documento, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, contrasena, rol, estado FROM padre WHERE numero_documento = ?");
            $stmt->bind_param("s", $documento);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $usuario = $result->fetch_assoc();
                $usuario_encontrado = true;
                $tipo_usuario = 'padre';
                $id_usuario = $usuario['id_padre'];
                $rol = isset($usuario['rol']) ? $usuario['rol'] : 'acudiente';
                $nombre_completo = $usuario['primer_nombre'] . ' ' . $usuario['primer_apellido'];
                
                // Verificar contraseña
                if (!isset($usuario['contrasena']) || empty($usuario['contrasena'])) {
                    // Si no tiene contraseña, verificar si la contraseña ingresada es igual al número de documento (primer ingreso)
                    if ($contrasena === $documento) {
                        // Establecer una contraseña por defecto (igual al documento por ahora)
                        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                        $update_stmt = $conn->prepare("UPDATE padre SET contrasena = ? WHERE id_padre = ?");
                        $update_stmt->bind_param("si", $contrasena_hash, $id_usuario);
                        $update_stmt->execute();
                        
                        // Marcar como primer ingreso para cambiar contraseña
                        $_SESSION['primer_ingreso'] = true;
                    } else {
                        $mensaje = '<div class="alert alert-danger">Contraseña incorrecta. Para el primer ingreso, utilice su número de documento.</div>';
                        $usuario_encontrado = false;
                    }
                } else {
                    // Verificar si la contraseña coincide
                    if (password_verify($contrasena, $usuario['contrasena']) || $contrasena === $usuario['contrasena']) {
                        // Si la contraseña está en texto plano, actualizarla a hash
                        if ($contrasena === $usuario['contrasena']) {
                            $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                            $update_stmt = $conn->prepare("UPDATE padre SET contrasena = ? WHERE id_padre = ?");
                            $update_stmt->bind_param("si", $contrasena_hash, $id_usuario);
                            $update_stmt->execute();
                        }
                    } else {
                        $mensaje = '<div class="alert alert-danger">Contraseña incorrecta.</div>';
                        $usuario_encontrado = false;
                    }
                }
                
                // Verificar si el estado es activo
                if ($usuario_encontrado && isset($usuario['estado']) && $usuario['estado'] != 'activo') {
                    $mensaje = '<div class="alert alert-danger">Su cuenta está inactiva. Contacte al administrador.</div>';
                    $usuario_encontrado = false;
                }
            }
        }
        
        // Finalmente, buscar en el instructor (entrenador)
        if (!$usuario_encontrado && in_array('instructor', $tablas_existentes)) {
            $stmt = $conn->prepare("SELECT id_instructor, numero_documento, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, contrasena, rol, estado FROM instructor WHERE numero_documento = ?");
            $stmt->bind_param("s", $documento);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $usuario = $result->fetch_assoc();
                $usuario_encontrado = true;
                $tipo_usuario = 'instructor';
                $id_usuario = $usuario['id_instructor'];
                $rol = isset($usuario['rol']) ? $usuario['rol'] : 'entrenador';
                $nombre_completo = $usuario['primer_nombre'] . ' ' . $usuario['primer_apellido'];
                
                // Verificar contraseña
                if (!isset($usuario['contrasena']) || empty($usuario['contrasena'])) {
                    // Si no tiene contraseña, verificar si la contraseña ingresada es igual al número de documento (primer ingreso)
                    if ($contrasena === $documento) {
                        // Establecer una contraseña por defecto (igual al documento por ahora)
                        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                        $update_stmt = $conn->prepare("UPDATE instructor SET contrasena = ? WHERE id_instructor = ?");
                        $update_stmt->bind_param("si", $contrasena_hash, $id_usuario);
                        $update_stmt->execute();
                        
                        // Marcar como primer ingreso para cambiar contraseña
                        $_SESSION['primer_ingreso'] = true;
                    } else {
                        $mensaje = '<div class="alert alert-danger">Contraseña incorrecta. Para el primer ingreso, utilice su número de documento.</div>';
                        $usuario_encontrado = false;
                    }
                } else {
                    // Verificar si la contraseña coincide
                    if (password_verify($contrasena, $usuario['contrasena']) || $contrasena === $usuario['contrasena']) {
                        // Si la contraseña está en texto plano, actualizarla a hash
                        if ($contrasena === $usuario['contrasena']) {
                            $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                            $update_stmt = $conn->prepare("UPDATE instructor SET contrasena = ? WHERE id_instructor = ?");
                            $update_stmt->bind_param("si", $contrasena_hash, $id_usuario);
                            $update_stmt->execute();
                        }
                    } else {
                        $mensaje = '<div class="alert alert-danger">Contraseña incorrecta.</div>';
                        $usuario_encontrado = false;
                    }
                }
                
                // Verificar si el estado es activo
                if ($usuario_encontrado && isset($usuario['estado']) && $usuario['estado'] != 'activo') {
                    $mensaje = '<div class="alert alert-danger">Su cuenta está inactiva. Contacte al administrador.</div>';
                    $usuario_encontrado = false;
                }
            }
        }
        
        // Hardcodear un usuario administrador si no existe aún
        if (!$usuario_encontrado && $documento === "admin" && $contrasena === "admin123") {
            $usuario_encontrado = true;
            $tipo_usuario = 'instructor';
            $id_usuario = 1; // ID arbitrario para el administrador
            $rol = 'administrador';
            $nombre_completo = 'Administrador del Sistema';
            
            // Verificar si existe la tabla instructor
            if (in_array('instructor', $tablas_existentes)) {
                // Verificar si ya existe un administrador con id 1
                $admin_check = $conn->prepare("SELECT id_instructor FROM instructor WHERE id_instructor = 1");
                $admin_check->execute();
                $admin_result = $admin_check->get_result();
                
                if ($admin_result->num_rows === 0) {
                    // Crear el usuario administrador
                    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                    $estado = 'activo';
                    $correo = 'admin@escueladeportiva.com';
                    
                    $insert_admin = $conn->prepare("INSERT INTO instructor (id_instructor, numero_documento, primer_nombre, primer_apellido, rol, estado, contrasena, correo_electronico) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $insert_admin->bind_param("isssssss", 
                        $id_usuario, 
                        $documento, 
                        'Administrador', 
                        'Sistema', 
                        $rol, 
                        $estado, 
                        $contrasena_hash, 
                        $correo
                    );
                    $insert_admin->execute();
                }
            }
        }
        
        // Si se encontró el usuario y las credenciales son correctas, iniciar sesión
        if ($usuario_encontrado) {
            $_SESSION['usuario_id'] = $id_usuario;
            $_SESSION['documento'] = $documento;
            $_SESSION['rol'] = $rol;
            $_SESSION['tipo_usuario'] = $tipo_usuario;
            $_SESSION['nombre_completo'] = $nombre_completo;
            
            // Si es primer ingreso, redirigir a cambiar contraseña
            if (isset($_SESSION['primer_ingreso']) && $_SESSION['primer_ingreso'] === true) {
                header("Location: cambiar-contrasena.php");
                exit();
            }
            
            // Redireccionar según el rol
            switch ($rol) {
                case 'administrador':
                    header("Location: admin/dashboard.php");
                    break;
                case 'entrenador':
                    header("Location: entrenador/dashboard.php");
                    break;
                case 'deportista':
                    header("Location: deportista/dashboard.php");
                    break;
                case 'acudiente':
                    header("Location: acudiente/dashboard.php");
                    break;
                default:
                    header("Location: index.php");
            }
            exit();
        } else if (empty($mensaje)) {
            $mensaje = '<div class="alert alert-danger">Usuario no encontrado o credenciales incorrectas.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Escuela Deportiva</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('img/background-login.jpg') no-repeat center center;
            background-size: cover;
            padding: 20px;
        }
        
        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            padding: 40px;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo img {
            max-width: 150px;
            height: auto;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: var(--gray-color);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon input {
            padding-left: 45px;
            width: 100%;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-color);
        }
        
        .forgot-password {
            text-align: right;
            margin-top: -15px;
            margin-bottom: 20px;
        }
        
        .forgot-password a {
            color: var(--primary-color);
            font-size: 14px;
            text-decoration: none;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background-color: var(--primary-color);
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-login:hover {
            background-color: #0055aa;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 30px;
            color: var(--gray-color);
            font-size: 14px;
        }
        
        .back-home {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-home:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-logo">
                <img src="img/logo.png" alt="Logo Escuela Deportiva">
            </div>
            
            <div class="login-header">
                <h2>Iniciar Sesión</h2>
                <p>Ingrese sus credenciales para acceder al sistema</p>
            </div>
            
            <?php echo $mensaje; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="documento">Número de Documento</label>
                    <div class="input-with-icon">
                        <i class="fas fa-id-card"></i>
                        <input type="text" id="documento" name="documento" placeholder="Ingrese su número de documento" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="contrasena">Contraseña</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="contrasena" name="contrasena" placeholder="Ingrese su contraseña" required>
                    </div>
                </div>
                
                <div class="forgot-password">
                    <a href="recuperar-contrasena.php">¿Olvidó su contraseña?</a>
                </div>
                
                <button type="submit" class="btn-login">Iniciar Sesión</button>
            </form>

            <div class="alert alert-info" style="margin-top: 20px;">
                <p><i class="fas fa-info-circle"></i> Si es la primera vez que accede al sistema:</p>
                <ul>
                    <li><strong>Usuario:</strong> Su número de documento de identidad</li>
                    <li><strong>Contraseña:</strong> Su número de documento de identidad</li>
                </ul>
                <p>Por seguridad, le recomendamos cambiar su contraseña después del primer inicio de sesión.</p>
            </div>
            
            <div class="login-footer">
                <p>&copy; <?php echo date('Y'); ?> Escuela Deportiva Champions. Todos los derechos reservados.</p>
            </div>
            
            <a href="index.php" class="back-home">
                <i class="fas fa-arrow-left"></i> Volver a la página principal
            </a>
        </div>
    </div>
</body>
</html>