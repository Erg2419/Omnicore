-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-12-2025 a las 14:16:37
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
-- Base de datos: `gm`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alimentos`
--

CREATE TABLE `alimentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `calorias` decimal(8,2) DEFAULT NULL,
  `proteina` decimal(8,2) DEFAULT NULL,
  `grasa` decimal(8,2) DEFAULT NULL,
  `carbohidrato` decimal(8,2) DEFAULT NULL,
  `categoria` enum('fruta','verdura','cereal','proteina','lacteo','grasa','bebida','otro') DEFAULT NULL,
  `porcion_estandar` varchar(100) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alimentos_favoritos`
--

CREATE TABLE `alimentos_favoritos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `alimento_id` int(11) DEFAULT NULL,
  `fecha_agregado` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuraciones_usuario`
--

CREATE TABLE `configuraciones_usuario` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tema` varchar(50) DEFAULT 'claro',
  `notificaciones` tinyint(1) DEFAULT 1,
  `idioma` varchar(10) DEFAULT 'es',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuraciones_usuario`
--

INSERT INTO `configuraciones_usuario` (`id`, `usuario_id`, `tema`, `notificaciones`, `idioma`, `created_at`) VALUES
(1, 12, 'claro', 1, 'es', '2025-12-02 04:39:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejercicios`
--

CREATE TABLE `ejercicios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo_ejercicio` varchar(255) DEFAULT NULL,
  `duracion_minutos` int(11) DEFAULT NULL,
  `calorias_quemadas` decimal(8,2) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `intensidad` enum('baja','media','alta') DEFAULT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ejercicios`
--

INSERT INTO `ejercicios` (`id`, `usuario_id`, `tipo_ejercicio`, `duracion_minutos`, `calorias_quemadas`, `fecha_registro`, `intensidad`, `notas`) VALUES
(1, 1, '0', 100, 500.00, '2025-11-15 11:12:36', 'media', 'Me senti bien'),
(2, 1, 'Correr', 30, 360.00, '2025-11-17 10:43:11', 'media', 'Me senti muy bien'),
(3, 2, 'Caminata', 30, 120.00, '2025-11-20 10:59:00', 'media', 'Muy bien'),
(4, 1, 'Yoga', 30, 90.00, '2025-11-21 21:03:08', 'media', ''),
(5, 4, 'Correr', 30, 360.00, '2025-11-21 21:18:17', 'media', ''),
(6, 4, 'Pesas', 120, 720.00, '2025-11-21 21:20:07', 'media', 'muy biemmmmmmmmmmmmmmmm'),
(7, 1, 'CrossFit', 30, 390.00, '2025-11-25 11:31:45', 'media', ''),
(8, 7, 'Pesas', 60, 360.00, '2025-11-25 18:08:54', 'media', ''),
(9, 8, 'Pesas', 30, 180.00, '2025-11-25 20:12:44', 'media', ''),
(10, 9, 'Pesas', 30, 180.00, '2025-11-26 21:52:44', 'media', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logros`
--

CREATE TABLE `logros` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo_logro` enum('consistencia','peso','ejercicio','alimentacion') DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_obtencion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `logros`
--

INSERT INTO `logros` (`id`, `usuario_id`, `tipo_logro`, `descripcion`, `fecha_obtencion`) VALUES
(1, 2, 'peso', '¡Felicidades! Has alcanzado tu meta de ganar peso', '2025-11-20 11:01:10'),
(2, 3, 'alimentacion', 'Meta diaria de calorías alcanzada', '2025-11-23 08:24:40'),
(3, 3, 'alimentacion', 'Meta diaria de calorías alcanzada', '2025-11-23 08:29:37'),
(4, 8, 'peso', '¡Felicidades! Has alcanzado tu meta de perder peso', '2025-11-25 20:12:12'),
(5, 3, 'peso', '¡Felicidades! Has alcanzado tu meta de perder peso', '2025-12-02 00:10:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metas`
--

