<?php
// Datos de conexión a la base de datos
$servername = "localhost";
$username = "root"; // Cambia esto por tu usuario de MySQL
$password = ""; // Cambia esto por tu contraseña de MySQL (puede estar vacío)
$dbname = "smart_pack"; // Asegúrate de que este nombre coincida exactamente con tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Establecer conjunto de caracteres
$conn->set_charset("utf8mb4");
?>