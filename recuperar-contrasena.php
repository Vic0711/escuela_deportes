<?php
// Iniciar sesión
session_start();

// Si ya hay una sesión activa, redirigir al panel correspondiente
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Incluir archivo de conexión a la base de datos
require_once 'config/db.php';

// Variable para mensajes
$mensaje = '';
$paso = 1; // Paso 1: Solicitar correo, Paso 2: Verificar documento, Paso 3: Mostrar contraseña por defecto

// Procesar solicitud de recuperación
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['paso']) && $_POST['paso'] == '1') {
        $correo = trim($_POST['correo']);
        
        if (empty($correo)) {
            $mensaje = '<div class="alert alert-danger">Por favor, ingrese su correo electrónico.</div>';
        } else {
            // Buscar usuario por correo
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ? AND estado = 'activo'");
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Depuración: Verificar si se encuentra el correo
            if ($result->num_rows === 0) {
                $mensaje = '<div class="alert alert-danger">No se encontró ninguna cuenta asociada a este correo electrónico.</div>';
                // Añadir información de depuración
                error_log("Recuperación de contraseña: No se encontró el correo $correo");
            } else {
                $usuario_data = $result->fetch_assoc();
                
                // Guardar información en sesión para el siguiente paso
                $_SESSION['recuperacion_id'] = $usuario_data['id_usuario'];
                $_SESSION['recuperacion_tipo'] = $usuario_data['tipo_persona'];
                $_SESSION['recuperacion_id_persona'] = $usuario_data['id_persona'];
                $_SESSION['recuperacion_correo'] = $correo;
                
                // Avanzar al paso 2
                $paso = 2;
            }
        }
    } elseif (isset($_POST['paso']) && $_POST['paso'] == '2') {
        $documento = trim($_POST['documento']);
        
        if (empty($documento)) {
            $mensaje = '<div class="alert alert-danger">Por favor, ingrese su número de documento.</div>';
        } else {
            // Verificar el documento según el tipo de persona
            $tabla = '';
            $campo_id = '';
            
            switch ($_SESSION['recuperacion_tipo']) {
                case 'deportista':
                    $tabla = 'deportista';
                    $campo_id = 'id_deportista';
                    break;
                case 'padre':
                    $tabla = 'padre';
                    $campo_id = 'id_padre';
                    break;
                case 'entrenador':
                    $tabla = 'entrenador';
                    $campo_id = 'id_entrenador';
                    break;
            }
            
            if (!empty($tabla)) {
                $query = "SELECT numero_documento, correo_electronico FROM $tabla WHERE $campo_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $_SESSION['recuperacion_id_persona']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $persona_data = $result->fetch_assoc();
                    
                    if ($persona_data['numero_documento'] === $documento) {
                        // Documento verificado, mostrar contraseña por defecto
                        $contrasena_default = $documento . $persona_data['correo_electronico'];
                        
                        // Guardar en sesión para el paso 3
                        $_SESSION['recuperacion_pwd'] = $contrasena_default;
                        
                        // Avanzar al paso 3
                        $paso = 3;
                    } else {
                        $mensaje = '<div class="alert alert-danger">El número de documento no coincide con nuestros registros.</div>';
                    }
                } else {
                    $mensaje = '<div class="alert alert-danger">No se encontró información asociada a esta cuenta.</div>';
                }
            } else {
                $mensaje = '<div class="alert alert-danger">Error en el proceso de recuperación. Por favor, inténtelo nuevamente.</div>';
            }
        }
    }
}

