<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// Determinar qué sección mostrar
$currentSection = isset($_GET['section']) ? $_GET['section'] : 'dashboard';

// Obtener información para el dashboard
try {
    // Total de usuarios
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $totalUsuarios = $result->fetch_assoc()['total'];
    } else {
        $totalUsuarios = 0;
    }

    // Usuarios activos
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE estado = 1");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $usuariosActivos = $result->fetch_assoc()['total'];
    } else {
        $usuariosActivos = 0;
    }

    // Usuarios inactivos
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE estado = 0");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $usuariosInactivos = $result->fetch_assoc()['total'];
    } else {
        $usuariosInactivos = 0;
    }

    // Obtener todos los usuarios para la tabla
    $usuarios = [];
    $stmt = $conn->prepare("
        SELECT u.id_usuario, u.nombre, u.usuario, u.estado, u.ultimo_acceso, r.nombre_rol
        FROM usuarios u
        JOIN roles r ON u.id_rol = r.id_rol
        ORDER BY u.nombre
    ");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
    }

    // Obtener roles para el formulario
    $roles = [];
    $stmt = $conn->prepare("SELECT id_rol, nombre_rol FROM roles ORDER BY nombre_rol");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
    }

    // Obtener total de empresas y plantas
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM empresas");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $totalEmpresas = $result->fetch_assoc()['total'];
    } else {
        $totalEmpresas = 0;
    }

    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM plantas");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $totalPlantas = $result->fetch_assoc()['total'];
    } else {
        $totalPlantas = 0;
    }

    // Obtener empresas para el formulario de plantas
    $empresas = [];
    $stmt = $conn->prepare("SELECT id_empresa, nombre_empresa FROM empresas WHERE estado = 1 ORDER BY nombre_empresa");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $empresas[] = $row;
        }
    }

    // Obtener todas las empresas para la tabla
    $todasEmpresas = [];
    $stmt = $conn->prepare("
        SELECT id_empresa, nombre_empresa, rfc, direccion, telefono, correo, sitio_web, estado
        FROM empresas
        ORDER BY nombre_empresa ASC
    ");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $todasEmpresas[] = $row;
        }
    }

    // Obtener todas las plantas para la tabla
    $todasPlantas = [];
    $stmt = $conn->prepare("
        SELECT p.id_planta, p.nombre_planta, p.id_empresa, e.nombre_empresa, 
               p.ubicacion, p.codigo_planta, p.responsable, p.telefono, p.correo, p.estado
        FROM plantas p
        JOIN empresas e ON p.id_empresa = e.id_empresa
        ORDER BY e.nombre_empresa ASC, p.nombre_planta ASC
    ");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $todasPlantas[] = $row;
        }
    }

    // Obtener citas asignadas al usuario
    $citasAsignadas = [];
    $citasCreadas = [];
    
    if ($currentSection == 'citas') {
        $idUsuario = $_SESSION['id_usuario'];

        // Citas asignadas al usuario
        $stmt = $conn->prepare("
            SELECT c.id_cita, c.folio, e.nombre_empresa, p.nombre_planta, 
                   DATE_FORMAT(c.fecha_programada, '%d/%m/%Y') as fecha_formateada,
                   DATE_FORMAT(c.hora_programada, '%H:%i') as hora_formateada,
                   u.nombre as nombre_asignador, c.estado
            FROM citas c
            JOIN empresas e ON c.id_empresa = e.id_empresa
            JOIN plantas p ON c.id_planta = p.id_planta
            LEFT JOIN usuarios u ON c.id_usuario_asignador = u.id_usuario
            WHERE c.id_usuario_asignado = ?
            ORDER BY c.fecha_programada DESC, c.hora_programada ASC
        ");
        
        if ($stmt) {
            $stmt->bind_param("i", $idUsuario);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $citasAsignadas[] = $row;
            }
        }

        // Citas creadas por el usuario
        $stmt = $conn->prepare("
            SELECT c.id_cita, c.folio, e.nombre_empresa, p.nombre_planta, 
                   DATE_FORMAT(c.fecha_programada, '%d/%m/%Y') as fecha_formateada,
                   DATE_FORMAT(c.hora_programada, '%H:%i') as hora_formateada,
                   u.nombre as nombre_asignado, c.estado
            FROM citas c
            JOIN empresas e ON c.id_empresa = e.id_empresa
            JOIN plantas p ON c.id_planta = p.id_planta
            LEFT JOIN usuarios u ON c.id_usuario_asignado = u.id_usuario
            WHERE c.id_usuario_asignador = ?
            ORDER BY c.fecha_programada DESC, c.hora_programada ASC
        ");
        
        if ($stmt) {
            $stmt->bind_param("i", $idUsuario);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $citasCreadas[] = $row;
            }
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al cargar los datos: ' . $e->getMessage();
}

// Manejar formularios y acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar formulario de asignación de cita
    if (isset($_POST['action']) && $_POST['action'] === 'asignar_cita') {
        $idCita = $_POST['id_cita'];
        $idUsuarioAsignado = $_POST['id_usuario_asignado'];
        $notasAsignacion = $_POST['notas_asignacion'];

        try {
            $stmt = $conn->prepare("
                UPDATE citas 
                SET id_usuario_asignado = ?, 
                    id_usuario_asignador = ?, 
                    notas_asignacion = ?,
                    fecha_asignacion = NOW()
                WHERE id_cita = ?
            ");

            if ($stmt) {
                $idUsuarioAsignador = $_SESSION['id_usuario'];
                $stmt->bind_param("iisi", $idUsuarioAsignado, $idUsuarioAsignador, $notasAsignacion, $idCita);
                $resultado = $stmt->execute();

                if ($resultado) {
                    $_SESSION['success'] = 'Cita asignada correctamente';
                    header('Location: dashboard.php?section=citas');
                    exit;
                } else {
                    $_SESSION['error'] = 'Error al asignar la cita';
                }
            } else {
                $_SESSION['error'] = 'Error en la consulta: ' . $conn->error;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
    }
}

// Determinar la pestaña activa para la sección de citas
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'mis-citas';

// Función auxiliar para determinar clase CSS según el estado
function getStatusClass($estado)
{
    switch ($estado) {
        case 0:
            return 'inactive'; // pendiente
        case 1:
            return 'active';   // completada
        case 2:
            return 'cancelled'; // cancelada
        default:
            return '';
    }
}

// Función auxiliar para obtener texto de estado
function getStatusText($estado)
{
    switch ($estado) {
        case 0:
            return 'Pendiente';
        case 1:
            return 'Completada';
        case 2:
            return 'Cancelada';
        default:
            return 'Desconocido';
    }
}
?>




<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Pack - Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #005580;
            /* Azul de Smart Pack */
            --primary-dark: #004466;
            /* Azul más oscuro */
            --secondary-color: #2c3e50;
            /* Color oscuro para fondos */
            --accent-color: #bb2e35;
            /* Rojo de Smart Pack */
            --danger-color: #c63030;
            /* Rojo para alertas */
            --success-color: #2ecc71;
            /* Verde para éxito */
            --warning-color: #f39c12;
            /* Naranja para advertencias */
            --text-color: #333;
            --text-light: #777;
            --bg-color: #f8f9fa;
            --bg-card: #ffffff;
            --border-color: #e0e0e0;
            --border-radius: 8px;
            --shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        /* Reset y estilos base */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        html {
            font-size: 16px;
            height: 100%;
        }

        body {
            font-family: 'Segoe UI', 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 1rem;
            line-height: 1.5;
            color: var(--text-color);
            background-color: var(--bg-color);
            overflow-x: hidden;
            min-height: 100%;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        button,
        input,
        select {
            font-family: inherit;
            font-size: inherit;
            border: none;
            outline: none;
        }

        /* Layout principal */
        .dashboard-layout {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Navbar móvil */
        .mobile-nav {
            display: none;
            background-color: var(--secondary-color);
            padding: 1rem;
            color: white;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .mobile-nav-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mobile-logo img {
            height: 40px;
            object-fit: contain;
        }

        .menu-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.25rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }

        .menu-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Overlay para el menú móvil */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 90;
            opacity: 0;
            visibility: hidden;
            backdrop-filter: blur(5px);
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            height: 100vh;
            position: fixed;
            background-color: var(--secondary-color);
            color: white;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.15);
        }

        .logo-container {
            margin-bottom: 2rem;
            text-align: center;
        }

        .logo-container img {
            max-width: 80%;
            height: auto;
            object-fit: contain;
        }

        .user-profile {
            padding: 1rem;
            border-radius: var(--border-radius);
            background-color: rgba(255, 255, 255, 0.1);
            margin-bottom: 2rem;
        }

        .user-profile h3 {
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        .user-profile p {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .nav-menu {
            margin-bottom: auto;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 0.5rem;
        }

        .nav-link i {
            margin-right: 1rem;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
        }

        .nav-link.active {
            border-left: 3px solid var(--accent-color);
            /* Rojo Smart Pack */
        }

        .btn-logout {
            width: 100%;
            padding: 0.75rem;
            border: none;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: var(--border-radius);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1rem;
        }

        .btn-logout i {
            margin-right: 0.5rem;
        }

        .btn-logout:hover {
            background-color: rgba(231, 76, 60, 0.2);
        }

        /* Contenido principal */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--secondary-color);
            position: relative;
        }

        .page-header h1::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 40px;
            height: 3px;
            background-color: var(--accent-color);
            border-radius: 3px;
        }

        /* Alertas */
        .alerts-container {
            margin-bottom: 1.5rem;
        }

        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .alert::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: inherit;
            border-radius: 4px 0 0 4px;
        }

        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            border-left: 4px solid var(--success-color);
            color: var(--success-color);
        }

        .alert-error {
            background-color: rgba(231, 76, 60, 0.1);
            border-left: 4px solid var(--danger-color);
            color: var(--danger-color);
        }

        /* Contenedores de sección */
        .content-section {
            display: none;
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        .content-section.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
            animation: pageTransition 0.6s ease-out forwards;
        }

        @keyframes pageTransition {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Estadísticas en cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stats-card {
            background-color: var(--bg-card);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }

        .stats-icon-wrapper {
            margin-right: 1.5rem;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .stats-icon.blue {
            background-color: var(--primary-color);
            /* Azul Smart Pack */
        }

        .stats-icon.green {
            background-color: var(--success-color);
        }

        .stats-icon.red {
            background-color: var(--accent-color);
            /* Rojo Smart Pack */
        }

        .stats-info h2 {
            font-size: 1.8rem;
            margin-bottom: 0.25rem;
            font-weight: 600;
        }

        .stats-info p {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Tabla de usuarios y datos */
        .users-table {
            background-color: var(--bg-card);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: auto;
            margin-top: 1.5rem;
            border: 1px solid var(--border-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: rgba(0, 0, 0, 0.02);
            font-weight: 600;
            color: var(--secondary-color);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Badges de estado */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .status-badge.active {
            background-color: rgba(46, 204, 113, 0.15);
            color: var(--success-color);
        }

        .status-badge.inactive {
            background-color: rgba(231, 76, 60, 0.15);
            color: var(--danger-color);
        }

        /* Botones de acciones */
        .actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        .btn-edit,
        .btn-toggle,
        .btn-delete {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            background-color: #f8f9fa;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-edit {
            color: var(--primary-color);
            /* Azul Smart Pack */
        }

        .btn-edit:hover {
            background-color: var(--primary-color);
            /* Azul Smart Pack */
            color: white;
        }

        .btn-toggle {
            color: var(--accent-color);
            /* Rojo Smart Pack */
        }

        .btn-toggle:hover {
            background-color: var(--accent-color);
            /* Rojo Smart Pack */
            color: white;
        }

        .btn-delete {
            color: var(--danger-color);
        }

        .btn-delete:hover {
            background-color: var(--danger-color);
            color: white;
        }

        .inline-form {
            display: inline;
        }

        /* Botón agregar */
        .btn-add-user {
            background-color: var(--accent-color);
            /* Rojo Smart Pack */
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .btn-add-user i {
            margin-right: 0.5rem;
        }

        .btn-add-user:hover {
            background-color: #a12930;
            /* Rojo Smart Pack más oscuro */
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 200;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .modal.active {
            opacity: 1;
        }

        .modal-content {
            background-color: var(--bg-card);
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            background-color: var(--bg-card);
            z-index: 10;
        }

        .modal-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--secondary-color);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .modal-close:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--danger-color);
        }

        /* Formularios */
        form {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--secondary-color);
        }

        input,
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            background-color: var(--bg-color);
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 85, 128, 0.2);
        }

        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            /* Azul Smart Pack */
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            margin-top: 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
        }

        /* Pestañas para empresas/plantas */
        .tab-container {
            margin-bottom: 20px;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .tab-button {
            padding: 10px 20px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-light);
            transition: all 0.3s ease;
        }

        .tab-button:hover {
            color: var(--primary-color);
        }

        .tab-button.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Media queries */
        @media (max-width: 992px) {
            .sidebar {
                width: 250px;
            }

            .main-content {
                margin-left: 250px;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 250px;
                z-index: 1000;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
                width: 100%;
            }

            .mobile-nav {
                display: block;
            }

            .users-table {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 0 -1rem;
                padding: 0 1rem;
                width: calc(100% + 2rem);
            }

            .stats-card {
                padding: 1rem;
            }

            .stats-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }

            .btn-edit,
            .btn-toggle {
                width: 42px;
                height: 42px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .modal-content {
                max-height: 85vh;
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            th,
            td {
                padding: 0.75rem 0.5rem;
                font-size: 0.9rem;
            }

            .modal-content {
                width: 95%;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .btn-add-user {
                width: 100%;
                justify-content: center;
            }

            .alert {
                padding: 0.75rem;
            }

            .nav-link {
                padding: 0.6rem 1rem;
            }

            .stats-info h2 {
                font-size: 1.5rem;
            }

            .modal-header {
                padding: 1rem;
            }

            .modal-header h2 {
                font-size: 1.3rem;
            }

            form {
                padding: 1rem;
            }

            input,
            select,
            .btn-submit {
                padding: 0.6rem;
            }
        }
    </style>
</head>


<body>
    <!-- Navbar móvil -->
    <nav class="mobile-nav">
        <div class="mobile-nav-header">
            <div class="mobile-logo">
                <img src="../assets/images/smartpack_largo.jpeg" alt="Smart Pack">
            </div>
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Overlay para el menú móvil -->
    <div class="overlay" onclick="toggleSidebar()"></div>

    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo-container">
                <img src="../assets/images/smartpack_largo.jpeg" alt="Smart Pack">
            </div>

            <div class="user-profile">
                <h3><?php echo htmlspecialchars($_SESSION['nombre']); ?></h3>
                <p><?php echo htmlspecialchars($_SESSION['rol']); ?></p>
            </div>

            <nav>
                <ul class="nav-menu">
                    <li>
                        <a href="dashboard.php" class="nav-link <?php echo $currentSection == 'dashboard' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?section=users" class="nav-link <?php echo $currentSection == 'users' ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i>
                            <span>Usuarios</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?section=empresas" class="nav-link <?php echo $currentSection == 'empresas' ? 'active' : ''; ?>">
                            <i class="fas fa-building"></i>
                            <span>Empresas</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?section=citas" class="nav-link <?php echo $currentSection == 'citas' ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-check"></i>
                            <span>Citas</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?section=config" class="nav-link <?php echo $currentSection == 'config' ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i>
                            <span>Configuración</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <form action="../includes/logout.php" method="POST">
                <button type="submit" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </form>
        </aside>

        <!-- Contenido Principal -->
        <main class="main-content">
            <!-- Contenedor de alertas con animación -->
            <div class="alerts-container">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success animate-alert">
                        <?php
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error animate-alert">
                        <?php
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($currentSection == 'dashboard'): ?>
                <!-- Sección Dashboard -->
                <div class="content-section active">
                    <div class="page-header">
                        <h1>Dashboard</h1>
                    </div>

                    <!-- Grid de tarjetas de estadísticas -->
                    <div class="stats-grid">
                        <!-- Tarjeta Total Usuarios -->
                        <div class="stats-card">
                            <div class="stats-icon-wrapper">
                                <div class="stats-icon blue">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="stats-info">
                                <h2><?php echo $totalUsuarios; ?></h2>
                                <p>Total de Usuarios</p>
                            </div>
                        </div>

                        <!-- Tarjeta Usuarios Activos -->
                        <div class="stats-card">
                            <div class="stats-icon-wrapper">
                                <div class="stats-icon green">
                                    <i class="fas fa-user-check"></i>
                                </div>
                            </div>
                            <div class="stats-info">
                                <h2><?php echo isset($usuariosActivos) ? $usuariosActivos : '0'; ?></h2>
                                <p>Usuarios Activos</p>
                            </div>
                        </div>

                        <!-- Tarjeta Usuarios Inactivos -->
                        <div class="stats-card">
                            <div class="stats-icon-wrapper">
                                <div class="stats-icon red">
                                    <i class="fas fa-user-times"></i>
                                </div>
                            </div>
                            <div class="stats-info">
                                <h2><?php echo isset($usuariosInactivos) ? $usuariosInactivos : '0'; ?></h2>
                                <p>Usuarios Inactivos</p>
                            </div>
                        </div>

                        <!-- Tarjeta Total Empresas -->
                        <div class="stats-card">
                            <div class="stats-icon-wrapper">
                                <div class="stats-icon blue">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>
                            <div class="stats-info">
                                <h2><?php echo isset($totalEmpresas) ? $totalEmpresas : '0'; ?></h2>
                                <p>Total de Empresas</p>
                            </div>
                        </div>

                        <!-- Tarjeta Total Plantas -->
                        <div class="stats-card">
                            <div class="stats-icon-wrapper">
                                <div class="stats-icon green">
                                    <i class="fas fa-industry"></i>
                                </div>
                            </div>
                            <div class="stats-info">
                                <h2><?php echo isset($totalPlantas) ? $totalPlantas : '0'; ?></h2>
                                <p>Total de Plantas</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($currentSection == 'users'): ?>
                <!-- Sección Usuarios -->
                <div class="content-section active">
                    <div class="page-header">
                        <h1>Gestión de Usuarios</h1>
                    </div>

                    <a href="add_user.php" class="btn-add-user">
                        <i class="fas fa-plus"></i> Agregar Usuario
                    </a>

                    <div class="users-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Último Acceso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['nombre_rol']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $usuario['estado'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $usuario['estado'] ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])); ?></td>
                                        <td class="actions">
                                            <a href="edit_user.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn-edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="../includes/toggle_user_status.php?id=<?php echo $usuario['id_usuario']; ?>&estado=<?php echo $usuario['estado']; ?>" class="btn-toggle">
                                                <i class="fas fa-<?php echo $usuario['estado'] ? 'ban' : 'check'; ?>"></i>
                                            </a>
                                            <?php if ($_SESSION['rol'] === 'Administrador'): ?>
                                                <a href="javascript:void(0)" onclick="confirmDelete('<?php echo $usuario['id_usuario']; ?>', '<?php echo htmlspecialchars(addslashes($usuario['nombre'])); ?>', 'user')" class="btn-delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($currentSection == 'empresas'): ?>
                <!-- Sección Empresas y Plantas -->
                <div class="content-section active">
                    <div class="page-header">
                        <h1>Gestión de Empresas y Plantas</h1>
                    </div>

                    <div class="tab-container">
                        <div class="tab-buttons">
                            <a href="dashboard.php?section=empresas&tab=empresas" class="tab-button <?php echo ($activeTab == 'empresas' || $activeTab == '') ? 'active' : ''; ?>">Empresas</a>
                            <a href="dashboard.php?section=empresas&tab=plantas" class="tab-button <?php echo $activeTab == 'plantas' ? 'active' : ''; ?>">Plantas</a>
                        </div>

                        <?php if ($activeTab == 'empresas' || $activeTab == ''): ?>
                            <!-- Tab de Empresas -->
                            <div class="tab-content active">
                                <a href="add_empresa.php" class="btn-add-user">
                                    <i class="fas fa-plus"></i> Agregar Empresa
                                </a>

                                <div class="users-table">
                                    <table id="empresas-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>RFC</th>
                                                <th>Teléfono</th>
                                                <th>Correo</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($todasEmpresas)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No hay empresas registradas</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($todasEmpresas as $empresa): ?>
                                                    <tr>
                                                        <td><?php echo $empresa['id_empresa']; ?></td>
                                                        <td><?php echo htmlspecialchars($empresa['nombre_empresa']); ?></td>
                                                        <td><?php echo htmlspecialchars($empresa['rfc'] ?? ''); ?></td>
                                                        <td><?php echo htmlspecialchars($empresa['telefono'] ?? ''); ?></td>
                                                        <td><?php echo htmlspecialchars($empresa['correo'] ?? ''); ?></td>
                                                        <td>
                                                            <span class="status-badge <?php echo $empresa['estado'] ? 'active' : 'inactive'; ?>">
                                                                <?php echo $empresa['estado'] ? 'Activo' : 'Inactivo'; ?>
                                                            </span>
                                                        </td>
                                                        <td class="actions">
                                                            <a href="edit_empresa.php?id=<?php echo $empresa['id_empresa']; ?>" class="btn-edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="../includes/toggle_empresa_status.php?id=<?php echo $empresa['id_empresa']; ?>&estado=<?php echo $empresa['estado']; ?>" class="btn-toggle">
                                                                <i class="fas fa-<?php echo $empresa['estado'] ? 'ban' : 'check'; ?>"></i>
                                                            </a>
                                                            <a href="javascript:void(0)" onclick="confirmDelete('<?php echo $empresa['id_empresa']; ?>', '<?php echo htmlspecialchars(addslashes($empresa['nombre_empresa'])); ?>', 'empresa')" class="btn-delete">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($activeTab == 'plantas'): ?>
                            <!-- Tab de Plantas -->
                            <div class="tab-content active">
                                <a href="add_planta.php" class="btn-add-user">
                                    <i class="fas fa-plus"></i> Agregar Planta
                                </a>

                                <div class="users-table">
                                    <table id="plantas-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre Planta</th>
                                                <th>Empresa</th>
                                                <th>Ubicación</th>
                                                <th>Código</th>
                                                <th>Responsable</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($todasPlantas)): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center">No hay plantas registradas</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($todasPlantas as $planta): ?>
                                                    <tr>
                                                        <td><?php echo $planta['id_planta']; ?></td>
                                                        <td><?php echo htmlspecialchars($planta['nombre_planta']); ?></td>
                                                        <td><?php echo htmlspecialchars($planta['nombre_empresa']); ?></td>
                                                        <td><?php echo htmlspecialchars($planta['ubicacion'] ?? ''); ?></td>
                                                        <td><?php echo htmlspecialchars($planta['codigo_planta'] ?? ''); ?></td>
                                                        <td><?php echo htmlspecialchars($planta['responsable'] ?? ''); ?></td>
                                                        <td>
                                                            <span class="status-badge <?php echo $planta['estado'] ? 'active' : 'inactive'; ?>">
                                                                <?php echo $planta['estado'] ? 'Activo' : 'Inactivo'; ?>
                                                            </span>
                                                        </td>
                                                        <td class="actions">
                                                            <a href="edit_planta.php?id=<?php echo $planta['id_planta']; ?>" class="btn-edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="../includes/toggle_planta_status.php?id=<?php echo $planta['id_planta']; ?>&estado=<?php echo $planta['estado']; ?>" class="btn-toggle">
                                                                <i class="fas fa-<?php echo $planta['estado'] ? 'ban' : 'check'; ?>"></i>
                                                            </a>
                                                            <a href="javascript:void(0)" onclick="confirmDelete('<?php echo $planta['id_planta']; ?>', '<?php echo htmlspecialchars(addslashes($planta['nombre_planta'])); ?>', 'planta')" class="btn-delete">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($currentSection == 'citas'): ?>
                <!-- Sección de Citas y Evaluaciones -->
                <div class="content-section active">
                    <div class="page-header">
                        <h1>Gestión de Citas de Evaluación</h1>
                        <div class="notifications-wrapper">
                            <?php
                            // Contar notificaciones no leídas
                            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM notificaciones WHERE id_usuario = ? AND leida = 0");
                            $stmt->bind_param("i", $_SESSION['id_usuario']);
                            $stmt->execute();
                            $countResult = $stmt->get_result();
                            $notificacionesNoLeidas = $countResult->fetch_assoc()['total'];
                            ?>
                            <a href="notifications.php" class="notifications-icon">
                                <i class="fas fa-bell"></i>
                                <?php if ($notificacionesNoLeidas > 0): ?>
                                    <span class="badge"><?php echo $notificacionesNoLeidas; ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>

                    <div class="tab-container">
                        <div class="tab-buttons">
                            <a href="dashboard.php?section=citas&tab=mis-citas" class="tab-button <?php echo ($activeTab == 'mis-citas' || $activeTab == '') ? 'active' : ''; ?>">Mis Citas Asignadas</a>
                            <a href="dashboard.php?section=citas&tab=citas-creadas" class="tab-button <?php echo $activeTab == 'citas-creadas' ? 'active' : ''; ?>">Citas Que He Asignado</a>
                        </div>

                        <?php if ($activeTab == 'mis-citas' || $activeTab == ''): ?>
                            <!-- Tab Mis Citas -->
                            <div class="tab-content active">
                                <div class="users-table">
                                    <table id="mis-citas-table">
                                        <thead>
                                            <tr>
                                                <th>Folio</th>
                                                <th>Empresa</th>
                                                <th>Planta</th>
                                                <th>Fecha</th>
                                                <th>Hora</th>
                                                <th>Asignada Por</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($citasAsignadas)): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center">No tienes citas asignadas</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($citasAsignadas as $cita): ?>
                                                    <tr>
                                                        <td><?php echo $cita['folio']; ?></td>
                                                        <td><?php echo htmlspecialchars($cita['nombre_empresa']); ?></td>
                                                        <td><?php echo htmlspecialchars($cita['nombre_planta']); ?></td>
                                                        <td><?php echo $cita['fecha_formateada']; ?></td>
                                                        <td><?php echo $cita['hora_formateada']; ?></td>
                                                        <td><?php echo htmlspecialchars($cita['nombre_asignador'] ?? 'No especificado'); ?></td>
                                                        <td>
                                                            <span class="status-badge <?php echo getStatusClass($cita['estado']); ?>">
                                                                <?php echo getStatusText($cita['estado']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="actions">
                                                            <a href="ver_cita.php?id=<?php echo $cita['id_cita']; ?>" class="btn-view">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <?php if ($cita['estado'] == 0): // Si está pendiente 
                                                            ?>
                                                                <a href="evaluacion.php?id=<?php echo $cita['id_cita']; ?>" class="btn-edit">
                                                                    <i class="fas fa-clipboard-check"></i>
                                                                </a>
                                                            <?php else: // Si está completada 
                                                            ?>
                                                                <a href="../includes/generate_pdf.php?id=<?php echo $cita['id_cita']; ?>" target="_blank" class="btn-pdf">
                                                                    <i class="fas fa-file-pdf"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($activeTab == 'citas-creadas'): ?>
                            <!-- Tab Citas Creadas -->
                            <div class="tab-content active">
                                <a href="nueva_cita.php" class="btn-add-user">
                                    <i class="fas fa-plus"></i> Nueva Cita de Evaluación
                                </a>

                                <div class="users-table">
                                    <table id="citas-creadas-table">
                                        <thead>
                                            <tr>
                                                <th>Folio</th>
                                                <th>Empresa</th>
                                                <th>Planta</th>
                                                <th>Fecha</th>
                                                <th>Hora</th>
                                                <th>Asignada A</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($citasCreadas)): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center">No has creado citas</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($citasCreadas as $cita): ?>
                                                    <tr>
                                                        <td><?php echo $cita['folio']; ?></td>
                                                        <td><?php echo htmlspecialchars($cita['nombre_empresa']); ?></td>
                                                        <td><?php echo htmlspecialchars($cita['nombre_planta']); ?></td>
                                                        <td><?php echo $cita['fecha_formateada']; ?></td>
                                                        <td><?php echo $cita['hora_formateada']; ?></td>
                                                        <td>
                                                            <?php if (empty($cita['nombre_asignado'])): ?>
                                                                <span class="status-badge inactive">Sin asignar</span>
                                                            <?php else: ?>
                                                                <?php echo htmlspecialchars($cita['nombre_asignado']); ?>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <span class="status-badge <?php echo getStatusClass($cita['estado']); ?>">
                                                                <?php echo getStatusText($cita['estado']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="actions">
                                                            <a href="ver_cita.php?id=<?php echo $cita['id_cita']; ?>" class="btn-view">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <?php if ($cita['estado'] == 0): // Si está pendiente 
                                                            ?>
                                                                <a href="editar_cita.php?id=<?php echo $cita['id_cita']; ?>" class="btn-edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="asignar_cita.php?id=<?php echo $cita['id_cita']; ?>" class="btn-assign">
                                                                    <i class="fas fa-user-plus"></i>
                                                                </a>
                                                                <a href="javascript:void(0)" onclick="confirmCancel('<?php echo $cita['id_cita']; ?>')" class="btn-delete">
                                                                    <i class="fas fa-times"></i>
                                                                </a>
                                                            <?php else: // Si está completada 
                                                            ?>
                                                                <a href="../includes/generate_pdf.php?id=<?php echo $cita['id_cita']; ?>" target="_blank" class="btn-pdf">
                                                                    <i class="fas fa-file-pdf"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Scripts JavaScript minimizados, solo para funcionalidades básicas -->
    <script>
        // Funciones para el sidebar en móviles
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.overlay').classList.toggle('active');
        }

        // Funciones para confirmaciones
        function confirmDelete(id, nombre, tipo) {
            let mensaje = '';
            let url = '';

            switch (tipo) {
                case 'user':
                    mensaje = `¿Estás seguro de que deseas eliminar al usuario "${nombre}"?`;
                    url = '../includes/delete_user.php?id=' + id;
                    break;
                case 'empresa':
                    mensaje = `¿Estás seguro de que deseas eliminar la empresa "${nombre}"?`;
                    url = '../includes/delete_empresa.php?id=' + id;
                    break;
                case 'planta':
                    mensaje = `¿Estás seguro de que deseas eliminar la planta "${nombre}"?`;
                    url = '../includes/delete_planta.php?id=' + id;
                    break;
            }

            if (confirm(mensaje + ' Esta acción no se puede deshacer.')) {
                window.location.href = url;
            }
        }

        function confirmCancel(idCita) {
            if (confirm('¿Estás seguro de que deseas cancelar esta cita? Esta acción no se puede deshacer.')) {
                window.location.href = '../includes/cancel_cita.php?id=' + idCita;
            }
        }

        // Ocultar alertas después de 5 segundos
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
    </script>
</body>




</html>