CREATE TABLE `metas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `meta_calorias` int(11) DEFAULT NULL,
  `meta_peso` decimal(5,2) DEFAULT NULL,
  `tipo_meta` enum('perder','ganar','mantener') DEFAULT NULL,
  `fecha_limite` date DEFAULT NULL,
  `recordatorio` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `metas`
--

INSERT INTO `metas` (`id`, `usuario_id`, `meta_calorias`, `meta_peso`, `tipo_meta`, `fecha_limite`, `recordatorio`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 1, 3000, 130.00, 'ganar', '2025-11-30', 1, '2025-11-15 12:00:35', '2025-11-17 10:43:33'),
(2, 2, 2500, 140.00, 'ganar', '2025-12-20', 0, '2025-11-20 11:00:31', '2025-11-20 11:00:31'),
(3, 3, 4000, 160.00, 'perder', '2026-01-31', 1, '2025-11-21 20:55:36', '2025-11-23 08:30:24'),
(4, 4, 1800, 160.00, 'perder', '2026-01-31', 1, '2025-11-21 21:17:01', '2025-11-21 21:17:01'),
(5, 7, 2500, 180.00, 'ganar', '2025-12-31', 0, '2025-11-25 18:08:06', '2025-11-25 18:08:06'),
(6, 8, 1800, 200.00, 'perder', '2026-01-16', 1, '2025-11-25 20:10:10', '2025-11-25 20:10:10'),
(7, 9, 2500, 140.00, 'ganar', '2026-01-30', 1, '2025-11-26 21:53:37', '2025-11-26 21:53:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `tipo` enum('recordatorio','logro','sistema','meta') DEFAULT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id`, `titulo`, `mensaje`, `tipo`, `leido`, `fecha_creacion`) VALUES
(1, 1, 'Meta Actualizada', 'Has actualizado tus metas de fitness', 'meta', 1, '2025-11-15 12:00:35'),
(2, 1, 'Meta Actualizada', 'Has actualizado tus metas de fitness', 'meta', 1, '2025-11-15 12:01:25'),
(3, 1, 'Meta Actualizada', 'Has actualizado tus metas de fitness', 'meta', 1, '2025-11-15 12:18:28'),
(4, 1, 'Meta Actualizada', 'Has actualizado tus metas de fitness', 'meta', 1, '2025-11-15 12:18:49'),
(6, 2, 'Meta Actualizada', 'Has actualizado tus metas de fitness', 'meta', 0, '2025-11-20 11:00:31'),
(7, 2, 'Meta de Peso Alcanzada', '¡Felicidades! Has alcanzado tu meta de ganar peso', 'meta', 0, '2025-11-20 11:01:10'),
(8, 3, 'Meta Actualizada', 'Has actualizado tus metas de fitness', 'meta', 0, '2025-11-21 20:55:36'),
(9, 4, 'Meta Actualizada', 'Has actualizado tus metas de fitness', 'meta', 1, '2025-11-21 21:17:01'),
(10, 3, 'Meta Alcanzada', '¡Felicidades! Has alcanzado tu meta diaria de calorías', 'meta', 0, '2025-11-23 08:24:40'),
(11, 3, 'Meta Alcanzada', '¡Felicidades! Has alcanzado tu meta diaria de calorías', 'meta', 0, '2025-11-23 08:29:37'),
(12, 3, 'Meta Actualizada', 'Has actualizado tus metas de fitness', 'meta', 0, '2025-11-23 08:30:24'),
(13, 7, 'Meta Actualizada', 'Has actualizado tus metas de fitness', 'meta', 0, '2025-11-25 18:08:06'),
(14, 8, 'Meta Actualizada', 'Has actualizado tus metas de fitness', 'meta', 0, '2025-11-25 20:10:10'),
(15, 8, 'Meta de Peso Alcanzada', '¡Felicidades! Has alcanzado tu meta de perder peso', 'meta', 0, '2025-11-25 20:12:12'),
(16, 9, 'Meta Actualizada', 'Has actualizado tus metas de fitness', 'meta', 0, '2025-11-26 21:53:37'),
(17, 3, 'Meta de Peso Alcanzada', '¡Felicidades! Has alcanzado tu meta de perder peso', 'meta', 0, '2025-12-02 00:10:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfiles_usuario`
--

CREATE TABLE `perfiles_usuario` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nivel_actividad` enum('sedentario','ligero','moderado','activo','muy_activo') DEFAULT NULL,
  `tipo_cuerpo` enum('ectomorfo','mesomorfo','endomorfo') DEFAULT NULL,
  `alergias` text DEFAULT NULL,
  `preferencias_alimenticias` text DEFAULT NULL,
  `condiciones_medicas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `perfiles_usuario`
--

INSERT INTO `perfiles_usuario` (`id`, `usuario_id`, `nivel_actividad`, `tipo_cuerpo`, `alergias`, `preferencias_alimenticias`, `condiciones_medicas`) VALUES
(1, 1, 'ligero', 'ectomorfo', 'ffgfff,nnjhj,nbhjjj,', 'nmmkj', 'nmmm');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes_alimentacion`
--

CREATE TABLE `planes_alimentacion` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nombre_plan` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `calorias_objetivo` int(11) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('activo','inactivo','completado') DEFAULT 'activo',
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plan_comidas`
--

CREATE TABLE `plan_comidas` (
  `id` int(11) NOT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `dia_semana` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') DEFAULT NULL,
  `tipo_comida` enum('Desayuno','Almuerzo','Merienda','Cena','Snack') DEFAULT NULL,
  `alimento_id` int(11) DEFAULT NULL,
  `cantidad` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `progreso_peso`
--

CREATE TABLE `progreso_peso` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `fecha_registro` date DEFAULT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `progreso_peso`
--

INSERT INTO `progreso_peso` (`id`, `usuario_id`, `peso`, `fecha_registro`, `notas`) VALUES
(1, 1, 122.00, '2025-11-15', ''),
(2, 1, 124.00, '2025-11-19', ''),
(3, 2, 145.00, '2025-11-20', 'Bien'),
(4, 4, 190.00, '2025-11-22', 'jjjj'),
(5, 1, 126.00, '2025-11-22', ''),
(6, 8, 198.00, '2025-11-26', ''),
(7, 9, 126.00, '2025-11-27', ''),
(8, 3, 129.00, '2025-12-02', ''),
(9, 12, 123.00, '2025-12-02', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recetas`
--

CREATE TABLE `recetas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `instrucciones` text DEFAULT NULL,
  `tiempo_preparacion` int(11) DEFAULT NULL,
  `porciones` int(11) DEFAULT NULL,
  `dificultad` enum('facil','media','dificil') DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` enum('publica','privada') DEFAULT 'privada',
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_comidas`
--

CREATE TABLE `registro_comidas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `comida` enum('Desayuno','Almuerzo','Merienda','Cena','Snack') DEFAULT NULL,
  `nombre_alimento` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `calorias` decimal(8,2) DEFAULT NULL,
  `proteina` decimal(8,2) DEFAULT NULL,
  `grasa` decimal(8,2) DEFAULT NULL,
  `carbohidrato` decimal(8,2) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registro_comidas`
--

INSERT INTO `registro_comidas` (`id`, `usuario_id`, `comida`, `nombre_alimento`, `descripcion`, `calorias`, `proteina`, `grasa`, `carbohidrato`, `imagen`, `fecha_registro`) VALUES
(1, 2, 'Desayuno', 'Pan con huevo', 'hhhh', 234.00, 45.00, 7.00, 6.00, '', '2025-11-20 11:01:54'),
(2, 3, 'Desayuno', 'Papa', 'uuihhu', 200.00, 6.00, 89.00, 566.00, '', '2025-11-21 20:53:53'),
(3, 3, 'Almuerzo', 'huevo', 'yyyy', 567.00, 77.00, 77.00, 66.00, '', '2025-11-21 20:56:47'),
(4, 1, 'Merienda', 'gg', '', 567.00, 0.00, 0.00, 0.00, '', '2025-11-21 20:58:04'),
(5, 4, 'Merienda', 'avena', 'hhjjjuj', 200.00, 88.00, 0.00, 457.00, '', '2025-11-21 21:22:15'),
(6, 3, 'Cena', 'hhhhhhhhhhh', '', 667.00, 0.00, 0.00, 0.00, 'uploads/1763900431_6922fc0f4a7f5.jpeg', '2025-11-23 08:21:04'),
(7, 3, 'Cena', 'hhhhhhhhhhh', '', 667.00, 0.00, 0.00, 0.00, '', '2025-11-23 08:24:26'),
(8, 3, 'Cena', 'hhhhhhhhhhh', '', 667.00, 0.00, 0.00, 0.00, '', '2025-11-23 08:24:40'),
(9, 3, 'Cena', 'hhhhhhhhhhh', '', 667.00, 0.00, 0.00, 0.00, '', '2025-11-23 08:29:37'),
(10, 1, 'Almuerzo', 'hhhdh', '', 190.00, 0.00, 0.00, 0.00, '', '2025-11-23 14:34:43'),
(11, 1, 'Desayuno', 'gggggggggggggg', '', 234.00, 0.00, 0.00, 0.00, '', '2025-11-25 11:31:34'),
(12, 7, 'Desayuno', 'Arroz', '', 150.00, 45.00, 8.00, 256.00, 'uploads/1764108354_69262842da7bd.jpeg', '2025-11-25 18:07:15'),
(13, 8, 'Desayuno', 'Arroz', '', 200.00, 0.00, 0.00, 0.00, 'uploads/1764115635_692644b30ea45.jpeg', '2025-11-25 20:08:57'),
(14, 9, 'Desayuno', 'manzana', 'Muy buena estaba la manzana', 290.00, 89.00, 0.00, 8.00, 'uploads/1764208225_6927ae613ace0.png', '2025-11-26 21:51:02'),
(15, 3, 'Snack', 'hhh', '', 234.00, 0.00, 0.00, 0.00, '', '2025-12-01 23:45:43'),
(16, 1, 'Snack', 'nnnnn', '', 342.00, 0.00, 0.00, 0.00, '', '2025-12-02 00:24:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuari`
--

CREATE TABLE `usuari` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `face_descriptor` text DEFAULT NULL,
  `edad` int(11) DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `altura` decimal(4,2) DEFAULT NULL,
  `genero` enum('Masculino','Femenino','Otro') DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `face_data` text DEFAULT NULL,
  `face_id_enabled` tinyint(1) DEFAULT 0,
  `face_image` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `face_descriptor`, `edad`, `peso`, `altura`, `genero`, `foto`, `fecha_registro`, `fecha_actualizacion`, `estado`, `face_data`, `face_id_enabled`, `face_image`) VALUES
