<?php
session_start();
require_once '../config/database.php';

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Verificar si se proporciona un ID de usuario
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de usuario no válido']);
    exit;
}

$id_usuario = intval($_GET['id']);

try {
    $stmt = $conn->prepare("SELECT id_usuario, nombre, usuario, id_rol FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }
    
    $usuario = $result->fetch_assoc();
    
    // Establecer el tipo de contenido como JSON
    header('Content-Type: application/json');
    echo json_encode($usuario);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
    exit;
}
?>