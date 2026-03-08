-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-03-2026 a las 17:16:05
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `if0_40294789_ride4study`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anuncios`
--

CREATE TABLE `anuncios` (
  `idAnuncio` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `tipo` enum('ofrezco','busco') NOT NULL,
  `origen` int(11) NOT NULL,
  `destino` int(11) NOT NULL,
  `fechaSalida` date NOT NULL,
  `horaSalida` time NOT NULL,
  `horaRegreso` time DEFAULT NULL,
  `plazasDisponibles` int(11) DEFAULT NULL,
  `precio` decimal(6,2) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fechaPublicacion` datetime DEFAULT current_timestamp(),
  `destacado` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = anuncio destacado, aparece primero en el dashboard'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `anuncios`
--

INSERT INTO `anuncios` (`idAnuncio`, `idUsuario`, `tipo`, `origen`, `destino`, `fechaSalida`, `horaSalida`, `horaRegreso`, `plazasDisponibles`, `precio`, `descripcion`, `fechaPublicacion`, `destacado`) VALUES
(1, 1, 'ofrezco', 3, 1, '2025-11-02', '07:30:00', '15:00:00', 3, 8.00, 'Salida desde el centro de Lepe, regreso por la tarde.', '2025-10-29 23:32:38', 0),
(2, 1, 'ofrezco', 18, 17, '2025-10-31', '08:00:00', NULL, 2, 1.00, NULL, '2025-10-29 23:36:09', 0),
(3, 1, 'busco', 1, 9, '2025-10-31', '02:00:00', NULL, 5, 4.00, NULL, '2025-10-29 23:36:34', 0),
(4, 1, 'busco', 11, 12, '2025-10-31', '08:00:00', '10:03:00', NULL, 2.00, NULL, '2025-10-29 23:44:02', 0),
(5, 1, 'ofrezco', 10, 17, '2025-11-02', '02:00:00', '10:00:00', 2, NULL, NULL, '2025-10-29 23:46:16', 0),
(6, 1, 'busco', 5, 10, '2025-10-31', '02:00:00', '06:00:00', 4, NULL, NULL, '2025-10-29 23:50:24', 0),
(7, 8, 'ofrezco', 6, 4, '2025-11-12', '03:04:00', NULL, 3, NULL, NULL, '2025-11-12 22:04:35', 0),
(8, 8, 'ofrezco', 12, 20, '2025-11-28', '03:03:00', '06:06:00', 4, NULL, NULL, '2025-11-12 22:05:08', 0),
(9, 8, 'ofrezco', 3, 17, '2025-11-29', '04:04:00', NULL, 4, NULL, NULL, '2025-11-12 22:06:08', 0),
(10, 1, 'ofrezco', 18, 3, '2026-01-23', '03:02:00', '11:01:00', 2, NULL, 'Esto es una prueba', '2026-01-11 23:26:23', 0),
(13, 9, 'ofrezco', 2, 12, '2026-02-12', '20:20:00', NULL, 1, NULL, '', '2026-02-09 23:20:13', 0),
(14, 9, 'ofrezco', 4, 16, '2026-02-13', '17:45:00', NULL, 0, NULL, '', '2026-02-10 17:45:38', 0),
(15, 9, 'ofrezco', 20, 13, '2026-02-25', '21:37:00', NULL, 1, NULL, '', '2026-02-18 16:32:57', 0),
(17, 5, 'ofrezco', 2, 11, '2026-02-18', '01:56:00', NULL, 1, NULL, '', '2026-02-18 22:55:18', 0),
(19, 9, 'ofrezco', 18, 8, '2026-02-25', '20:49:00', NULL, 1, NULL, '', '2026-02-22 18:48:05', 0),
(20, 9, 'ofrezco', 14, 15, '2026-02-24', '23:54:00', NULL, 2, NULL, '', '2026-02-22 18:50:13', 0),
(21, 5, 'ofrezco', 9, 20, '2026-02-28', '20:51:00', NULL, 0, NULL, '', '2026-02-26 17:48:26', 0),
(25, 9, 'busco', 6, 17, '2026-02-28', '03:56:00', NULL, 0, NULL, '', '2026-02-26 23:52:49', 0),
(26, 5, 'ofrezco', 9, 13, '2026-02-28', '14:22:00', NULL, 0, NULL, '', '2026-02-28 14:21:31', 0),
(30, 9, 'busco', 1, 9, '2026-03-23', '17:25:00', NULL, 0, NULL, '', '2026-03-08 15:23:17', 0),
(31, 9, 'ofrezco', 10, 9, '2026-03-17', '18:27:00', NULL, 0, NULL, '', '2026-03-08 15:24:57', 0),
(32, 9, 'ofrezco', 6, 10, '2026-03-18', '20:54:00', NULL, 0, NULL, '', '2026-03-08 15:50:20', 0),
(33, 5, 'ofrezco', 11, 17, '2026-03-24', '20:08:00', NULL, 1, NULL, '', '2026-03-08 17:05:21', 0),
(34, 5, 'ofrezco', 14, 15, '2026-03-17', '20:07:00', NULL, 3, NULL, '', '2026-03-08 17:05:35', 0),
(35, 5, 'busco', 11, 17, '2026-03-10', '21:09:00', NULL, 1, NULL, '', '2026-03-08 17:05:43', 0),
(36, 5, 'ofrezco', 2, 7, '2026-03-18', '18:05:00', NULL, 4, NULL, '', '2026-03-08 17:05:56', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conversations`
--

CREATE TABLE `conversations` (
  `idConversation` int(11) NOT NULL,
  `idAnuncio` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `conversations`
--

INSERT INTO `conversations` (`idConversation`, `idAnuncio`, `user1_id`, `user2_id`, `created_at`) VALUES
(5, 21, 5, 9, '2026-02-26 17:40:00'),
(12, 30, 5, 9, '2026-03-08 14:23:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instituciones`
--

CREATE TABLE `instituciones` (
  `idInstitucion` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `localidades`
--

CREATE TABLE `localidades` (
  `idLocalidad` int(11) NOT NULL,
  `nombreLocalidad` varchar(100) NOT NULL,
  `provincia` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `localidades`
--

INSERT INTO `localidades` (`idLocalidad`, `nombreLocalidad`, `provincia`) VALUES
(1, 'Huelva', 'Huelva'),
(2, 'Aljaraque', 'Huelva'),
(3, 'Lepe', 'Huelva'),
(4, 'Isla Cristina', 'Huelva'),
(5, 'Ayamonte', 'Huelva'),
(6, 'Cartaya', 'Huelva'),
(7, 'Punta Umbría', 'Huelva'),
(8, 'Moguer', 'Huelva'),
(9, 'Palos de la Frontera', 'Huelva'),
(10, 'Gibraleón', 'Huelva'),
(11, 'Bollullos Par del Condado', 'Huelva'),
(12, 'La Palma del Condado', 'Huelva'),
(13, 'Valverde del Camino', 'Huelva'),
(14, 'Trigueros', 'Huelva'),
(15, 'Almonte', 'Huelva'),
(16, 'Rociana del Condado', 'Huelva'),
(17, 'Lucena del Puerto', 'Huelva'),
(18, 'Beas', 'Huelva'),
(19, 'Villarrasa', 'Huelva'),
(20, 'San Juan del Puerto', 'Huelva');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `idMensaje` int(11) NOT NULL,
  `idConversation` int(11) NOT NULL,
  `idEmisor` int(11) NOT NULL,
  `idReceptor` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` enum('normal','sistema') DEFAULT 'normal' COMMENT 'Tipo de mensaje: normal (usuario) o sistema (contexto automático)',
  `fechaCreacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`idMensaje`, `idConversation`, `idEmisor`, `idReceptor`, `mensaje`, `tipo`, `fechaCreacion`, `leido`) VALUES
(30, 0, 9, 5, 'Hola!', 'normal', '2026-02-16 16:32:41', 1),
(31, 0, 5, 9, 'Hey!', 'normal', '2026-02-16 16:33:19', 0),
(33, 2, 5, 9, 'y', 'normal', '2026-02-18 16:03:08', 1),
(37, 9, 29, 5, 'Buenas tio ', 'normal', '2026-03-05 22:25:26', 1),
(38, 10, 29, 5, 'Buenas', 'normal', '2026-03-07 15:07:49', 1),
(39, 10, 29, 5, 'Te puedo llevar', 'normal', '2026-03-07 15:07:53', 1),
(40, 11, 29, 5, 'Hola!', 'normal', '2026-03-07 15:09:00', 1),
(41, 11, 29, 5, 'Te puedo llevar', 'normal', '2026-03-07 15:09:03', 1),
(42, 12, 5, 9, 'Buenas', 'normal', '2026-03-08 14:23:23', 1),
(43, 12, 5, 9, 'Te puedo llevar', 'normal', '2026-03-08 14:23:26', 1),
(48, 12, 9, 5, 'fds', 'normal', '2026-03-08 15:20:23', 0),
(49, 12, 9, 5, 'gfrefg', 'normal', '2026-03-08 15:24:10', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `idNotificacion` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `tipoNotificacion` enum('email','sistema') NOT NULL,
  `mensaje` text NOT NULL,
  `fechaEnvio` datetime DEFAULT current_timestamp(),
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `icono` varchar(60) NOT NULL DEFAULT 'fas fa-bell',
  `url` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `code`, `expires_at`, `created_at`) VALUES
(12, 1, '309466', '2026-02-15 16:36:18', '2026-02-15 15:21:18'),
(13, 5, '110389', '2026-02-18 22:59:34', '2026-02-18 21:44:34'),
(19, 9, '993372', '2026-03-07 16:19:22', '2026-03-07 15:04:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `idReporte` int(11) NOT NULL,
  `tipo` enum('usuario','anuncio','chat') NOT NULL,
  `idUsuarioReportado` int(11) DEFAULT NULL,
  `idAnuncio` int(11) DEFAULT NULL,
  `idChat` int(11) DEFAULT NULL,
  `idUsuarioQueReporta` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `estado` enum('pendiente','resuelto') DEFAULT 'pendiente',
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `idRol` int(11) NOT NULL,
  `nombreRol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`idRol`, `nombreRol`) VALUES
(1, 'Administrador'),
(2, 'Usuario'),
(3, 'Usuario Premium'),
(4, 'Institución');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones`
--

CREATE TABLE `sesiones` (
  `idSesion` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `fechaInicio` datetime DEFAULT current_timestamp(),
  `fechaFin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `idUsuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `vehiculo` varchar(150) DEFAULT NULL,
  `institucion` varchar(150) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `biografia` text DEFAULT NULL,
  `contrasena` varchar(255) NOT NULL,
  `idRol` int(11) NOT NULL,
  `estado_verificacion` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: No verificado, 1: Pendiente, 2: Verificado',
  `documento_verificacion` varchar(255) DEFAULT NULL,
  `nota_admin` text DEFAULT NULL,
  `visibilidad_perfil` enum('public','registered','private') DEFAULT 'public',
  `visibilidad_telefono` enum('public','rides_only') DEFAULT 'rides_only',
  `notificaciones_email` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `premium` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = usuario con plan premium activo',
  `premium_hasta` datetime DEFAULT NULL COMMENT 'Fecha de expiración del plan premium',
  `stripe_customer_id` varchar(255) DEFAULT NULL COMMENT 'ID de cliente en Stripe para pagos recurrentes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idUsuario`, `nombre`, `correo`, `telefono`, `ciudad`, `vehiculo`, `institucion`, `foto_perfil`, `biografia`, `contrasena`, `idRol`, `estado_verificacion`, `documento_verificacion`, `nota_admin`, `visibilidad_perfil`, `visibilidad_telefono`, `notificaciones_email`, `creado_en`, `premium`, `premium_hasta`, `stripe_customer_id`) VALUES
(1, 'Antonio Jesús', 'antoniojesusgonzalezdomingo4@gmail.com', '624897163', 'Lepe', 'Citroen xsara', 'IES La Arboleda', NULL, 'Hola! Soy nuevo por aquí', '$2y$10$.JGyk1dI3aN.ZjW3Op2YKeJ0kCE4FxS/hzNKdL4U1qDA6lTU5ga2W', 1, 1, '6965129c1d554-Captura de pantalla 2026-01-11 235340.png', NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(2, 'Admin', 'admin@ride4study.local', '600000000', NULL, NULL, NULL, NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 0, NULL, NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(3, 'Antonio Jesús', 'ibt_ag2@yopmail.com', '624897163', NULL, NULL, NULL, NULL, NULL, '$2y$10$k7Sx5fs0kCDgHuiEBGtLeuYvpXW7vJIJyEzGSmDn/ri8.3hXpukZO', 2, 0, NULL, NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(4, 'Antonio Jesús', 'ibt_ag9@yopmail.com', '', NULL, NULL, NULL, NULL, NULL, '$2y$10$0VRX1y4fQg5ETpV.gZKK/.Qmx4tfUNVOSaQ0Pf6JVwtNSxli8bDYu', 2, 0, NULL, NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(5, 'Antonio Jesús', 'ibt_ag10@yopmail.com', '624897163', 'Lepe', '', '', 'profile_698e529350944.jpg', '', '$2y$10$JZjf8jpr1JKEg5S2GKcyGO03cElQRVGExQnaViX4FxusvmcgcOv76', 2, 1, '6915085dcb57d-reza-madani-UI6feF4NbQs-unsplash.jpg', NULL, 'public', 'public', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(6, 'Administrador', 'admin@ride4study.com', '600000000', NULL, NULL, NULL, NULL, NULL, '$2y$10$YcPnD9StN5jL1BqOq7wHkeHTdY9aHw.5Fh0A1r7SV3gIfhTzKkSm2', 1, 0, NULL, NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(7, 'Manuel Hernandez', 'antoniodomingo.gd@gmail.com', '', NULL, NULL, NULL, NULL, NULL, '$2y$10$aA2cOTEE6OXyk4FMutL6CezP2OPP7QSRLFaLDCkzvd06gSfQwxlvq', 2, 0, NULL, NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(8, 'González Domingo', 'ibt_11@yopmail.com', '', NULL, NULL, NULL, NULL, NULL, '$2y$10$N06dxbYdBrxjGpiDJX.Bx.I6cdRrUoWK4Xn4Mp.C8RbzyftvRO.my', 2, 0, NULL, NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(9, 'Fernando Domingo', 'ibt_ag120@yopmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$wRC7JgGK23TfxJrieun27egyBWccpJ0NLUade0uChwCIGtAZGyqvm', 2, 1, '69ad940034d4a-Captura de pantalla 2026-03-08 162129.png', NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(10, '', 'ibt_ag12@yopmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 'Antonio122', 1, 0, NULL, NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(12, 'admin', 'ibt_ag14@yopmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 'Antonio122', 1, 0, NULL, NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(13, 'Paco', 'ibt_02@yopmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$kwSkiKcAWTCEb06/DlZQKu5SM6eGRf2qOkLfvHrV/wwqqecxt72Bq', 2, 0, NULL, NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(24, 'Pedro', 'ag@yopmail.com', '624897163', NULL, NULL, NULL, NULL, NULL, '$2y$10$7gJYR2mJHdvRaRBOmrX/SO9bu3/hArQwm1HiIgHy4BOg3yfXDnXm2', 2, 0, NULL, NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(25, 'Prueba', 'ag12@yopmail.com', '0', NULL, NULL, NULL, NULL, NULL, '$2y$10$aMjZ1kFAVDasUx3tjHkC/OUicpalRK85rfgqYo1PAlZ4Dk4yh4iPS', 2, 0, NULL, NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(26, 'fewrf', 'ibt_ag1320@yopmail.com', '0', NULL, NULL, NULL, NULL, NULL, '$2y$10$xVeedDxKL1eAjtBDjwqI0.o5LjBkzzBu8Q2V72P6105DtjfFT.gF2', 2, 0, NULL, NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(27, 'González Domingo', 'ibt_ag4320@yopmail.com', '423432432', NULL, NULL, NULL, NULL, NULL, '$2y$10$KSlMQQzRQogMrWK2qEBLNePHIWt4SJWnsJiA76VDWIe4yB0hUN8we', 2, 0, NULL, NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(28, 'Antonio', 'ag01@yopmail.com', '624897163', NULL, NULL, NULL, NULL, NULL, '$2y$10$2nU1crqEcs0V4N6XHHj.JOzEa7UqzNiUi4kF1osr0TxUn7GJRV/Qq', 2, 0, NULL, NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL),
(29, 'Paco', 'paco@yopmail.com', '624897163', NULL, NULL, NULL, NULL, NULL, '$2y$10$ZlEV1PIn3YA7NGXc3KbJdujWumVnSSoYNkIX69jSVVxSkI2cK2nHK', 2, 0, NULL, NULL, 'public', 'rides_only', 1, '2026-03-08 15:19:45', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `valoraciones`
--

CREATE TABLE `valoraciones` (
  `idValoracion` int(11) NOT NULL,
  `idViaje` int(11) DEFAULT NULL,
  `idValorador` int(11) NOT NULL,
  `idValorado` int(11) NOT NULL,
  `puntuacion` tinyint(4) NOT NULL,
  `puntualidad` tinyint(4) DEFAULT NULL COMMENT 'Valoración de puntualidad (1-5)',
  `comunicacion` tinyint(4) DEFAULT NULL COMMENT 'Valoración de comunicación (1-5)',
  `vehiculo` tinyint(4) DEFAULT NULL COMMENT 'Valoración del vehículo (1-5, solo conductores)',
  `conduccion` tinyint(4) DEFAULT NULL COMMENT 'Valoración de la conducción (1-5, solo conductores)',
  `comportamiento` tinyint(4) DEFAULT NULL COMMENT 'Valoración del comportamiento (1-5)',
  `comentario` text DEFAULT NULL COMMENT 'Comentario escrito opcional',
  `respuesta` text DEFAULT NULL COMMENT 'Respuesta del usuario valorado',
  `fecha_respuesta` datetime DEFAULT NULL,
  `fecha_valoracion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `valoraciones`
--

INSERT INTO `valoraciones` (`idValoracion`, `idViaje`, `idValorador`, `idValorado`, `puntuacion`, `puntualidad`, `comunicacion`, `vehiculo`, `conduccion`, `comportamiento`, `comentario`, `respuesta`, `fecha_respuesta`, `fecha_valoracion`) VALUES
(0, NULL, 5, 9, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-27 14:05:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `viajes`
--

CREATE TABLE `viajes` (
  `idViaje` int(11) NOT NULL,
  `idAnuncio` int(11) NOT NULL,
  `idConductor` int(11) NOT NULL,
  `idPasajero` int(11) NOT NULL,
  `estado` enum('pendiente','aceptado','rechazado') NOT NULL DEFAULT 'pendiente',
  `fechaSalida` datetime DEFAULT NULL,
  `fechaRegreso` datetime DEFAULT NULL,
  `notificacion_valoracion_enviada` datetime DEFAULT NULL COMMENT 'Fecha en que se envió email de valoración'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `viajes`
--

INSERT INTO `viajes` (`idViaje`, `idAnuncio`, `idConductor`, `idPasajero`, `estado`, `fechaSalida`, `fechaRegreso`, `notificacion_valoracion_enviada`) VALUES
(1, 10, 1, 9, '', '2026-01-23 03:02:00', NULL, NULL),
(2, 6, 1, 7, 'pendiente', '2025-10-29 23:50:24', '2025-10-29 23:50:24', NULL),
(4, 14, 9, 5, '', NULL, NULL, NULL),
(5, 15, 9, 5, '', NULL, NULL, NULL),
(6, 15, 9, 28, '', NULL, NULL, NULL),
(7, 21, 5, 9, 'aceptado', NULL, NULL, '2026-03-01 15:28:40'),
(10, 25, 5, 9, 'pendiente', NULL, NULL, NULL),
(11, 26, 5, 9, 'aceptado', NULL, NULL, '2026-02-28 14:24:14'),
(18, 31, 9, 5, 'aceptado', NULL, NULL, NULL),
(19, 30, 5, 9, 'aceptado', NULL, NULL, NULL),
(20, 32, 9, 5, 'aceptado', NULL, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `anuncios`
--
ALTER TABLE `anuncios`
  ADD PRIMARY KEY (`idAnuncio`),
  ADD KEY `fk_anuncio_usuario` (`idUsuario`),
  ADD KEY `fk_anuncio_origen` (`origen`),
  ADD KEY `fk_anuncio_destino` (`destino`);

--
-- Indices de la tabla `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`idConversation`),
  ADD UNIQUE KEY `idAnuncio` (`idAnuncio`,`user1_id`,`user2_id`),
  ADD KEY `user1_id` (`user1_id`),
  ADD KEY `user2_id` (`user2_id`);

--
-- Indices de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  ADD PRIMARY KEY (`idInstitucion`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `localidades`
--
ALTER TABLE `localidades`
  ADD PRIMARY KEY (`idLocalidad`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`idMensaje`),
  ADD KEY `idEmisor` (`idEmisor`),
  ADD KEY `idReceptor` (`idReceptor`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`idNotificacion`),
  ADD KEY `idUsuario` (`idUsuario`),
  ADD KEY `idx_notif_usuario_leida` (`idUsuario`,`leida`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code` (`code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`idReporte`),
  ADD KEY `fk_report_user_reportado` (`idUsuarioReportado`),
  ADD KEY `fk_report_anuncio` (`idAnuncio`),
  ADD KEY `fk_report_user_que_reporta` (`idUsuarioQueReporta`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`idRol`);

--
-- Indices de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD PRIMARY KEY (`idSesion`),
  ADD KEY `idUsuario` (`idUsuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `idRol` (`idRol`);

--
-- Indices de la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  ADD UNIQUE KEY `unique_valoracion_viaje` (`idViaje`,`idValorador`),
  ADD KEY `idx_valoracion_viaje` (`idViaje`),
  ADD KEY `idx_valoracion_fecha` (`fecha_valoracion`),
  ADD KEY `idx_valoracion_valorador_valorado` (`idValorador`,`idValorado`);

--
-- Indices de la tabla `viajes`
--
ALTER TABLE `viajes`
  ADD PRIMARY KEY (`idViaje`),
  ADD UNIQUE KEY `unique_reserva` (`idAnuncio`,`idPasajero`),
  ADD KEY `fk_viaje_anuncio` (`idAnuncio`),
  ADD KEY `fk_viaje_conductor` (`idConductor`),
  ADD KEY `fk_viaje_pasajero` (`idPasajero`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `anuncios`
--
ALTER TABLE `anuncios`
  MODIFY `idAnuncio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `conversations`
--
ALTER TABLE `conversations`
  MODIFY `idConversation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  MODIFY `idInstitucion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `localidades`
--
ALTER TABLE `localidades`
  MODIFY `idLocalidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `idMensaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `idNotificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `idReporte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `idRol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  MODIFY `idSesion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `viajes`
--
ALTER TABLE `viajes`
  MODIFY `idViaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`idAnuncio`) REFERENCES `anuncios` (`idAnuncio`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`user1_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_3` FOREIGN KEY (`user2_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`idEmisor`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`idReceptor`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `fk_report_anuncio` FOREIGN KEY (`idAnuncio`) REFERENCES `anuncios` (`idAnuncio`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_report_user_que_reporta` FOREIGN KEY (`idUsuarioQueReporta`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_report_user_reportado` FOREIGN KEY (`idUsuarioReportado`) REFERENCES `usuarios` (`idUsuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  ADD CONSTRAINT `fk_valoracion_viaje` FOREIGN KEY (`idViaje`) REFERENCES `viajes` (`idViaje`) ON DELETE CASCADE;

--
-- Filtros para la tabla `viajes`
--
ALTER TABLE `viajes`
  ADD CONSTRAINT `fk_viaje_anuncio` FOREIGN KEY (`idAnuncio`) REFERENCES `anuncios` (`idAnuncio`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_viaje_conductor` FOREIGN KEY (`idConductor`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_viaje_pasajero` FOREIGN KEY (`idPasajero`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
