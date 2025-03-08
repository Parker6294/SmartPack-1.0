<?php
session_start();

// Habilitar reporte de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el archivo database.php existe
$databasePath = '../config/database.php';
if (!file_exists($databasePath)) {
    error_log("Error: El archivo database.php no existe en la ruta: " . $databasePath);
    $_SESSION['error'] = 'Error en la configuración del sistema. Contacte al administrador.';
    header('Location: ../views/login.php');
    exit;
}

// Incluir el archivo de conexión a la base de datos
require_once $databasePath;

// Verificar si $conn está definido
if (!isset($conn)) {
    error_log("Error: La conexión a la base de datos no se ha establecido correctamente.");
    $_SESSION['error'] = 'Error en la conexión a la base de datos. Contacte al administrador.';
    header('Location: ../views/login.php');
    exit;
}

// Verificar si se enviaron los datos del formulario
if (!isset($_POST['usuario']) || !isset($_POST['password'])) {
    $_SESSION['error'] = 'Por favor, ingresa usuario y contraseña.';
    header('Location: ../views/login.php');
    exit;
}

$usuario = trim($_POST['usuario']);
$password = trim($_POST['password']);

// Validación básica
if (empty($usuario) || empty($password)) {
    $_SESSION['error'] = 'Por favor, completa todos los campos.';
    header('Location: ../views/login.php');
    exit;
}

// Consulta para obtener los datos del usuario
try {
    // Registrar intento de inicio de sesión para depuración
    error_log("Intentando autenticar al usuario: " . $usuario);
    
    $stmt = $conn->prepare("SELECT u.id_usuario, u.nombre, u.usuario, u.password, u.id_rol, r.nombre_rol 
                           FROM usuarios u
                           JOIN roles r ON u.id_rol = r.id_rol
                           WHERE u.usuario = ? AND u.estado = 1");
    
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("s", $usuario);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("Usuario no encontrado o inactivo: " . $usuario);
        $_SESSION['error'] = 'Usuario o contraseña incorrectos.';
        header('Location: ../views/login.php');
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    // Registrar información para depuración
    error_log("Contraseña ingresada: " . $password);
    error_log("Contraseña almacenada: " . $user['password']);
    
    // Verificar contraseña 
    // PASO 1: Intentar verificar como contraseña en texto plano (para usuarios existentes)
    if ($password === $user['password']) {
        // Autenticación exitosa (texto plano)
        error_log("Autenticación exitosa (texto plano) para: " . $usuario);
        
        // Guardar datos en la sesión
        $_SESSION['id_usuario'] = $user['id_usuario'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['id_rol'] = $user['id_rol'];
        $_SESSION['rol'] = $user['nombre_rol'];
        
        // Actualizar último acceso
        $stmt = $conn->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?");
        $stmt->bind_param("i", $user['id_usuario']);
        $stmt->execute();
        
        // Redireccionar al dashboard
        header('Location: ../views/dashboard.php');
        exit;
    } 
    // PASO 2: Intentar verificar como contraseña hasheada (para usuarios nuevos o modificados)
    else if (function_exists('password_verify') && password_verify($password, $user['password'])) {
        // Autenticación exitosa (hash)
        error_log("Autenticación exitosa (hash) para: " . $usuario);
        
        // Guardar datos en la sesión
        $_SESSION['id_usuario'] = $user['id_usuario'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['id_rol'] = $user['id_rol'];
        $_SESSION['rol'] = $user['nombre_rol'];
        
        // Actualizar último acceso
        $stmt = $conn->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?");
        $stmt->bind_param("i", $user['id_usuario']);
        $stmt->execute();
        
        // Redireccionar al dashboard
        header('Location: ../views/dashboard.php');
        exit;
    } 
    // Si ninguna verificación es exitosa
    else {
        error_log("Contraseña incorrecta para: " . $usuario);
        $_SESSION['error'] = 'Usuario o contraseña incorrectos.';
        header('Location: ../views/login.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error en autenticación: " . $e->getMessage());
    $_SESSION['error'] = 'Error al intentar iniciar sesión. Por favor, inténtalo más tarde.';
    header('Location: ../views/login.php');
    exit;
}
?>