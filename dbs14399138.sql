-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Servidor: db5018152599.hosting-data.io
-- Tiempo de generación: 25-07-2025 a las 06:59:25
-- Versión del servidor: 10.11.13-MariaDB-log
-- Versión de PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dbs14399138`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_trabajo` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Volcado de datos para la tabla `likes`
--

INSERT INTO `likes` (`id`, `id_usuario`, `id_trabajo`, `fecha_creacion`) VALUES
(3, 1, 12, '2025-07-23 08:11:10'),
(8, 1, 15, '2025-07-23 10:40:52'),
(11, 1, 16, '2025-07-24 11:33:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyectos`
--

CREATE TABLE `proyectos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `descripcion` text NOT NULL DEFAULT current_timestamp(),
  `imagen` varchar(100) DEFAULT NULL,
  `favorito` tinyint(4) DEFAULT NULL,
  `programas_usados` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Volcado de datos para la tabla `proyectos`
--

INSERT INTO `proyectos` (`id`, `usuario_id`, `titulo`, `fecha_publicacion`, `descripcion`, `imagen`, `favorito`, `programas_usados`) VALUES
(15, 10, 'Diseño de imagen,  papelería corporativa y merchandising', '2025-07-23 07:06:44', 'Proyecto de fin de curso para Diseño y Edición de Publicaciones Impresas y Multimedia. \r\n\r\n \r\n\r\nEmpresa dedicada al entretenimiento y a la diversión para todo tipo de edades mediante el videojuego y los juegos de mesa. Creación de logotipo, con manual de identidad corporativa.\r\n\r\n​\r\n\r\nLos colores del logotipo los puse así porque me pareció que con fondo negro destacaban muy bien, y la identidad de marca iría toda con el fondo negro, al igual que la papelería corporativa y el merchandising. \r\n\r\n​\r\n\r\nAl ser un establecimiento en el cual solo se escucha rock, boceté la mano con el símbolo roquero cogiendo un mando de consola.', '68808a049c09b.png', NULL, 'img/LOGOS/Adobe Indesgn.png, img/LOGOS/Adobe Photoshop.png'),
(16, 13, 'Ilustración de retratos', '2025-07-23 08:12:38', 'Estas ilustraciones las he hecho en Adobe Illustrator, con el efecto como si se hubiese arrancado un trozo de la foto e ilustrado en ese hueco, o viceversa.\r\n\r\n', '6880997697f3a.png', NULL, 'img/LOGOS/Adobe Illustrator.png, img/LOGOS/Adobe Indesgn.png, img/LOGOS/Adobe Photoshop.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `ultima_conexion` datetime DEFAULT NULL,
  `token` varchar(255) NOT NULL,
  `token_recuperacion` varchar(255) NOT NULL,
  `verificado` tinyint(1) NOT NULL DEFAULT 0,
  `intentos_fallidos` int(11) NOT NULL DEFAULT 0,
  `bloqueado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `email`, `password`, `nombre`, `apellido`, `fecha_registro`, `ultima_conexion`, `token`, `token_recuperacion`, `verificado`, `intentos_fallidos`, `bloqueado`) VALUES
(10, 'laurapalomocastaeda@gmail.com', '$2y$10$D.nOBJpdGfqoywMrVgTqJeHAe/w8Up1dSB90YMo4mHTnHyCLx8Ybu', 'lauradeoz', '', '2025-07-21 12:28:50', '2025-07-25 08:55:03', '', '', 1, 0, 0),
(13, 'lauradeozdesign@gmail.com', '$2y$10$RbhhhOYHDS6aOeVXvSRJbe7L00DAMq6oP1nKTQ0TAeqU1FDsgT4uW', 'Lauradeoz2', '', '2025-07-21 12:59:38', '2025-07-24 12:04:16', '', '', 1, 0, 0),
(14, 'miguerubsk@gmail.com', '$2y$10$G2osYBx9CZlex5oTn613pOPK3v9dNZylf34qOMgIY/8Luf2H7U2.S', 'miguerubsk', '', '2025-07-22 10:03:21', '2025-07-22 21:11:00', '', '', 1, 0, 0),
(16, 'rosalas921@gmail.com', '$2y$10$DfmRHTavgzCsyBH7m51Nd.gC.Kz3N9Mbi/hCMQKkpu3KZO2KUm2/u', 'ro', '', '2025-07-23 12:39:54', '2025-07-23 12:40:36', '', '', 1, 0, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`,`id_trabajo`);

--
-- Indices de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ususario_id` (`usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
