<?php
session_start();
// Verificar si el usuario ya está autenticado
if (isset($_SESSION['id_usuario'])) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Pack - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Estilo para login.css */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Arial', sans-serif;
}

body {
    background-color: #f5f5f5;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    padding: 20px;
}

.login-container {
    width: 100%;
    max-width: 420px;
    padding: 20px;
    animation: fadeIn 0.8s ease-in-out;
}

.login-box {
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 30px;
    text-align: center;
    transform: translateY(0);
    animation: slideInUp 0.5s ease-out;
    transition: transform 0.3s, box-shadow 0.3s;
}

.login-box:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    transform: translateY(-5px);
}

.login-header {
    margin-bottom: 25px;
}

.logo {
    max-width: 200px;
    margin-bottom: 15px;
    animation: pulse 2s infinite;
}

h2 {
    color: #212121;
    font-size: 24px;
    margin-bottom: 5px;
    position: relative;
    display: inline-block;
}

h2:after {
    content: '';
    position: absolute;
    width: 50%;
    height: 2px;
    bottom: -5px;
    left: 25%;
    background-color: #e91e29;
    transform: scaleX(0);
    transition: transform 0.3s;
    animation: expandWidth 1s forwards 0.5s;
}

.alert {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 5px;
    text-align: left;
    animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}

.alert-error {
    background-color: #ffebee;
    color: #d32f2f;
    border: 1px solid #ffcdd2;
}

.form-group {
    margin-bottom: 20px;
    text-align: left;
    position: relative;
    overflow: hidden;
}

.form-group:nth-child(1) {
    animation: slideInLeft 0.5s forwards 0.2s;
    opacity: 0;
}

.form-group:nth-child(2) {
    animation: slideInLeft 0.5s forwards 0.4s;
    opacity: 0;
}

label {
    display: block;
    margin-bottom: 8px;
    color: #424242;
    font-weight: 500;
    transition: color 0.3s;
}

input[type="text"],
input[type="password"] {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #e0e0e0;
    border-radius: 5px;
    font-size: 16px;
    transition: all 0.3s;
}

input[type="text"]:focus,
input[type="password"]:focus {
    outline: none;
    border-color: #e91e29;
    box-shadow: 0 0 0 2px rgba(233, 30, 41, 0.2);
}

.form-group:hover label {
    color: #e91e29;
}

.btn-login {
    width: 100%;
    padding: 12px;
    background-color: #e91e29;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    overflow: hidden;
    animation: slideInUp 0.5s forwards 0.6s;
    opacity: 0;
}

.btn-login:hover {
    background-color: #c1161f;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.btn-login:active {
    transform: translateY(1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-login::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: 0.5s;
}

.btn-login:hover::before {
    left: 100%;
}

.btn-login i {
    margin-left: 8px;
    animation: bounceIn 1s;
}

/* Adaptar iconos si los utilizas */
.form-group i {
    margin-right: 8px;
    color: #555;
    transition: color 0.3s;
}

.form-group:hover i {
    color: #e91e29;
    transform: scale(1.1);
}

/* Animaciones */
@keyframes fadeIn {
    0% { opacity: 0; }
    100% { opacity: 1; }
}

@keyframes slideInUp {
    0% { 
        transform: translateY(20px);
        opacity: 0;
    }
    100% { 
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideInLeft {
    0% { 
        transform: translateX(-20px);
        opacity: 0;
    }
    100% { 
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes expandWidth {
    0% { transform: scaleX(0); }
    100% { transform: scaleX(1); }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.03); }
    100% { transform: scale(1); }
}

@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}

@keyframes bounceIn {
    0%, 20%, 40%, 60%, 80%, 100% { transition-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000); }
    0% { opacity: 0; transform: scale3d(.3, .3, .3); }
    20% { transform: scale3d(1.1, 1.1, 1.1); }
    40% { transform: scale3d(.9, .9, .9); }
    60% { opacity: 1; transform: scale3d(1.03, 1.03, 1.03); }
    80% { transform: scale3d(.97, .97, .97); }
    100% { opacity: 1; transform: scale3d(1, 1, 1); }
}
/* Añadir estos estilos al CSS existente */
.password-container {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #777;
    transition: color 0.3s;
    z-index: 10;
    padding: 5px;
}

.password-toggle:hover {
    color: #e91e29;
}

.form-group input[type="password"],
.form-group input[type="text"].password-visible {
    padding-right: 40px;
}
        </style>
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="../assets/images/smartpack_largo.jpeg" alt="Smart Pack" class="logo">
                <h2>Iniciar Sesión</h2>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <form action="../includes/authenticate.php" method="POST">
                <div class="form-group">
                    <label for="usuario"><i class="fas fa-user"></i> Usuario</label>
                    <input type="text" id="usuario" name="usuario" required>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required>
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="password-icon"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">Ingresar <i class="fas fa-sign-in-alt"></i></button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordField.classList.add('password-visible');
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                passwordField.classList.remove('password-visible');
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>