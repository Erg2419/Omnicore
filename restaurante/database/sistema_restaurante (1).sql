-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-12-2025 a las 19:18:15
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
-- Base de datos: `sistema_restaurante`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `agregar_producto_orden` (IN `p_id_orden` INT, IN `p_id_producto` INT, IN `p_cantidad` INT)   BEGIN
    DECLARE v_precio DECIMAL(10,2);
    DECLARE v_subtotal DECIMAL(10,2);
    
    -- Obtener precio del producto
    SELECT precio INTO v_precio FROM productos WHERE id_producto = p_id_producto;
    
    SET v_subtotal = v_precio * p_cantidad;
    
    -- Insertar detalle
    INSERT INTO detalle_orden (id_orden, id_producto, cantidad, precio_unitario, subtotal)
    VALUES (p_id_orden, p_id_producto, p_cantidad, v_precio, v_subtotal);
    
    -- Actualizar total de la orden
    UPDATE ordenes 
    SET total = total + v_subtotal 
    WHERE id_orden = p_id_orden;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `crear_orden` (IN `p_id_mesa` INT, IN `p_id_empleado` INT, IN `p_tipo_orden` ENUM('mesa','domicilio','recoger'))   BEGIN
    INSERT INTO ordenes (id_mesa, id_empleado, tipo_orden)
    VALUES (p_id_mesa, p_id_empleado, p_tipo_orden);
    
    SELECT LAST_INSERT_ID() as id_orden;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`, `estado`, `fecha_creacion`) VALUES
(1, 'Entradas', 'Platos para comenzar la experiencia culinaria', 'activo', '2025-11-24 18:15:56'),
(2, 'Platos Fuertes', 'Platos principales del restaurante', 'activo', '2025-11-24 18:15:56'),
(3, 'Postres', 'Deliciosos postres para finalizar', 'activo', '2025-11-24 18:15:56'),
(10, 'Bebidas', 'Bebidas refrescantes', 'activo', '2025-11-27 01:41:04'),
(11, 'Ensaladas', 'Ensaladas saludables para ti', 'activo', '2025-11-27 01:42:55'),
(12, 'Vinos', 'Vinos disponibles para el consumo del cliente', 'activo', '2025-11-27 01:51:08'),
(13, 'Sopas y Cremas', 'Sopas y Cremas de todo tipo', 'activo', '2025-11-27 02:06:50'),
(14, 'Sándwiches y Wraps', 'Sándwiches y Wraps para todas horas', 'activo', '2025-11-27 02:08:02'),
(15, 'Mariscos', 'Sección de comida marina', 'activo', '2025-11-27 02:08:39'),
(16, 'Desayunos / Brunch', 'Desayunos de todo tipo', 'activo', '2025-11-27 02:09:30'),
(17, 'Pastas y Risottos', 'Pastas Italianas para los amantes', 'activo', '2025-11-27 02:10:10'),
(18, 'Menú infantil', 'Menu para los mas peques', 'activo', '2025-11-27 02:10:31'),
(19, 'Pizzas', 'Pizzas Italianas especialidades del chef', 'activo', '2025-11-27 02:11:10'),
(20, 'Acompañamientos', 'Acompañamientos con los que completar', 'activo', '2025-11-27 02:12:10'),
(21, 'Sushi', 'Sushis', 'activo', '2025-11-27 15:27:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `telefono`, `email`, `fecha_registro`) VALUES
(1, 'Cliente General', NULL, NULL, '2025-11-24 18:15:56'),
(7, 'Javier Milei', NULL, NULL, '2025-11-27 20:56:12'),
(8, 'Javier Milei', '(829) 227 9891', NULL, '2025-11-27 22:24:26'),
(9, 'Javier Milei', '(809) 999-7687', NULL, '2025-11-27 22:32:36'),
(10, 'Javier Milei', '(829) 229-1294', NULL, '2025-11-27 23:18:13'),
(11, 'Javier Milei', '(809) 999-7687', NULL, '2025-11-27 23:20:38'),
(12, 'Joel Mendoza', NULL, NULL, '2025-11-27 23:22:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden`
--

