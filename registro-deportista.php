<?php
// Iniciar sesión para manejar variables de sesión
session_start();

// Conexión a la base de datos
require_once 'config/db.php';

// Variable para mensajes
$mensaje = '';

// Variables para almacenar los datos del deportista
$id_deportista_temp = isset($_SESSION['id_deportista_temp']) ? $_SESSION['id_deportista_temp'] : null;
$datos_deportista = isset($_SESSION['datos_deportista']) ? $_SESSION['datos_deportista'] : [];

// Obtener lista de departamentos
$departamentos = [];
$stmt = $conn->prepare("SELECT * FROM departamentos ORDER BY nombre");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $departamentos[] = $row;
}

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos del formulario
    $tipo_identificacion = $_POST['tipo_identificacion'];
    $numero_documento = $_POST['numero_documento'];
    $primer_nombre = $_POST['primer_nombre'];
    $segundo_nombre = $_POST['segundo_nombre'] ?: NULL;
    $primer_apellido = $_POST['primer_apellido'];
    $segundo_apellido = $_POST['segundo_apellido'] ?: NULL;
    $correo_electronico = $_POST['correo_electronico'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $numero_telefono = $_POST['numero_telefono'];
    $peso = $_POST['peso'];
    $estatura = $_POST['estatura'];
    $sexo = $_POST['sexo'];
    $direccion_residencia = $_POST['direccion_residencia'];
    $ciudad_nacimiento = $_POST['ciudad_nacimiento'];
    $departamento_nacimiento = $_POST['departamento_nacimiento'];
    $barrio_residencia = $_POST['barrio_residencia'];
    $ciudad_residencia = $_POST['ciudad_residencia'];
    $departamento_residencia = $_POST['departamento_residencia'];
    $rh = $_POST['rh'];
    
    // Validar correo electrónico
    if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
        $mensaje = '<div class="alert alert-danger">Por favor, ingrese un correo electrónico válido.</div>';
    }
    // Validar número de teléfono colombiano (10 dígitos)
    else if (!preg_match('/^3[0-9]{9}$/', $numero_telefono)) {
        $mensaje = '<div class="alert alert-danger">Por favor, ingrese un número de teléfono móvil colombiano válido (10 dígitos comenzando con 3).</div>';
    }
    else {
        // Manejo de la foto
        $foto = NULL;
        $target_dir = "uploads/fotos/";
        
        // Verificar si la carpeta existe, si no, crearla
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == 0) {
            $allowed_types = array("jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png");
            $file_name = $_FILES["foto"]["name"];
            $file_type = $_FILES["foto"]["type"];
            $file_size = $_FILES["foto"]["size"];
            
            // Verificar extensión
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            if (!array_key_exists($ext, $allowed_types) || !in_array($file_type, $allowed_types)) {
                $mensaje = '<div class="alert alert-danger">Error: Por favor seleccione un formato de archivo válido (JPG o PNG).</div>';
            } else if ($file_size > 5242880) { // 5MB
                $mensaje = '<div class="alert alert-danger">Error: El tamaño del archivo no debe exceder los 5MB.</div>';
            } else {
                // Generar nombre único para el archivo
                $new_file_name = uniqid() . "." . $ext;
                $target_file = $target_dir . $new_file_name;
                
                if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                    $foto = $target_file;
                } else {
                    $mensaje = '<div class="alert alert-danger">Error: Hubo un problema al subir la foto.</div>';
                }
            }
        }

        // Validar que los campos obligatorios no estén vacíos
        if (empty($tipo_identificacion) || empty($numero_documento) || empty($primer_nombre) || 
            empty($primer_apellido) || empty($correo_electronico) || empty($fecha_nacimiento) || empty($sexo) || 
            empty($direccion_residencia) || empty($ciudad_nacimiento) || empty($departamento_nacimiento) ||
            empty($barrio_residencia) || empty($ciudad_residencia) || empty($departamento_residencia)) {
            $mensaje = '<div class="alert alert-danger">Por favor, complete todos los campos obligatorios.</div>';
        } else {
            // Verificar si el deportista ya existe
            $stmt = $conn->prepare("SELECT id_deportista FROM deportista WHERE numero_documento = ?");
            $stmt->bind_param("s", $numero_documento);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $mensaje = '<div class="alert alert-warning">El deportista ya está registrado en el sistema.</div>';
            } else {
                // Verificar si ya existe un usuario con este correo
                $stmt = $conn->prepare("SELECT id_deportista FROM deportista WHERE correo_electronico = ?");
                $stmt->bind_param("s", $correo_electronico);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $mensaje = '<div class="alert alert-warning">Ya existe un usuario con este correo electrónico. Por favor, utilice otro correo.</div>';
                } else {
                    // Almacenar datos temporalmente en la sesión
                    $_SESSION['datos_deportista'] = [
                        'tipo_identificacion' => $tipo_identificacion,
                        'numero_documento' => $numero_documento,
                        'primer_nombre' => $primer_nombre,
                        'segundo_nombre' => $segundo_nombre,
                        'primer_apellido' => $primer_apellido,
                        'segundo_apellido' => $segundo_apellido,
                        'correo_electronico' => $correo_electronico,
                        'fecha_nacimiento' => $fecha_nacimiento,
                        'numero_telefono' => $numero_telefono,
                        'peso' => $peso,
                        'estatura' => $estatura,
                        'sexo' => $sexo,
                        'direccion_residencia' => $direccion_residencia,
                        'ciudad_nacimiento' => $ciudad_nacimiento,
                        'departamento_nacimiento' => $departamento_nacimiento,
                        'barrio_residencia' => $barrio_residencia,
                        'ciudad_residencia' => $ciudad_residencia,
                        'departamento_residencia' => $departamento_residencia,
                        'rh' => $rh,
                        'foto' => $foto
                    ];
                    
                    $_SESSION['nombre_deportista'] = $primer_nombre . ' ' . $primer_apellido;
                    
                    // Redirección directa sin setTimeout
                    echo "<script>
                        alert('Información del deportista registrada correctamente. Ahora será redirigido al registro de acudiente.');
                        window.location.href = 'registro-acudiente.php';
                    </script>";
                    exit();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Deportista - Escuela Deportiva</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="form-page">
        <div class="container">
            <div class="form-container">
                <h2 class="form-title">Registro de Deportista</h2>
                <p class="form-description">Complete el formulario para registrar al deportista. Después registrará al acudiente.</p>
                
                <?php echo $mensaje; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="registration-form" enctype="multipart/form-data">
                    <div class="form-progress">
                        <div class="progress-step active">
                            <div class="step-number">1</div>
                            <div class="step-name">Deportista</div>
                        </div>
                        <div class="progress-connector"></div>
                        <div class="progress-step">
                            <div class="step-number">2</div>
                            <div class="step-name">Acudiente</div>
                        </div>
                        <div class="progress-connector"></div>
                        <div class="progress-step">
                            <div class="step-number">3</div>
                            <div class="step-name">Matrícula</div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Información Personal</h3>
                        
                        <div class="form-group">
                            <label for="tipo_identificacion">Tipo de Identificación <span class="required">*</span></label>
                            <select id="tipo_identificacion" name="tipo_identificacion" required>
                                <option value="">Seleccione...</option>
                                <option value="Tarjeta de Identidad">Tarjeta de Identidad</option>
                                <option value="Registro Civil">Registro Civil</option>
                                <option value="Cédula de Ciudadanía">Cédula de Ciudadanía</option>
                                <option value="Pasaporte">Pasaporte</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="numero_documento">Número de Documento <span class="required">*</span></label>
                            <input type="text" id="numero_documento" name="numero_documento" required value="<?php echo isset($datos_deportista['numero_documento']) ? $datos_deportista['numero_documento'] : ''; ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="primer_nombre">Primer Nombre <span class="required">*</span></label>
                                <input type="text" id="primer_nombre" name="primer_nombre" required value="<?php echo isset($datos_deportista['primer_nombre']) ? $datos_deportista['primer_nombre'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="segundo_nombre">Segundo Nombre</label>
                                <input type="text" id="segundo_nombre" name="segundo_nombre" value="<?php echo isset($datos_deportista['segundo_nombre']) ? $datos_deportista['segundo_nombre'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="primer_apellido">Primer Apellido <span class="required">*</span></label>
                                <input type="text" id="primer_apellido" name="primer_apellido" required value="<?php echo isset($datos_deportista['primer_apellido']) ? $datos_deportista['primer_apellido'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="segundo_apellido">Segundo Apellido</label>
                                <input type="text" id="segundo_apellido" name="segundo_apellido" value="<?php echo isset($datos_deportista['segundo_apellido']) ? $datos_deportista['segundo_apellido'] : ''; ?>">
                            </div>
                        </div>
                        
                        <!-- Campos de contacto justo después de nombres y apellidos -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="correo_electronico">Correo Electrónico <span class="required">*</span></label>
                                <input type="email" id="correo_electronico" name="correo_electronico" required value="<?php echo isset($datos_deportista['correo_electronico']) ? $datos_deportista['correo_electronico'] : ''; ?>">
                                <p class="field-info">Debe incluir el símbolo @ (ejemplo: nombre@dominio.com)</p>
                                <p class="field-info"><strong>Este será su nombre de usuario para iniciar sesión</strong></p>
                            </div>
                            
                            <div class="form-group">
                                <label for="numero_telefono">Número de Teléfono <span class="required">*</span></label>
                                <input type="tel" id="numero_telefono" name="numero_telefono" required value="<?php echo isset($datos_deportista['numero_telefono']) ? $datos_deportista['numero_telefono'] : ''; ?>">
                                <p class="field-info">Debe ser un número colombiano de 10 dígitos comenzando con 3</p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_nacimiento">Fecha de Nacimiento <span class="required">*</span></label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required value="<?php echo isset($datos_deportista['fecha_nacimiento']) ? $datos_deportista['fecha_nacimiento'] : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Sexo <span class="required">*</span></label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="sexo" value="M" required <?php echo (isset($datos_deportista['sexo']) && $datos_deportista['sexo'] == 'M') ? 'checked' : ''; ?>> Masculino
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="sexo" value="F" <?php echo (isset($datos_deportista['sexo']) && $datos_deportista['sexo'] == 'F') ? 'checked' : ''; ?>> Femenino
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Lugar de Nacimiento</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="departamento_nacimiento">Departamento <span class="required">*</span></label>
                                <select id="departamento_nacimiento" name="departamento_nacimiento" required>
                                    <option value="">Seleccione un departamento...</option>
                                    <?php foreach ($departamentos as $departamento): ?>
                                    <option value="<?php echo $departamento['id_departamento']; ?>" <?php echo (isset($datos_deportista['departamento_nacimiento']) && $datos_deportista['departamento_nacimiento'] == $departamento['id_departamento']) ? 'selected' : ''; ?>>
                                        <?php echo $departamento['nombre']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="ciudad_nacimiento">Ciudad/Municipio <span class="required">*</span></label>
                                <select id="ciudad_nacimiento" name="ciudad_nacimiento" required>
                                    <option value="">Primero seleccione un departamento...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Lugar de Residencia</h3>
                        
                        <div class="form-group">
                            <label for="direccion_residencia">Dirección de Residencia <span class="required">*</span></label>
                            <input type="text" id="direccion_residencia" name="direccion_residencia" required value="<?php echo isset($datos_deportista['direccion_residencia']) ? $datos_deportista['direccion_residencia'] : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="barrio_residencia">Barrio <span class="required">*</span></label>
                            <input type="text" id="barrio_residencia" name="barrio_residencia" required value="<?php echo isset($datos_deportista['barrio_residencia']) ? $datos_deportista['barrio_residencia'] : ''; ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="departamento_residencia">Departamento <span class="required">*</span></label>
                                <select id="departamento_residencia" name="departamento_residencia" required>
                                    <option value="">Seleccione un departamento...</option>
                                    <?php foreach ($departamentos as $departamento): ?>
                                    <option value="<?php echo $departamento['id_departamento']; ?>" <?php echo (isset($datos_deportista['departamento_residencia']) && $datos_deportista['departamento_residencia'] == $departamento['id_departamento']) ? 'selected' : ''; ?>>
                                        <?php echo $departamento['nombre']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="ciudad_residencia">Ciudad/Municipio <span class="required">*</span></label>
                                <select id="ciudad_residencia" name="ciudad_residencia" required>
                                    <option value="">Primero seleccione un departamento...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Información Física</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="peso">Peso (kg)</label>
                                <input type="number" id="peso" name="peso" step="0.01" min="0" value="<?php echo isset($datos_deportista['peso']) ? $datos_deportista['peso'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="estatura">Estatura (m)</label>
                                <input type="number" id="estatura" name="estatura" step="0.01" min="0" value="<?php echo isset($datos_deportista['estatura']) ? $datos_deportista['estatura'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="rh">Grupo Sanguíneo (RH)</label>
                            <select id="rh" name="rh">
                                <option value="">Seleccione...</option>
                                <option value="O+" <?php echo (isset($datos_deportista['rh']) && $datos_deportista['rh'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo (isset($datos_deportista['rh']) && $datos_deportista['rh'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                                <option value="A+" <?php echo (isset($datos_deportista['rh']) && $datos_deportista['rh'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo (isset($datos_deportista['rh']) && $datos_deportista['rh'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo (isset($datos_deportista['rh']) && $datos_deportista['rh'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo (isset($datos_deportista['rh']) && $datos_deportista['rh'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                <option value="AB+" <?php echo (isset($datos_deportista['rh']) && $datos_deportista['rh'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo (isset($datos_deportista['rh']) && $datos_deportista['rh'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="foto">Fotografía</label>
                            <div class="file-input-container">
                                <input type="file" id="foto" name="foto" accept="image/jpeg,image/png" class="file-input">
                                <div class="file-input-button">
                                    <i class="fas fa-upload"></i> Seleccionar Imagen
                                </div>
                                <span class="file-name">Ningún archivo seleccionado</span>
                            </div>
                            <div class="file-preview"></div>
                            <p class="field-info">Formatos aceptados: JPG, JPEG, PNG. Tamaño máximo: 5MB</p>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Información de acceso al sistema</h3>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <p>Al completar el registro, se creará automáticamente una cuenta de acceso con los siguientes datos:</p>
                            <ul>
                                <li><strong>Usuario:</strong> Su correo electrónico</li>
                                <li><strong>Contraseña:</strong> Su número de documento de identidad</li>
                                <li><strong>Rol:</strong> Deportista</li>
                            </ul>
                            <p>Por razones de seguridad, le recomendamos cambiar su contraseña después del primer inicio de sesión.</p>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Continuar al Registro de Acudiente</button>
                        <a href="index.php" class="btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.registration-form');
            
            // Validación de formulario en tiempo real
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const requiredFields = form.querySelectorAll('[required]');
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('invalid');
                    } else {
                        field.classList.remove('invalid');
                    }
                });
                
                // Validar correo electrónico
                const emailField = document.getElementById('correo_electronico');
                if (emailField.value && !emailField.value.includes('@')) {
                    isValid = false;
                    emailField.classList.add('invalid');
                    alert('El correo electrónico debe contener el símbolo @');
                }
                
                // Validar número de teléfono colombiano
                const phoneField = document.getElementById('numero_telefono');
                const phoneRegex = /^3[0-9]{9}$/;
                if (phoneField.value && !phoneRegex.test(phoneField.value)) {
                    isValid = false;
                    phoneField.classList.add('invalid');
                    alert('El número de teléfono debe ser un número colombiano válido de 10 dígitos comenzando con 3');
                }
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Por favor, complete correctamente todos los campos obligatorios.');
                }
            });
            
            // Validar correo electrónico en tiempo real
            const emailField = document.getElementById('correo_electronico');
            emailField.addEventListener('blur', function() {
                if (this.value && !this.value.includes('@')) {
                    this.classList.add('invalid');
                    this.setCustomValidity('El correo electrónico debe contener el símbolo @');
                } else {
                    this.classList.remove('invalid');
                    this.setCustomValidity('');
                }
            });
            
            // Validar número de teléfono (solo números y formato colombiano)
            const phoneField = document.getElementById('numero_telefono');
            phoneField.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            
            phoneField.addEventListener('blur', function() {
                const phoneRegex = /^3[0-9]{9}$/;
                if (this.value && !phoneRegex.test(this.value)) {
                    this.classList.add('invalid');
                    this.setCustomValidity('Debe ser un número colombiano de 10 dígitos comenzando con 3');
                } else {
                    this.classList.remove('invalid');
                    this.setCustomValidity('');
                }
            });
            
            // Validar peso y estatura (solo números y punto decimal)
            const numericFields = document.querySelectorAll('#peso, #estatura');
            numericFields.forEach(field => {
                field.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9.]/g, '');
                });
            });
            
            // Mostrar vista previa de la imagen
            const fileInput = document.getElementById('foto');
            const filePreview = document.querySelector('.file-preview');
            const fileName = document.querySelector('.file-name');
            
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                
                if (file) {
                    fileName.textContent = file.name;
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        filePreview.innerHTML = `<img src="${e.target.result}" alt="Vista previa" class="preview-image">`;
                    }
                    reader.readAsDataURL(file);
                    
                    // Validar tamaño y tipo de archivo
                    const fileSize = file.size / 1024 / 1024; // Convertir a MB
                    const fileType = file.type;
                    
                    if (!['image/jpeg', 'image/png'].includes(fileType)) {
                        alert('Por favor, seleccione un archivo de imagen válido (JPG o PNG).');
                        this.value = '';
                        fileName.textContent = 'Ningún archivo seleccionado';
                        filePreview.innerHTML = '';
                    } else if (fileSize > 5) {
                        alert('El tamaño del archivo no debe exceder los 5MB.');
                        this.value = '';
                        fileName.textContent = 'Ningún archivo seleccionado';
                        filePreview.innerHTML = '';
                    }
                } else {
                    fileName.textContent = 'Ningún archivo seleccionado';
                    filePreview.innerHTML = '';
                }
            });
            
            // Calcular edad automáticamente
            const fechaNacimiento = document.getElementById('fecha_nacimiento');
            fechaNacimiento.addEventListener('change', function() {
                const birthDate = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                // Si hay un campo de edad, actualizarlo
                const edadField = document.getElementById('edad');
                if (edadField) {
                    edadField.value = age;
                }
            });
        });
    </script>
</body>
</html>