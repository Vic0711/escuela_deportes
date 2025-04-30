<?php
// Iniciar sesión para manejar variables de sesión
session_start();

// Verificar si existe información del deportista
if (!isset($_SESSION['datos_deportista']) || empty($_SESSION['datos_deportista'])) {
    // Redirigir al registro de deportista si no hay datos de deportista
    header("Location: registro_deportista.php");
    exit();
}

// Conexión a la base de datos
require_once 'config/db.php';

// Variable para mensajes
$mensaje = '';

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
    $parentesco = $_POST['parentesco'];
    $sexo = $_POST['sexo'];
    $numero_telefono = $_POST['numero_telefono'];
    $correo_electronico = $_POST['correo_electronico'];
    $direccion_residencia = $_POST['direccion_residencia'];
    $ciudad_nacimiento = $_POST['ciudad_nacimiento'];
    $departamento_nacimiento = $_POST['departamento_nacimiento'];
    $barrio_residencia = $_POST['barrio_residencia'];
    $ciudad_residencia = $_POST['ciudad_residencia'];
    $departamento_residencia = $_POST['departamento_residencia'];

    // Validar que los campos obligatorios no estén vacíos
    if (empty($tipo_identificacion) || empty($numero_documento) || empty($primer_nombre) || 
        empty($primer_apellido) || empty($parentesco) || empty($sexo) || 
        empty($numero_telefono) || empty($correo_electronico) || empty($direccion_residencia) ||
        empty($ciudad_nacimiento) || empty($departamento_nacimiento) ||
        empty($barrio_residencia) || empty($ciudad_residencia) || empty($departamento_residencia)) {
        $mensaje = '<div class="alert alert-danger">Por favor, complete todos los campos obligatorios.</div>';
    } else {
        // Verificar si el acudiente ya existe
        $stmt = $conn->prepare("SELECT id_padre FROM padre WHERE numero_documento = ?");
        $stmt->bind_param("s", $numero_documento);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // El acudiente ya existe, obtener su ID
            $row = $result->fetch_assoc();
            $id_padre = $row['id_padre'];
            $mensaje = '<div class="alert alert-warning">El acudiente ya está registrado. Continuando con el proceso de matrícula.</div>';
        } else {
            // Insertar nuevo acudiente
            $stmt = $conn->prepare("INSERT INTO padre (tipo_identificacion, numero_documento, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, parentesco, sexo, numero_telefono, correo_electronico, direccion_residencia, ciudad_nacimiento, departamento_nacimiento, barrio_residencia, ciudad_residencia, departamento_residencia) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("ssssssssssssssss", 
                $tipo_identificacion, 
                $numero_documento, 
                $primer_nombre, 
                $segundo_nombre, 
                $primer_apellido, 
                $segundo_apellido, 
                $parentesco, 
                $sexo, 
                $numero_telefono, 
                $correo_electronico, 
                $direccion_residencia, 
                $ciudad_nacimiento, 
                $departamento_nacimiento, 
                $barrio_residencia, 
                $ciudad_residencia, 
                $departamento_residencia
            );
            
            if ($stmt->execute()) {
                $id_padre = $conn->insert_id;
                $mensaje = '<div class="alert alert-success">Acudiente registrado correctamente.</div>';
            } else {
                $mensaje = '<div class="alert alert-danger">Error al registrar el acudiente: ' . $stmt->error . '</div>';
                // Si hay error, no continuar con el proceso
                $id_padre = null;
            }
        }
        
        // Si tenemos un ID de padre válido, ahora registramos al deportista
        if ($id_padre) {
            // Obtener los datos del deportista almacenados en la sesión
            $datos_deportista = $_SESSION['datos_deportista'];
            
            // Preparar la consulta para insertar al deportista
            $stmt = $conn->prepare("INSERT INTO deportista (tipo_identificacion, numero_documento, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, fecha_nacimiento, numero_telefono, peso, estatura, sexo, direccion_residencia, ciudad_nacimiento, departamento_nacimiento, barrio_residencia, ciudad_residencia, departamento_residencia, RH, foto, id_padre) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("ssssssssddsssssssssi", 
                $datos_deportista['tipo_identificacion'], 
                $datos_deportista['numero_documento'], 
                $datos_deportista['primer_nombre'], 
                $datos_deportista['segundo_nombre'], 
                $datos_deportista['primer_apellido'], 
                $datos_deportista['segundo_apellido'], 
                $datos_deportista['fecha_nacimiento'], 
                $datos_deportista['numero_telefono'], 
                $datos_deportista['peso'], 
                $datos_deportista['estatura'], 
                $datos_deportista['sexo'], 
                $datos_deportista['direccion_residencia'], 
                $datos_deportista['ciudad_nacimiento'], 
                $datos_deportista['departamento_nacimiento'], 
                $datos_deportista['barrio_residencia'], 
                $datos_deportista['ciudad_residencia'], 
                $datos_deportista['departamento_residencia'], 
                $datos_deportista['rh'], 
                $datos_deportista['foto'], 
                $id_padre
            );
            
            if ($stmt->execute()) {
                $id_deportista = $conn->insert_id;
                $_SESSION['id_deportista'] = $id_deportista;
                $_SESSION['id_acudiente'] = $id_padre;
                $_SESSION['nombre_acudiente'] = $primer_nombre . ' ' . $primer_apellido;
                
                // Eliminar los datos temporales del deportista
                unset($_SESSION['datos_deportista']);
                
                $mensaje .= '<div class="alert alert-success">Deportista registrado exitosamente. Ahora puede completar la matrícula.</div>';
                
                // Redirigir después de 2 segundos
                echo '<script>
                    setTimeout(function() {
                        window.location.href = "registro_matricula.php";
                    }, 2000);
                </script>';
            } else {
                $mensaje .= '<div class="alert alert-danger">Error al registrar el deportista: ' . $stmt->error . '</div>';
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
    <title>Registro de Acudiente - Escuela Deportiva</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="form-page">
        <div class="container">
            <div class="form-container">
                <h2 class="form-title">Registro de Acudiente</h2>
                <p class="form-description">Complete el formulario para registrar al acudiente del deportista: <strong><?php echo isset($_SESSION['nombre_deportista']) ? $_SESSION['nombre_deportista'] : ''; ?></strong></p>
                
                <?php echo $mensaje; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="registration-form">
                    <div class="form-progress">
                        <div class="progress-step completed">
                            <div class="step-number"><i class="fas fa-check"></i></div>
                            <div class="step-name">Deportista</div>
                        </div>
                        <div class="progress-connector completed"></div>
                        <div class="progress-step active">
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
                                <option value="Cédula de Ciudadanía">Cédula de Ciudadanía</option>
                                <option value="Cédula de Extranjería">Cédula de Extranjería</option>
                                <option value="Pasaporte">Pasaporte</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="numero_documento">Número de Documento <span class="required">*</span></label>
                            <input type="text" id="numero_documento" name="numero_documento" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="primer_nombre">Primer Nombre <span class="required">*</span></label>
                                <input type="text" id="primer_nombre" name="primer_nombre" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="segundo_nombre">Segundo Nombre</label>
                                <input type="text" id="segundo_nombre" name="segundo_nombre">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="primer_apellido">Primer Apellido <span class="required">*</span></label>
                                <input type="text" id="primer_apellido" name="primer_apellido" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="segundo_apellido">Segundo Apellido</label>
                                <input type="text" id="segundo_apellido" name="segundo_apellido">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="parentesco">Parentesco con el Deportista <span class="required">*</span></label>
                            <select id="parentesco" name="parentesco" required>
                                <option value="">Seleccione...</option>
                                <option value="Padre">Padre</option>
                                <option value="Madre">Madre</option>
                                <option value="Abuelo/a">Abuelo/a</option>
                                <option value="Tío/a">Tío/a</option>
                                <option value="Hermano/a">Hermano/a</option>
                                <option value="Tutor Legal">Tutor Legal</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Sexo <span class="required">*</span></label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="sexo" value="M" required> Masculino
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="sexo" value="F"> Femenino
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
                                    <option value="<?php echo $departamento['id_departamento']; ?>">
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
                        <h3 class="section-title">Información de Contacto</h3>
                        
                        <div class="form-group">
                            <label for="direccion_residencia">Dirección de Residencia <span class="required">*</span></label>
                            <input type="text" id="direccion_residencia" name="direccion_residencia" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="barrio_residencia">Barrio <span class="required">*</span></label>
                            <input type="text" id="barrio_residencia" name="barrio_residencia" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="departamento_residencia">Departamento <span class="required">*</span></label>
                                <select id="departamento_residencia" name="departamento_residencia" required>
                                    <option value="">Seleccione un departamento...</option>
                                    <?php foreach ($departamentos as $departamento): ?>
                                    <option value="<?php echo $departamento['id_departamento']; ?>">
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
                        
                        <div class="form-group">
                            <label for="numero_telefono">Número de Teléfono <span class="required">*</span></label>
                            <input type="tel" id="numero_telefono" name="numero_telefono" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="correo_electronico">Correo Electrónico <span class="required">*</span></label>
                            <input type="email" id="correo_electronico" name="correo_electronico" required>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Continuar al Registro de Matrícula</button>
                        <a href="registro_deportista.php" class="btn-secondary">Volver al Registro de Deportista</a>
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
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Por favor, complete todos los campos obligatorios.');
                }
            });
            
            // Validar el formato de correo electrónico
            const emailField = document.getElementById('correo_electronico');
            emailField.addEventListener('blur', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.value && !emailRegex.test(this.value)) {
                    this.classList.add('invalid');
                    alert('Por favor, ingrese un correo electrónico válido.');
                } else {
                    this.classList.remove('invalid');
                }
            });
            
            // Validar número de teléfono (solo números)
            const phoneField = document.getElementById('numero_telefono');
            phoneField.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            
            // Cargar municipios según el departamento seleccionado
            function cargarMunicipios(departamentoSelect, municipioSelect) {
                departamentoSelect.addEventListener('change', function() {
                    const departamentoId = this.value;
                    municipioSelect.innerHTML = '<option value="">Seleccionando municipios...</option>';
                    
                    if (departamentoId) {
                        // Realizar petición AJAX para obtener municipios
                        fetch(`get_municipios.php?id_departamento=${departamentoId}`)
                            .then(response => response.json())
                            .then(data => {
                                municipioSelect.innerHTML = '<option value="">Seleccione un municipio...</option>';
                                
                                data.forEach(municipio => {
                                    const option = document.createElement('option');
                                    option.value = municipio.id_municipio;
                                    option.textContent = municipio.nombre;
                                    municipioSelect.appendChild(option);
                                });
                            })
                            .catch(error => {
                                console.error('Error al cargar municipios:', error);
                                municipioSelect.innerHTML = '<option value="">Error al cargar municipios</option>';
                            });
                    } else {
                        municipioSelect.innerHTML = '<option value="">Primero seleccione un departamento...</option>';
                    }
                });
            }
            
            // Configurar carga de municipios para lugar de nacimiento
            const deptoNacimiento = document.getElementById('departamento_nacimiento');
            const ciudadNacimiento = document.getElementById('ciudad_nacimiento');
            cargarMunicipios(deptoNacimiento, ciudadNacimiento);
            
            // Configurar carga de municipios para lugar de residencia
            const deptoResidencia = document.getElementById('departamento_residencia');
            const ciudadResidencia = document.getElementById('ciudad_residencia');
            cargarMunicipios(deptoResidencia, ciudadResidencia);
        });
    </script>
</body>
</html>