CREATE TABLE `detalle_orden` (
  `id_detalle` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('pendiente','en_preparacion','listo','entregado') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id_empleado` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `puesto` enum('mesero','cocinero','cajero','administrador') NOT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `contrasena` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_contratacion` date DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `foto_perfil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id_empleado`, `nombre`, `telefono`, `email`, `puesto`, `usuario`, `contrasena`, `estado`, `fecha_contratacion`, `fecha_creacion`, `foto_perfil`) VALUES
(1, 'J. Martinez', '(809) 527 4938', 'jabeseliasm@gmail.com', 'administrador', 'J. Martinez', 'password', 'activo', NULL, '2025-11-24 18:15:56', 'uploads/perfiles/perfil_1_1764613999.png'),
(2, 'Carlos López', '987654322', 'carlos@restaurante.com', 'mesero', 'clopez', '123456', 'activo', NULL, '2025-11-24 18:15:56', NULL),
(3, 'María Torres', '987654323', 'maria@restaurante.com', 'cocinero', 'mtorres', '123456', 'activo', NULL, '2025-11-24 18:15:56', NULL),
(4, 'Marta Martina', '(829) 229-1294', 'martina@gmail.com', 'mesero', 'marta', '1234', 'activo', '2025-11-27', '2025-11-27 23:15:58', NULL),
(5, 'Laura Sepulveda', '(809) 555-1234', 'laura@gmail.com', 'mesero', 'laura', '1234', 'activo', '2025-12-01', '2025-12-01 18:13:11', 'uploads/perfiles/perfil_5_1764613293.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id_inventario` int(11) NOT NULL,
  `nombre_ingrediente` varchar(200) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `unidad_medida` varchar(20) NOT NULL,
  `stock_minimo` decimal(10,2) DEFAULT 0.00,
  `proveedor` varchar(150) DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu`
--

CREATE TABLE `menu` (
  `id_producto` int(11) NOT NULL,
  `nombre_producto` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `tiempo_preparacion` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `id_mesa` int(11) NOT NULL,
  `numero_mesa` varchar(10) NOT NULL,
  `capacidad` int(11) NOT NULL,
  `ubicacion` varchar(100) DEFAULT NULL,
  `estado` enum('disponible','ocupada','reservada','mantenimiento') DEFAULT 'disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id_mesa`, `numero_mesa`, `capacidad`, `ubicacion`, `estado`) VALUES
