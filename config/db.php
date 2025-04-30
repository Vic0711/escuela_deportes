<?php
// Datos de conexión a la base de datos
$servername = "localhost"; // Nombre del servidor (generalmente localhost)
$username = "root";       // Usuario de la base de datos (generalmente root)
$password = "";           // Contraseña del usuario (generalmente vacía en XAMPP/WAMP)
$dbname = "escuela_deportes"; // Nombre de la base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>