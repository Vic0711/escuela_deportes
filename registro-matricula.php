<?php
// Iniciar sesión para manejar variables de sesión
session_start();

// Verificar si existe un deportista y acudiente registrado
if (!isset($_SESSION['id_deportista']) || !isset($_SESSION['id_acudiente'])) {
    // Redirigir al registro de deportista si no hay deportista registrado
    header("Location: registro_deportista.php");
    exit();
}

// Conexión a la base de datos
require_once 'config/db.php';

// Variable para mensajes
$mensaje = '';

// Obtener las categorías disponibles
$categorias = [];
$stmt = $conn->prepare("SELECT * FROM categoria ORDER BY futbol DESC, nombre_categoria"); // Ordenar primero por fútbol (descendente), luego por nombre
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
}

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos del formulario
    $id_deportista = $_SESSION['id_deportista'];
    $id_categoria = $_POST['id_categoria'];
    $id_acudiente = $_SESSION['id_acudiente'];
    $fecha_matricula = date('Y-m-d'); // Fecha actual
    $estado = 'Activo';
    $observaciones = $_POST['observaciones'] ?: NULL;
    
    // Validar que se haya seleccionado una categoría
    if (empty($id_categoria)) {
        $mensaje = '<div class="alert alert-danger">Por favor, seleccione una categoría.</div>';
    } else {
        // Verificar si ya existe una matrícula para este deportista
        $stmt = $conn->prepare("SELECT id_matricula FROM matricula WHERE id_deportista = ? AND estado = 'Activo'");
        $stmt->bind_param("i", $id_deportista);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $mensaje = '<div class="alert alert-warning">Este deportista ya tiene una matrícula activa.</div>';
        } else {
            // Insertar nueva matrícula
            $stmt = $conn->prepare("INSERT INTO matricula (id_deportista, id_categoria, id_acudiente, fecha_matricula, estado, observaciones) 
                           VALUES (?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("iiisss", 
                $id_deportista, 
                $id_categoria, 
                $id_acudiente, 
                $fecha_matricula, 
                $estado, 
                $observaciones
            );
            
            if ($stmt->execute()) {
                $id_matricula = $conn->insert_id;
                
                // Limpiar variables de sesión
                unset($_SESSION['id_deportista']);
                unset($_SESSION['id_acudiente']);
                unset($_SESSION['nombre_deportista']);
                unset($_SESSION['nombre_acudiente']);
                
                $mensaje = '<div class="alert alert-success">Matrícula registrada correctamente. Ahora puede realizar el pago correspondiente.</div>';
                
                // Redirigir a la página de pagos o a un resumen
                echo '<script>
                    setTimeout(function() {
                        window.location.href = "pago.php?id_matricula=' . $id_matricula . '";
                    }, 2000);
                </script>';
            } else {
                $mensaje = '<div class="alert alert-danger">Error al registrar la matrícula: ' . $stmt->error . '</div>';
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
    <title>Registro de Matrícula - Escuela Deportiva</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .categoria-futbol {
            font-weight: bold;
            color: #0066cc;
            background-color: #f0f8ff;
        }
        
        .futbol-header {
            color: #0066cc;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .futbol-icon {
            margin-left: 5px;
            font-size: 20px;
        }
        
        .categoria-info {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        
        .category-details h4 {
            color: #0066cc;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="form-page">
        <div class="container">
            <div class="form-container">
                <h2 class="form-title">Registro de Matrícula</h2>
                <p class="form-description">Complete el formulario para finalizar el proceso de matrícula del deportista: <strong><?php echo isset($_SESSION['nombre_deportista']) ? $_SESSION['nombre_deportista'] : ''; ?></strong></p>
                
                <?php echo $mensaje; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="registration-form">
                    <div class="form-progress">
                        <div class="progress-step completed">
                            <div class="step-number"><i class="fas fa-check"></i></div>
                            <div class="step-name">Deportista</div>
                        </div>
                        <div class="progress-connector completed"></div>
                        <div class="progress-step completed">
                            <div class="step-number"><i class="fas fa-check"></i></div>
                            <div class="step-name">Acudiente</div>
                        </div>
                        <div class="progress-connector completed"></div>
                        <div class="progress-step active">
                            <div class="step-number">3</div>
                            <div class="step-name">Matrícula</div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Información de la Matrícula</h3>
                        
                        <div class="form-group">
                            <label for="id_categoria">Categoría <span class="required">*</span></label>
                            
                            <?php 
                            // Verificar si hay categorías de fútbol
                            $hay_futbol = false;
                            foreach ($categorias as $categoria) {
                                if (!empty($categoria['futbol'])) {
                                    $hay_futbol = true;
                                    break;
                                }
                            }
                            
                            if ($hay_futbol) {
                                echo '<div class="futbol-header">Categorías de Fútbol <span class="futbol-icon">⚽</span></div>';
                            }
                            ?>
                            
                            <select id="id_categoria" name="id_categoria" required>
                                <option value="">Seleccione una categoría...</option>
                                
                                <?php 
                                // Agrupar categorías por fútbol y otras
                                $categorias_futbol = [];
                                $otras_categorias = [];
                                
                                foreach ($categorias as $categoria) {
                                    if (!empty($categoria['futbol'])) {
                                        $categorias_futbol[] = $categoria;
                                    } else {
                                        $otras_categorias[] = $categoria;
                                    }
                                }
                                
                                // Mostrar primero las categorías de fútbol
                                foreach ($categorias_futbol as $categoria) {
                                    echo '<option value="' . $categoria['id_categoria'] . '" class="categoria-futbol">' . 
                                         $categoria['nombre_categoria'] . ' (' . $categoria['edad_minima'] . ' - ' . 
                                         $categoria['edad_maxima'] . ' años) ⚽</option>';
                                }
                                
                                // Si hay categorías de fútbol y otras categorías, añadir un separador
                                if (!empty($categorias_futbol) && !empty($otras_categorias)) {
                                    echo '<option disabled>──────────────</option>';
                                }
                                
                                // Mostrar el resto de categorías
                                foreach ($otras_categorias as $categoria) {
                                    echo '<option value="' . $categoria['id_categoria'] . '">' . 
                                         $categoria['nombre_categoria'] . ' (' . $categoria['edad_minima'] . ' - ' . 
                                         $categoria['edad_maxima'] . ' años)</option>';
                                }
                                ?>
                            </select>
                            <p class="field-info">Seleccione la categoría adecuada según la edad del deportista</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" rows="4"></textarea>
                        </div>
                        
                        <div class="categoria-info" id="categoria-info">
                            <!-- La información de la categoría seleccionada se mostrará aquí -->
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Términos y Condiciones</h3>
                        
                        <div class="custom-checkbox">
                            <input type="checkbox" id="terminos" name="terminos" required>
                            <label for="terminos">Acepto los términos y condiciones de la escuela deportiva, incluyendo las políticas de asistencia, comportamiento y uso de imagen. También autorizo la participación del deportista en todas las actividades programadas.</label>
                        </div>
                        
                        <div class="custom-checkbox">
                            <input type="checkbox" id="condicion_medica" name="condicion_medica" required>
                            <label for="condicion_medica">Declaro que el deportista no presenta condiciones médicas que impidan la práctica deportiva. En caso de existir alguna condición especial, ha sido detallada en las observaciones.</label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Completar Matrícula</button>
                        <a href="registro_acudiente.php" class="btn-secondary">Volver a Registro de Acudiente</a>
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
            const categoriaSelect = document.getElementById('id_categoria');
            const categoriaInfo = document.getElementById('categoria-info');
            
            // Mostrar información de la categoría seleccionada
            categoriaSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                
                if (this.value) {
                    // Obtener información de la categoría mediante AJAX
                    fetch(`get_categoria_info.php?id=${this.value}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data) {
                                let iconoDeporte = '';
                                // Verificar si es fútbol
                                if (data.futbol) {
                                    iconoDeporte = ' ⚽';
                                }
                                
                                categoriaInfo.innerHTML = `
                                    <div class="category-details">
                                        <h4>${data.nombre_categoria}${iconoDeporte}</h4>
                                        <p><strong>Descripción:</strong> ${data.descripcion || 'No disponible'}</p>
                                        <p><strong>Rango de edad:</strong> ${data.edad_minima} - ${data.edad_maxima} años</p>
                                    </div>
                                `;
                                
                                // Si es fútbol, aplicar clase especial
                                if (data.futbol) {
                                    document.querySelector('.category-details').classList.add('futbol-details');
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            categoriaInfo.innerHTML = '<p>No se pudo cargar la información de la categoría.</p>';
                        });
                } else {
                    categoriaInfo.innerHTML = '';
                }
            });
            
            // Validación del formulario
            form.addEventListener('submit', function(e) {
                const terminos = document.getElementById('terminos');
                const condicionMedica = document.getElementById('condicion_medica');
                
                if (!terminos.checked || !condicionMedica.checked) {
                    e.preventDefault();
                    alert('Debe aceptar los términos y condiciones para continuar.');
                }
                
                if (!categoriaSelect.value) {
                    e.preventDefault();
                    alert('Por favor, seleccione una categoría.');
                    categoriaSelect.classList.add('invalid');
                } else {
                    categoriaSelect.classList.remove('invalid');
                }
            });
        });
    </script>
</body>
</html>