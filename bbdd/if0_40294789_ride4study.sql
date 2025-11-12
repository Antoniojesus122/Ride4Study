-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-11-2025 a las 00:03:04
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
  `fechaPublicacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `anuncios`
--

INSERT INTO `anuncios` (`idAnuncio`, `idUsuario`, `tipo`, `origen`, `destino`, `fechaSalida`, `horaSalida`, `horaRegreso`, `plazasDisponibles`, `precio`, `descripcion`, `fechaPublicacion`) VALUES
(1, 1, 'ofrezco', 3, 1, '2025-11-02', '07:30:00', '15:00:00', 3, 8.00, 'Salida desde el centro de Lepe, regreso por la tarde.', '2025-10-29 23:32:38'),
(2, 1, 'ofrezco', 18, 17, '2025-10-31', '08:00:00', NULL, 2, 1.00, NULL, '2025-10-29 23:36:09'),
(3, 1, 'busco', 1, 9, '2025-10-31', '02:00:00', NULL, 5, 4.00, NULL, '2025-10-29 23:36:34'),
(4, 1, 'busco', 11, 12, '2025-10-31', '08:00:00', '10:03:00', NULL, 2.00, NULL, '2025-10-29 23:44:02'),
(5, 1, 'ofrezco', 10, 17, '2025-11-02', '02:00:00', '10:00:00', 2, NULL, NULL, '2025-10-29 23:46:16'),
(6, 1, 'busco', 5, 10, '2025-10-31', '02:00:00', '06:00:00', 4, NULL, NULL, '2025-10-29 23:50:24'),
(7, 8, 'ofrezco', 6, 4, '2025-11-12', '03:04:00', NULL, 3, NULL, NULL, '2025-11-12 22:04:35'),
(8, 8, 'ofrezco', 12, 20, '2025-11-28', '03:03:00', '06:06:00', 4, NULL, NULL, '2025-11-12 22:05:08'),
(9, 8, 'ofrezco', 3, 17, '2025-11-29', '04:04:00', NULL, 4, NULL, NULL, '2025-11-12 22:06:08');

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
  `idEmisor` int(11) NOT NULL,
  `idReceptor` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `fechaCreacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`idMensaje`, `idEmisor`, `idReceptor`, `mensaje`, `fechaCreacion`, `leido`) VALUES
