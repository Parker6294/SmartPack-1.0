<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([]);
    exit;
}

// Verificar si se recibió el ID de empresa
if (!isset($_GET['id_empresa']) || empty($_GET['id_empresa'])) {
    echo json_encode([]);
    exit;
}

$idEmpresa = $_GET['id_empresa'];

try {
    // Obtener plantas activas de la empresa seleccionada
    $stmt = $conn->prepare("
        SELECT id_planta, nombre_planta 
        FROM plantas 
        WHERE id_empresa = ? AND estado = 1 
        ORDER BY nombre_planta
    ");
    $stmt->bind_param("i", $idEmpresa);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $plantas = [];
    while ($row = $result->fetch_assoc()) {
        $plantas[] = $row;
    }
    
    echo json_encode($plantas);
    
} catch (Exception $e) {
    echo json_encode([]);
}
?>