// Forzar avance de paso para depuración (quitar en producción)
if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
    if (isset($_GET['paso'])) {
        $paso = (int)$_GET['paso'];
        if ($paso === 2) {
            $_SESSION['recuperacion_id'] = 1;
            $_SESSION['recuperacion_tipo'] = 'deportista'; 
            $_SESSION['recuperacion_id_persona'] = 1;
            $_SESSION['recuperacion_correo'] = 'test@example.com';
        } elseif ($paso === 3) {
            $_SESSION['recuperacion_pwd'] = '12345test@example.com';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Escuela Deportiva</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .recovery-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('img/background-login.jpg') no-repeat center center;
            background-size: cover;
            padding: 20px;
        }
        
        .recovery-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            padding: 40px;
        }
        
        .recovery-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .recovery-logo img {
            max-width: 150px;
            height: auto;
        }
        
        .recovery-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .recovery-header h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .recovery-header p {
            color: var(--gray-color);
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            max-width: 100px;
            position: relative;
        }
        
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 14px;
            left: 60%;
            width: 80%;
            height: 2px;
            background-color: #e0e0e0;
            z-index: 1;
        }
        
        .step.active:not(:last-child)::after,
        .step.completed:not(:last-child)::after {
            background-color: var(--primary-color);
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e0e0e0;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 8px;
            z-index: 2;
        }
        
        .step.active .step-number,
        .step.completed .step-number {
            background-color: var(--primary-color);
            color: white;
        }
        
        .step-name {
            font-size: 12px;
            color: #666;
        }
        
        .step.active .step-name,
        .step.completed .step-name {
            color: var(--primary-color);
            font-weight: 600;
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
        
        .btn-recovery {
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
        
        .btn-recovery:hover {
            background-color: #0055aa;
        }
        
        .password-display {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .password-display p {
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .password-value {
            font-family: monospace;
            font-size: 18px;
            background-color: #e9ecef;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px dashed #adb5bd;
            display: inline-block;
            margin-top: 5px;
        }
        
        .nav-links {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .nav-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .nav-links a:hover {
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
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="recovery-page">
        <div class="recovery-container">
            <div class="recovery-logo">
                <img src="img/logo.png" alt="Logo Escuela Deportiva">
            </div>
            
            <div class="recovery-header">
                <h2>Recuperar Contraseña</h2>
                <p>Siga los pasos para recuperar su contraseña</p>
            </div>
            
            <div class="step-indicator">
                <div class="step <?php echo ($paso >= 1) ? 'active' : ''; ?> <?php echo ($paso > 1) ? 'completed' : ''; ?>">
                    <div class="step-number"><?php echo ($paso > 1) ? '<i class="fas fa-check"></i>' : '1'; ?></div>
                    <div class="step-name">Correo</div>
                </div>
                <div class="step <?php echo ($paso >= 2) ? 'active' : ''; ?> <?php echo ($paso > 2) ? 'completed' : ''; ?>">
                    <div class="step-number"><?php echo ($paso > 2) ? '<i class="fas fa-check"></i>' : '2'; ?></div>
                    <div class="step-name">Verificación</div>
                </div>
                <div class="step <?php echo ($paso >= 3) ? 'active' : ''; ?>">
                    <div class="step-number">3</div>
                    <div class="step-name">Contraseña</div>
                </div>
            </div>
            
            <?php echo $mensaje; ?>
            
            <?php if ($paso == 1): ?>
                <!-- Paso 1: Solicitar correo -->
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="paso" value="1">
                    
                    <div class="form-group">
                        <label for="correo">Correo Electrónico</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="correo" name="correo" placeholder="Ingrese su correo electrónico" required>
                        </div>
                        <small>Ingrese el correo con el que se registró en el sistema.</small>
                    </div>
                    
                    <button type="submit" class="btn-recovery">Continuar</button>
                </form>
            <?php elseif ($paso == 2): ?>
                <!-- Paso 2: Verificar documento -->
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="paso" value="2">
                    
                    <div class="form-group">
                        <label for="documento">Número de Documento</label>
                        <div class="input-with-icon">
                            <i class="fas fa-id-card"></i>
                            <input type="text" id="documento" name="documento" placeholder="Ingrese su número de documento" required>
                        </div>
                        <small>Ingrese el número de documento asociado a su cuenta para verificar su identidad.</small>
                    </div>
                    
                    <button type="submit" class="btn-recovery">Verificar</button>
                </form>
            <?php elseif ($paso == 3): ?>
                <!-- Paso 3: Mostrar contraseña por defecto -->
                <div class="password-display">
                    <p>Su contraseña por defecto es:</p>
                    <div class="password-value"><?php echo isset($_SESSION['recuperacion_pwd']) ? $_SESSION['recuperacion_pwd'] : ''; ?></div>
                    <p class="mt-3">Por favor, use esta contraseña para iniciar sesión y cámbiela por seguridad.</p>
                </div>
                
                <a href="login.php" class="btn-recovery">Ir a Iniciar Sesión</a>
                
                <?php
                // Limpiar variables de sesión de recuperación
                unset($_SESSION['recuperacion_id']);
                unset($_SESSION['recuperacion_tipo']);
                unset($_SESSION['recuperacion_id_persona']);
                unset($_SESSION['recuperacion_correo']);
                unset($_SESSION['recuperacion_token']);
                unset($_SESSION['recuperacion_pwd']);
                ?>
            <?php endif; ?>
            
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Volver a Inicio</a>
                <a href="login.php">Iniciar Sesión <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    
    <script>
        // Script para ayudar en la depuración del formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Solo para depuración, no es necesario en producción
                    console.log('Formulario enviado');
                    const formData = new FormData(this);
                    for (let [key, value] of formData.entries()) {
                        console.log(`${key}: ${value}`);
                    }
                });
            }
        });
    </script>
</body>
</html>