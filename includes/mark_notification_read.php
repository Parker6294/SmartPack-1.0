<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener y validar el ID de notificación
$id_notificacion = intval($_POST['id_notificacion'] ?? 0);

if ($id_notificacion <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID de notificación no válido']);
    exit;
}

// Configurar encabezados para JSON
header('Content-Type: application/json');

try {
    // Marcar notificación como leída
    $query = "UPDATE notificaciones_citas SET leida = 1 WHERE id_notificacion = ? AND id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $id_notificacion, $_SESSION['id_usuario']);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'No se pudo marcar la notificación como leída']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}