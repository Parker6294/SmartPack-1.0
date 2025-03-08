<?php
// includes/get_empresas.php
session_start();
require_once '../config/database.php';

// Ocultar errores para producción
error_reporting(0);
ini_set('display_errors', 0);

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Configurar encabezados para JSON
header('Content-Type: application/json');

try {
    // Consulta para obtener todas las empresas
    $query = "
        SELECT id_empresa, nombre_empresa, rfc, direccion, telefono, correo, sitio_web, estado
        FROM empresas
        ORDER BY nombre_empresa ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $empresas = [];
    while ($row = $result->fetch_assoc()) {
        $empresas[] = $row;
    }
    
    echo json_encode($empresas);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener datos: ' . $e->getMessage()]);
}
// No incluir ?> al final del archivo