<?php
// Incluir archivo de conexión a la base de datos
require_once 'config/db.php';

// Activar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función para respuesta JSON de error
function responderError($mensaje) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $mensaje]);
    exit;
}

// Verificar si se recibió un ID de departamento
if (!isset($_GET['id_departamento'])) {
    responderError('No se proporcionó ID de departamento');
}

$id_departamento = intval($_GET['id_departamento']);

// Validar que el ID de departamento sea válido
if ($id_departamento <= 0) {
    responderError('ID de departamento no válido');
}

try {
    // Consultar los municipios del departamento
    $stmt = $conn->prepare("SELECT id_municipio, nombre FROM municipios WHERE id_departamento = ? ORDER BY nombre");
    $stmt->bind_param("i", $id_departamento);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $municipios = [];
    while ($row = $result->fetch_assoc()) {
        $municipios[] = $row;
    }
    
    // Verificar si se encontraron municipios
    if (empty($municipios)) {
        // Consultar si el departamento existe
        $stmt = $conn->prepare("SELECT nombre FROM departamentos WHERE id_departamento = ?");
        $stmt->bind_param("i", $id_departamento);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            responderError('Departamento no encontrado');
        } else {
            $depto = $result->fetch_assoc();
            // El departamento existe pero no tiene municipios
            header('Content-Type: application/json');
            echo json_encode(['mensaje' => 'No hay municipios registrados para ' . $depto['nombre'], 'municipios' => []]);
            exit;
        }
    }
    
    // Devolver los municipios en formato JSON
    header('Content-Type: application/json');
    echo json_encode(['municipios' => $municipios]);
} catch (Exception $e) {
    responderError('Error en la consulta: ' . $e->getMessage());
}
?>