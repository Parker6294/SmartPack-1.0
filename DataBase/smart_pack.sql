-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-03-2025 a las 20:32:17
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `smart_pack`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas_evaluacion`
--

CREATE TABLE `citas_evaluacion` (
  `id_cita` int(11) NOT NULL,
  `id_usuario_asignador` int(11) NOT NULL,
  `id_usuario_asignado` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `id_planta` int(11) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_programada` date NOT NULL,
  `hora_programada` time NOT NULL,
  `estado` enum('pendiente','en_proceso','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  `folio` varchar(20) NOT NULL,
  `cliente` varchar(100) DEFAULT NULL,
  `domicilio` varchar(255) DEFAULT NULL,
  `solicitante` varchar(100) DEFAULT NULL,
  `maquina` varchar(100) DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `no_serie` varchar(100) DEFAULT NULL,
  `linea` varchar(100) DEFAULT NULL,
  `notas_adicionales` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_evaluacion`
--

CREATE TABLE `detalles_evaluacion` (
  `id_detalle` int(11) NOT NULL,
  `id_cita` int(11) NOT NULL,
  `objetivo_pruebas` text DEFAULT NULL,
  `condiciones_iniciales` text DEFAULT NULL,
  `condiciones_envolvedora` text DEFAULT NULL,
  `tipo_carga` enum('estable','inestable','ligera','pesada') DEFAULT NULL,
  `descripcion_carga` text DEFAULT NULL,
  `dimensiones_carga_largo` float DEFAULT NULL,
  `dimensiones_carga_ancho` float DEFAULT NULL,
  `dimensiones_carga_altura` float DEFAULT NULL,
  `dimensiones_tarima_largo` float DEFAULT NULL,
  `dimensiones_tarima_ancho` float DEFAULT NULL,
  `dimensiones_tarima_altura` float DEFAULT NULL,
  `observaciones_conclusiones` text DEFAULT NULL,
  `fecha_completado` timestamp NULL DEFAULT NULL,
  `nombre_usuario` varchar(100) DEFAULT NULL,
  `nombre_rodisa` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id_empresa` int(11) NOT NULL,
  `nombre_empresa` varchar(100) NOT NULL,
  `rfc` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `sitio_web` varchar(100) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id_empresa`, `nombre_empresa`, `rfc`, `direccion`, `telefono`, `correo`, `sitio_web`, `estado`, `fecha_creacion`) VALUES
(1, 'Empresa Demo', 'DEMO123456XYZ', 'Av. Principal 123, Ciudad', '555-123-4567', 'contacto@empresademo.com', 'www.empresademo.com', 1, '2025-02-28 15:10:47'),
(2, 'Industrias XYZ', 'IXYZ987654ABC', 'Calle Industrial 456, Ciudad', '555-987-6543', 'info@industriasxyz.com', 'www.industriasxyz.com', 1, '2025-02-28 15:10:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones_citas`
--

CREATE TABLE `notificaciones_citas` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_cita` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `leida` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantas`
--

CREATE TABLE `plantas` (
  `id_planta` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `nombre_planta` varchar(100) NOT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `codigo_planta` varchar(20) DEFAULT NULL,
  `responsable` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `plantas`
--

INSERT INTO `plantas` (`id_planta`, `id_empresa`, `nombre_planta`, `ubicacion`, `codigo_planta`, `responsable`, `telefono`, `correo`, `estado`, `fecha_creacion`) VALUES
(1, 1, 'Planta Norte', 'Zona Industrial Norte', 'PLN-001', 'Juan Pérez', '555-111-2222', 'jperez@empresademo.com', 1, '2025-02-28 15:10:47'),
(2, 1, 'Planta Sur', 'Zona Industrial Sur', 'PLS-002', 'María García', '555-333-4444', 'mgarcia@empresademo.com', 1, '2025-02-28 15:10:47'),
(3, 2, 'Planta Central', 'Parque Industrial Este', 'PLC-001', 'Roberto López', '555-555-6666', 'rlopez@industriasxyz.com', 1, '2025-02-28 15:10:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pruebas_evaluacion`
--

CREATE TABLE `pruebas_evaluacion` (
  `id_prueba` int(11) NOT NULL,
  `id_detalle` int(11) NOT NULL,
  `numero_prueba` int(11) NOT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `calibre` varchar(20) DEFAULT NULL,
  `ancho` float DEFAULT NULL,
  `estirado` float DEFAULT NULL,
  `vueltas_arriba` int(11) DEFAULT NULL,
  `vueltas_centro` int(11) DEFAULT NULL,
  `vueltas_abajo` int(11) DEFAULT NULL,
  `vueltas_totales` int(11) DEFAULT NULL,
  `retencion_arriba` float DEFAULT NULL,
  `retencion_centro` float DEFAULT NULL,
  `retencion_abajo` float DEFAULT NULL,
  `rendimiento` float DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`, `estado`, `fecha_creacion`) VALUES
(1, 'Administrador', 1, '2025-02-22 19:12:51'),
(2, 'Usuario', 1, '2025-02-22 19:12:51'),
(3, 'Lector', 1, '2025-02-28 15:07:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `ultimo_acceso` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `usuario`, `password`, `correo`, `id_rol`, `estado`, `ultimo_acceso`, `fecha_creacion`) VALUES
(1, 'Administrador Sistema', 'admin', 'admin123', 'admin@smartpack.com', 1, 1, '2025-03-03 17:47:51', '2025-02-22 19:12:53'),
(2, 'Usuario Regular', 'user', 'user123', 'user@smartpack.com', 2, 0, '2025-02-28 15:08:39', '2025-02-22 19:12:53');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas_evaluacion`
--
ALTER TABLE `citas_evaluacion`
  ADD PRIMARY KEY (`id_cita`),
  ADD KEY `id_usuario_asignador` (`id_usuario_asignador`),
  ADD KEY `id_usuario_asignado` (`id_usuario_asignado`),
  ADD KEY `id_empresa` (`id_empresa`),
  ADD KEY `id_planta` (`id_planta`);

--
-- Indices de la tabla `detalles_evaluacion`
--
ALTER TABLE `detalles_evaluacion`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_cita` (`id_cita`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id_empresa`);

--
-- Indices de la tabla `notificaciones_citas`
--
ALTER TABLE `notificaciones_citas`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_cita` (`id_cita`);

--
-- Indices de la tabla `plantas`
--
ALTER TABLE `plantas`
  ADD PRIMARY KEY (`id_planta`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Indices de la tabla `pruebas_evaluacion`
--
ALTER TABLE `pruebas_evaluacion`
  ADD PRIMARY KEY (`id_prueba`),
  ADD KEY `id_detalle` (`id_detalle`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas_evaluacion`
--
ALTER TABLE `citas_evaluacion`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_evaluacion`
--
ALTER TABLE `detalles_evaluacion`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `notificaciones_citas`
--
ALTER TABLE `notificaciones_citas`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `plantas`
--
ALTER TABLE `plantas`
  MODIFY `id_planta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pruebas_evaluacion`
--
ALTER TABLE `pruebas_evaluacion`
  MODIFY `id_prueba` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas_evaluacion`
--
ALTER TABLE `citas_evaluacion`
  ADD CONSTRAINT `citas_evaluacion_ibfk_1` FOREIGN KEY (`id_usuario_asignador`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `citas_evaluacion_ibfk_2` FOREIGN KEY (`id_usuario_asignado`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `citas_evaluacion_ibfk_3` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`),
  ADD CONSTRAINT `citas_evaluacion_ibfk_4` FOREIGN KEY (`id_planta`) REFERENCES `plantas` (`id_planta`);

--
-- Filtros para la tabla `detalles_evaluacion`
--
ALTER TABLE `detalles_evaluacion`
  ADD CONSTRAINT `detalles_evaluacion_ibfk_1` FOREIGN KEY (`id_cita`) REFERENCES `citas_evaluacion` (`id_cita`);

--
-- Filtros para la tabla `notificaciones_citas`
--
ALTER TABLE `notificaciones_citas`
  ADD CONSTRAINT `notificaciones_citas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `notificaciones_citas_ibfk_2` FOREIGN KEY (`id_cita`) REFERENCES `citas_evaluacion` (`id_cita`);

--
-- Filtros para la tabla `plantas`
--
ALTER TABLE `plantas`
  ADD CONSTRAINT `plantas_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pruebas_evaluacion`
--
ALTER TABLE `pruebas_evaluacion`
  ADD CONSTRAINT `pruebas_evaluacion_ibfk_1` FOREIGN KEY (`id_detalle`) REFERENCES `detalles_evaluacion` (`id_detalle`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