(1, 'Jonairy', 'jonairisanchez@gmail.com', '$2y$10$SSY5ezQD.yT/v6JoVhg8kOQS.LEuDsni4bKl6y8wzzyC6QJB39itO', NULL, NULL, 126.00, NULL, NULL, NULL, '2025-11-14 19:10:28', '2025-11-24 18:10:33', 'activo', 'data:image/jpeg;base64,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', 1, NULL),
(2, 'Ismael', 'ismael@gmail.com', '$2y$10$z3DDh2uG30gkA0XfLKXgjukbz.Q8F6h6oyvFN4CfibPxyGSdwvUUK', NULL, NULL, 145.00, NULL, NULL, NULL, '2025-11-20 10:57:52', '2025-11-20 11:01:10', 'activo', NULL, 0, NULL),
(3, 'jose', 'jose@gmail.com', '$2y$10$pqoanM3VioZktttzu84GH.yMVeoev3GWKoVdmjrtz9cZQ0wdMST26', NULL, NULL, 129.00, NULL, NULL, NULL, '2025-11-21 20:51:21', '2025-12-02 00:10:50', 'activo', NULL, 0, NULL),
(4, 'jose', 'josei@gmail.com', '$2y$10$XEnbx5dzKruEdX7G/u2bgea2YTj4zPVt4suhv7QRTtUhQr4mUS5LK', NULL, NULL, 190.00, NULL, NULL, NULL, '2025-11-21 21:08:23', '2025-11-21 21:25:43', 'activo', NULL, 0, NULL),
(5, 'Wendy', 'wendy@gmail.com', '$2y$10$pJkhfGIIiWDKQciX01DUIunnF6np3IEWNMH7p6H2Mu8whbVi7Jjyy', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-23 10:29:44', '2025-11-23 11:47:25', 'activo', NULL, 0, NULL);
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `face_descriptor`, `edad`, `peso`, `altura`, `genero`, `foto`, `fecha_registro`, `fecha_actualizacion`, `estado`, `face_data`, `face_id_enabled`, `face_image`) VALUES
(6, 'yo', 'yo@gmail.com', '$2y$10$bHFG58EuR0wWghuzDArgdOR5t3ctSPuNTNSGESmaWzwPolu68uplO', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-23 11:53:12', '2025-11-23 11:53:12', 'activo', NULL, 0, 'data:image/jpeg;base64,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'),
(7, 'Albin', 'albin@gmail.com', '$2y$10$7.4efb.dfivT4yqlIMLEzubabGj/morlWoiDZgbJFEqgQsQ3ieh9O', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-25 18:04:27', '2025-11-25 18:04:27', 'activo', NULL, 0, NULL),
(8, 'Juan', 'juan@gmail.com', '$2y$10$wK9ZXl5UEWGuiqL3Je6Dkuov0tv7M3UaPsg93tWkEGXpz8EPNktU2', NULL, NULL, 198.00, NULL, NULL, NULL, '2025-11-25 20:05:22', '2025-11-25 20:12:12', 'activo', NULL, 0, NULL),
(9, 'Nathali', 'nathali@gmail.com', '$2y$10$r4LjRIZMShQ/kC9GocCffOJdCuZc0KLWqKi696fkKzCQ9tdtlJSMa', NULL, NULL, 126.00, NULL, NULL, NULL, '2025-11-26 21:45:00', '2025-11-26 21:59:26', 'activo', NULL, 0, NULL),
(10, 'jonas', 'jonas@gmail.com', '$2y$10$Ax1lf.Lu7nl5d9xLiQuxjO1/WmzAlhpKhpEp6yogRqmIKdO7NAMYa', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-02 00:29:28', '2025-12-02 00:29:28', 'activo', NULL, 0, NULL),
(11, 'ko', 'k@gmail.com', '$2y$10$J97TfkEHyKuTm5zFALTReev/O8vRz1gsE8ZWO6vePgjnYdFbHUU/W', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-02 00:33:13', '2025-12-02 00:33:13', 'activo', NULL, 0, NULL),
(12, 'n', 'n@gmail.com', '$2y$10$2gnpwha48lZUeMGqUt9iGu3KJ4Oe0.yFcWhoGOSPgZWawPA9WlzWi', NULL, NULL, 123.00, NULL, NULL, NULL, '2025-12-02 00:39:22', '2025-12-02 00:40:31', 'activo', NULL, 0, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alimentos`
--
ALTER TABLE `alimentos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `alimentos_favoritos`
--
ALTER TABLE `alimentos_favoritos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `alimento_id` (`alimento_id`);

--
-- Indices de la tabla `configuraciones_usuario`
--
ALTER TABLE `configuraciones_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `ejercicios`
--
ALTER TABLE `ejercicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `logros`
--
ALTER TABLE `logros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `metas`
--
ALTER TABLE `metas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `perfiles_usuario`
--
ALTER TABLE `perfiles_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `planes_alimentacion`
--
ALTER TABLE `planes_alimentacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `plan_comidas`
--
ALTER TABLE `plan_comidas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `alimento_id` (`alimento_id`);

--
-- Indices de la tabla `progreso_peso`
--
ALTER TABLE `progreso_peso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `registro_comidas`
--
ALTER TABLE `registro_comidas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `usuari`
--
ALTER TABLE `usuari`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

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
-- AUTO_INCREMENT de la tabla `alimentos`
--
ALTER TABLE `alimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `alimentos_favoritos`
--
ALTER TABLE `alimentos_favoritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuraciones_usuario`
--
ALTER TABLE `configuraciones_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ejercicios`
--
ALTER TABLE `ejercicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `logros`
--
ALTER TABLE `logros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `metas`
--
ALTER TABLE `metas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `perfiles_usuario`
--
ALTER TABLE `perfiles_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `planes_alimentacion`
--
ALTER TABLE `planes_alimentacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `plan_comidas`
--
ALTER TABLE `plan_comidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `progreso_peso`
--
ALTER TABLE `progreso_peso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `recetas`
--
ALTER TABLE `recetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `registro_comidas`
--
ALTER TABLE `registro_comidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `usuari`
--
ALTER TABLE `usuari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alimentos_favoritos`
--
ALTER TABLE `alimentos_favoritos`
  ADD CONSTRAINT `alimentos_favoritos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alimentos_favoritos_ibfk_2` FOREIGN KEY (`alimento_id`) REFERENCES `alimentos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `configuraciones_usuario`
--
ALTER TABLE `configuraciones_usuario`
  ADD CONSTRAINT `configuraciones_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ejercicios`
--
ALTER TABLE `ejercicios`
  ADD CONSTRAINT `ejercicios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `logros`
--
ALTER TABLE `logros`
  ADD CONSTRAINT `logros_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `metas`
--
ALTER TABLE `metas`
  ADD CONSTRAINT `metas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `perfiles_usuario`
--
ALTER TABLE `perfiles_usuario`
  ADD CONSTRAINT `perfiles_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `planes_alimentacion`
--
ALTER TABLE `planes_alimentacion`
  ADD CONSTRAINT `planes_alimentacion_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `plan_comidas`
--
ALTER TABLE `plan_comidas`
  ADD CONSTRAINT `plan_comidas_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `planes_alimentacion` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `plan_comidas_ibfk_2` FOREIGN KEY (`alimento_id`) REFERENCES `alimentos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `progreso_peso`
--
ALTER TABLE `progreso_peso`
  ADD CONSTRAINT `progreso_peso_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD CONSTRAINT `recetas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `registro_comidas`
--
ALTER TABLE `registro_comidas`
  ADD CONSTRAINT `registro_comidas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
