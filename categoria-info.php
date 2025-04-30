<?php
// Conexión a la base de datos
require_once 'config/db.php';

// Verificar si se recibió un ID de categoría
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_categoria = intval($_GET['id']);
    
    // Consultar la información de la categoría
    $stmt = $conn->prepare("SELECT * FROM categoria WHERE id_categoria = ?");
    $stmt->bind_param("i", $id_categoria);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Devolver la información en formato JSON
        $categoria = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($categoria);
    } else {
        // No se encontró la categoría
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Categoría no encontrada']);
    }
} else {
    // ID no válido
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'ID de categoría no válido']);
}