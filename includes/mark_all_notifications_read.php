<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Configurar encabezados para JSON
header('Content-Type: application/json');

try {
    // Marcar todas las notificaciones como leídas
    $query = "UPDATE notificaciones_citas SET leida = 1 WHERE id_usuario = ? AND leida = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'affected' => $stmt->affected_rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}