(14, 'M01', 4, 'Sala Principal', 'disponible'),
(15, 'M02', 8, 'Terraza', 'disponible'),
(16, 'M03', 2, 'Sala Principal', 'disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes`
--

CREATE TABLE `ordenes` (
  `id_orden` int(11) NOT NULL,
  `id_mesa` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_empleado` int(11) NOT NULL,
  `estado` enum('pendiente','confirmada','en_preparacion','lista','entregada','cancelada','pagada') DEFAULT 'pendiente',
  `tipo_orden` enum('mesa','domicilio','recoger') DEFAULT 'mesa',
  `total` decimal(10,2) DEFAULT 0.00,
  `observaciones` text DEFAULT NULL,
  `fecha_orden` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `metodo_pago` enum('efectivo','tarjeta_credito','tarjeta_debito','transferencia') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `cambio` decimal(10,2) DEFAULT 0.00,
  `estado` enum('pendiente','completado','fallido') DEFAULT 'pendiente',
  `referencia` varchar(100) DEFAULT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` enum('disponible','no_disponible') DEFAULT 'disponible',
  `tiempo_preparacion` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `descripcion`, `precio`, `id_categoria`, `imagen`, `estado`, `tiempo_preparacion`, `fecha_creacion`) VALUES
(2, 'Lomo Salteado', 'Clásico peruano con lomo, cebolla, tomate y papas fritas', 349.95, 2, 'uploads/productos/6927ae50b3c05.jpeg', 'disponible', 20, '2025-11-24 18:15:56'),
(4, 'Tiramisú', 'Postre italiano con café y mascarpone', 149.95, 3, 'uploads/productos/6927ae6ef1320.jpeg', 'disponible', 5, '2025-11-24 18:15:56'),
(8, 'Palitos de pan', '4 palitos de pan horneado con salsa de tomate', 69.99, 1, 'uploads/productos/6927adeac1015.jpeg', 'disponible', 10, '2025-11-27 01:15:18'),
(9, 'Jugo de Limon', 'Jugo refrescante de limón ', 74.95, 10, 'uploads/productos/6927ac77248d5.jpeg', 'disponible', 5, '2025-11-27 01:42:15'),
(10, 'Ensalada Cesar', 'Ensalada Cesar saludable acompañada de aderezo', 95.95, 11, 'uploads/productos/6927ad101b281.jpg', 'disponible', 14, '2025-11-27 01:44:48'),
(11, '19 Crimes Red Wine', 'Vino tinto australiano de cuerpo medio, con una mezcla equilibrada de uvas que resaltan notas de frutos rojos maduros, vainilla tostada y un toque de especias suaves. Su sabor amplio y aterciopelado deja un final cálido y persistente, ideal para acompañar carnes, pastas y platos intensos.\r\nUna elección perfecta para quienes buscan un vino con carácter y personalidad.', 499.95, 12, 'uploads/productos/6927afc5eed49.jpeg', 'disponible', 0, '2025-11-27 01:56:21'),
(12, 'Refresco Coca Cola', 'Refresco de la marca Coca Cola', 49.95, 10, 'uploads/productos/6927b09d8f8d6.jpeg', 'disponible', 2, '2025-11-27 01:59:57'),
(13, 'Puré de papas', 'Pure de papas con un toque de puerro por encima', 89.95, 20, 'uploads/productos/6927b3d6b2ce6.jpeg', 'disponible', 20, '2025-11-27 02:13:42'),
(14, 'Arroz blanco', 'Arroz Blanco con semillas de sesame', 109.95, 20, 'uploads/productos/6927b480cb31e.jpeg', 'disponible', 45, '2025-11-27 02:16:32'),
(15, 'Papas fritas', 'Papas fritas con sal', 59.95, 20, 'uploads/productos/6927b4f411092.jpeg', 'disponible', 25, '2025-11-27 02:18:28'),
(16, 'Vegetales salteados', 'Vegetales salteados al vapor', 94.95, 20, 'uploads/productos/6927b5624f671.jpeg', 'disponible', 32, '2025-11-27 02:20:18'),
(17, 'Pizza Margherita', 'Pizza de tomate San Marzano, mozzarella fresca, albahaca y aceite de oliva.\r\nLa reina clásica de Nápoles. (Personal)', 119.95, 19, 'uploads/productos/6927b5d51ad3b.jpeg', 'disponible', 60, '2025-11-27 02:22:13'),
(18, 'Pizza Marinara', 'Pizza de tomate, ajo, orégano y aceite de oliva.\r\n(Personal)', 114.95, 19, 'uploads/productos/6927b6c4ebba5.jpeg', 'disponible', 50, '2025-11-27 02:26:12'),
(19, 'Pizza Quattro Formaggi', 'Pizza de Mozzarella, gorgonzola, parmesano y fontina.(Personal)', 199.95, 19, 'uploads/productos/6927b74684074.jpeg', 'disponible', 60, '2025-11-27 02:28:22'),
(20, 'Pizza Prosciutto e Funghi', 'Mozzarella, tomate, jamón prosciutto y hongos frescos. (Personal)', 209.95, 19, 'uploads/productos/6927b7bd00c8f.jpeg', 'disponible', 60, '2025-11-27 02:30:21'),
(21, 'Pizza Diavola', 'Pizza de Tomate, mozzarella y salami picante italiano. (Personal)', 149.95, 19, 'uploads/productos/6927b8392f6a4.jpeg', 'disponible', 55, '2025-11-27 02:32:25'),
(22, 'Pizza Napoletana', 'Pizza de Tomate, mozzarella, anchoas, alcaparras y orégano. (Personal)', 169.95, 19, 'uploads/productos/6927b8e0e563a.jpeg', 'disponible', 60, '2025-11-27 02:35:12'),
(23, 'Pollo frito', 'Pollo frito con papas fritas y salsa de tomate', 106.95, 18, 'uploads/productos/69283f67f0b74.jpeg', 'disponible', 45, '2025-11-27 12:09:11'),
(24, 'Pasta pesto', 'Pasta mezclada con una salsa fresca de albahaca, aceite de oliva, piñones y queso parmesano.', 199.95, 17, 'uploads/productos/6928580de820c.jpeg', 'disponible', 25, '2025-11-27 13:54:21'),
(25, 'Pasta Alfredo', 'Pasta bañada en una cremosa salsa de queso parmesano y mantequilla, con un toque suave de ajo.', 184.95, 17, 'uploads/productos/692858a0dc9a8.jpeg', 'disponible', 65, '2025-11-27 13:56:48'),
(26, 'Pasta a la Boloñesa', 'Pasta servida con una salsa tradicional italiana preparada a base de tomate, carne molida y hierbas aromáticas', 284.95, 17, 'uploads/productos/692859dcdbf3f.jpeg', 'disponible', 40, '2025-11-27 14:02:04'),
(27, 'Lasagna Clásica', 'Capas de pasta fresca, carne sazonada, salsa de tomate y una mezcla de quesos gratinados.', 499.95, 17, 'uploads/productos/69285f0eb390b.jpeg', 'disponible', 70, '2025-11-27 14:24:14'),
(28, 'Risotto de Hongos', 'Arroz arborio cocido lentamente en caldo aromático, con hongos salteados y parmesano rallado.', 599.95, 17, 'uploads/productos/69285fb3c4514.jpeg', 'disponible', 65, '2025-11-27 14:26:59'),
(29, 'Philadelphia Roll', 'Salmón fresco, queso crema y pepino, todo perfectamente equilibrado.', 219.95, 21, 'uploads/productos/69286e9b45326.jpeg', 'disponible', 45, '2025-11-27 15:30:35'),
(30, 'Salmon Roll', 'Salmón fresco envuelto en arroz y alga nori, con un toque ligero de wasabi.', 399.95, 21, 'uploads/productos/69286fdeedb02.jpeg', 'disponible', 30, '2025-11-27 15:35:58'),
(31, 'Spicy Tuna Roll', 'Atún fresco picado con salsa picante japonesa, pepino y un toque de mayo.', 549.95, 21, 'uploads/productos/6928702968c87.jpeg', 'disponible', 55, '2025-11-27 15:37:13'),
(32, 'Teriyaki Chicken Roll', 'Pollo teriyaki, pepino y aguacate con un toque de sésamo.', 599.95, 21, 'uploads/productos/69287063a49a4.jpeg', 'disponible', 40, '2025-11-27 15:38:11'),
(33, 'Dragon Roll', 'Camarón tempura, pepino y topping de aguacate con salsa eel.', 694.95, 21, 'uploads/productos/692870c83c6ee.jpeg', 'disponible', 40, '2025-11-27 15:39:52'),
(34, 'Cabernet Sauvignon', 'Vino de cuerpo completo con notas de frutos negros maduros, cacao y un toque de vainilla. Textura sedosa y final largo.', 899.95, 12, 'uploads/productos/692871f0d888e.jpeg', 'disponible', 0, '2025-11-27 15:44:48'),
(35, 'Malbec – Catena Zapata', 'Aromas intensos de ciruela, mora y especias, con taninos suaves y estructura redonda.', 699.95, 12, 'uploads/productos/6928725fdf67b.jpeg', 'disponible', 0, '2025-11-27 15:46:39'),
(36, 'Brunello di Montalcino – Banfi', 'Hecho 100% con uva Sangiovese, ofrece notas de cereza, cuero, café y hierbas mediterráneas.', 1119.95, 12, 'uploads/productos/692872ac1f1a2.jpeg', 'disponible', 0, '2025-11-27 15:47:56'),
(37, 'Camarones al ajillo', 'Camarones al ajillo', 649.95, 15, 'uploads/productos/6928733790cb8.jpeg', 'disponible', 80, '2025-11-27 15:50:15'),
(38, 'Philly cheesesteak', 'Pan de hot dog con queso, carne y vegetales', 199.95, 16, 'uploads/productos/6928740a9b5c5.jpeg', 'disponible', 24, '2025-11-27 15:53:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservaciones`
--

CREATE TABLE `reservaciones` (
  `id_reservacion` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_mesa` int(11) NOT NULL,
  `fecha_reservacion` datetime NOT NULL,
  `numero_personas` int(11) NOT NULL,
  `estado` enum('confirmada','pendiente','cancelada','completada') DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id_reserva` int(11) NOT NULL,
  `id_mesa` int(11) DEFAULT NULL,
  `nombre_cliente` varchar(100) NOT NULL,
  `email_cliente` varchar(100) DEFAULT NULL,
  `telefono_cliente` varchar(20) DEFAULT NULL,
  `cantidad_personas` int(11) NOT NULL,
  `fecha_reserva` datetime NOT NULL,
  `estado` enum('pendiente','confirmada','cancelada','completada') DEFAULT 'pendiente',
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_inventario_bajo`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_inventario_bajo` (
`nombre_ingrediente` varchar(200)
,`cantidad` decimal(10,2)
,`unidad_medida` varchar(20)
,`stock_minimo` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_ordenes_activas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_ordenes_activas` (
`id_orden` int(11)
,`numero_mesa` varchar(10)
,`nombre_cliente` varchar(150)
,`nombre_empleado` varchar(150)
,`estado` enum('pendiente','confirmada','en_preparacion','lista','entregada','cancelada','pagada')
,`total` decimal(10,2)
,`fecha_orden` timestamp
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_inventario_bajo`
--
DROP TABLE IF EXISTS `vista_inventario_bajo`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_inventario_bajo`  AS SELECT `inventario`.`nombre_ingrediente` AS `nombre_ingrediente`, `inventario`.`cantidad` AS `cantidad`, `inventario`.`unidad_medida` AS `unidad_medida`, `inventario`.`stock_minimo` AS `stock_minimo` FROM `inventario` WHERE `inventario`.`cantidad` <= `inventario`.`stock_minimo` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_ordenes_activas`
--
DROP TABLE IF EXISTS `vista_ordenes_activas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_ordenes_activas`  AS SELECT `o`.`id_orden` AS `id_orden`, `m`.`numero_mesa` AS `numero_mesa`, `c`.`nombre` AS `nombre_cliente`, `e`.`nombre` AS `nombre_empleado`, `o`.`estado` AS `estado`, `o`.`total` AS `total`, `o`.`fecha_orden` AS `fecha_orden` FROM (((`ordenes` `o` left join `mesas` `m` on(`o`.`id_mesa` = `m`.`id_mesa`)) left join `clientes` `c` on(`o`.`id_cliente` = `c`.`id_cliente`)) left join `empleados` `e` on(`o`.`id_empleado` = `e`.`id_empleado`)) WHERE `o`.`estado` not in ('pagada','cancelada') ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_orden` (`id_orden`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id_empleado`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id_inventario`);

--
-- Indices de la tabla `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_producto`);

--
-- Indices de la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`id_mesa`),
  ADD UNIQUE KEY `numero_mesa` (`numero_mesa`);

--
-- Indices de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD PRIMARY KEY (`id_orden`),
  ADD KEY `id_mesa` (`id_mesa`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_orden` (`id_orden`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `reservaciones`
--
ALTER TABLE `reservaciones`
  ADD PRIMARY KEY (`id_reservacion`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_mesa` (`id_mesa`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id_reserva`),
  ADD KEY `id_mesa` (`id_mesa`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id_inventario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `menu`
--
ALTER TABLE `menu`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id_mesa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  MODIFY `id_orden` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `reservaciones`
--
ALTER TABLE `reservaciones`
  MODIFY `id_reservacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reserva` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD CONSTRAINT `detalle_orden_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `ordenes` (`id_orden`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_orden_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD CONSTRAINT `ordenes_ibfk_1` FOREIGN KEY (`id_mesa`) REFERENCES `mesas` (`id_mesa`),
  ADD CONSTRAINT `ordenes_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `ordenes_ibfk_3` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `ordenes` (`id_orden`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);

--
-- Filtros para la tabla `reservaciones`
--
ALTER TABLE `reservaciones`
  ADD CONSTRAINT `reservaciones_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `reservaciones_ibfk_2` FOREIGN KEY (`id_mesa`) REFERENCES `mesas` (`id_mesa`);

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_mesa`) REFERENCES `mesas` (`id_mesa`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