(1, 5, 8, 'Hola', '2025-11-12 22:37:17', 1),
(2, 8, 5, 'Hey', '2025-11-12 22:37:43', 1),
(3, 5, 8, 'Waaa', '2025-11-12 22:37:54', 1),
(4, 5, 8, 'jejeje', '2025-11-12 22:37:58', 1),
(5, 8, 5, 'Que guay', '2025-11-12 22:38:01', 1),
(6, 8, 5, 'En verdad', '2025-11-12 22:38:03', 1),
(7, 8, 5, 'Si', '2025-11-12 22:38:06', 1),
(8, 8, 5, 'Señor', '2025-11-12 22:38:07', 1),
(9, 8, 5, 'ffew', '2025-11-12 22:38:13', 1),
(10, 5, 8, 'fewf', '2025-11-12 22:48:13', 1),
(11, 5, 8, 'fwe', '2025-11-12 22:48:14', 1),
(12, 5, 8, 'fwe', '2025-11-12 22:48:15', 1),
(13, 5, 8, 'Hola', '2025-11-12 22:59:05', 1),
(14, 5, 8, 'Hola', '2025-11-12 23:01:24', 1),
(15, 5, 8, 'Que tal', '2025-11-12 23:01:29', 1),
(16, 8, 5, 'Bien bien', '2025-11-12 23:01:39', 1),
(17, 8, 5, 'Y tu ??', '2025-11-12 23:01:44', 1),
(18, 5, 8, 'Nah muy bien', '2025-11-12 23:01:48', 1),
(19, 8, 5, 'Me alegro jeje', '2025-11-12 23:01:52', 1),
(20, 5, 1, 'Hey', '2025-11-12 23:02:16', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `idNotificacion` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `tipoNotificacion` enum('email','sistema') NOT NULL,
  `mensaje` text NOT NULL,
  `fechaEnvio` datetime DEFAULT current_timestamp()
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
(2, 'Usuario');

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
  `foto_perfil` varchar(255) DEFAULT NULL,
  `biografia` text DEFAULT NULL,
  `contrasena` varchar(255) NOT NULL,
  `idRol` int(11) NOT NULL,
  `estado_verificacion` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: No verificado, 1: Pendiente, 2: Verificado',
  `documento_verificacion` varchar(255) DEFAULT NULL,
  `nota_admin` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idUsuario`, `nombre`, `correo`, `telefono`, `foto_perfil`, `biografia`, `contrasena`, `idRol`, `estado_verificacion`, `documento_verificacion`, `nota_admin`) VALUES
(1, 'Antonio Jesús', 'antoniojesusgonzalezdomingo4@gmail.com', '624897163', NULL, NULL, '$2y$10$A3aXkdZ8N2CSdD14.89TZuq.khcYO6yKq4XWHNFSYVM2IdM21Zzti', 2, 0, NULL, NULL),
(2, 'Admin', 'admin@ride4study.local', '600000000', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 0, NULL, NULL),
(3, 'Antonio Jesús', 'ibt_ag2@yopmail.com', '624897163', NULL, NULL, '$2y$10$k7Sx5fs0kCDgHuiEBGtLeuYvpXW7vJIJyEzGSmDn/ri8.3hXpukZO', 2, 0, NULL, NULL),
(4, 'Antonio Jesús', 'ibt_ag9@yopmail.com', '', NULL, NULL, '$2y$10$0VRX1y4fQg5ETpV.gZKK/.Qmx4tfUNVOSaQ0Pf6JVwtNSxli8bDYu', 2, 0, NULL, NULL),
(5, 'Antonio Jesús', 'ibt_ag10@yopmail.com', '', NULL, NULL, '$2y$10$eYKHDf7MME2mUc07v/ESfugs9QJD/Bh5xTmEZ2dRsqhM9614NxtvK', 2, 1, '6915085dcb57d-reza-madani-UI6feF4NbQs-unsplash.jpg', NULL),
(6, 'Administrador', 'admin@ride4study.com', '600000000', NULL, NULL, '$2y$10$YcPnD9StN5jL1BqOq7wHkeHTdY9aHw.5Fh0A1r7SV3gIfhTzKkSm2', 1, 0, NULL, NULL),
(7, 'Manuel Hernandez', 'antoniodomingo.gd@gmail.com', '', NULL, NULL, '$2y$10$aA2cOTEE6OXyk4FMutL6CezP2OPP7QSRLFaLDCkzvd06gSfQwxlvq', 2, 0, NULL, NULL),
(8, 'González Domingo', 'ibt_11@yopmail.com', '', NULL, NULL, '$2y$10$N06dxbYdBrxjGpiDJX.Bx.I6cdRrUoWK4Xn4Mp.C8RbzyftvRO.my', 2, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `valoraciones`
--

CREATE TABLE `valoraciones` (
  `idValoracion` int(11) NOT NULL,
  `idValorador` int(11) NOT NULL,
  `idValorado` int(11) NOT NULL,
  `puntuacion` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `viajes`
--

CREATE TABLE `viajes` (
  `idViaje` int(11) NOT NULL,
  `idAnuncio` int(11) NOT NULL,
  `idConductor` int(11) NOT NULL,
  `idPasajero` int(11) NOT NULL,
  `estado` enum('pendiente','parcial','verificado','no_verificado') DEFAULT 'pendiente',
  `fechaSalida` datetime DEFAULT NULL,
  `fechaRegreso` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `viajes`
--

INSERT INTO `viajes` (`idViaje`, `idAnuncio`, `idConductor`, `idPasajero`, `estado`, `fechaSalida`, `fechaRegreso`) VALUES
(1, 6, 1, 7, 'pendiente', '2025-10-29 23:50:24', '2025-10-29 23:50:24');

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
  ADD KEY `idUsuario` (`idUsuario`);

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
-- Indices de la tabla `viajes`
--
ALTER TABLE `viajes`
  ADD PRIMARY KEY (`idViaje`),
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
  MODIFY `idAnuncio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `localidades`
--
ALTER TABLE `localidades`
  MODIFY `idLocalidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `idMensaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `idNotificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `idRol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  MODIFY `idSesion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`idEmisor`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`idReceptor`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
