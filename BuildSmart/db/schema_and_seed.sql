-- =========================================
-- USAR BASE DE DATOS EXISTENTE
-- =========================================
-- ⚠️ Cambia el nombre de la base de datos a la tuya real si es diferente.
USE if0_40266323_buildsmart;

-- =========================================
-- TABLA: usuarios
-- =========================================
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  email VARCHAR(120) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  rol ENUM('admin','empleado','cliente') DEFAULT 'empleado',
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- TABLA: clientes
-- =========================================
DROP TABLE IF EXISTS clientes;
CREATE TABLE clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  cedula VARCHAR(50),
  telefono VARCHAR(50),
  correo VARCHAR(120),
  direccion VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- TABLA: empleados
-- =========================================
DROP TABLE IF EXISTS empleados;
CREATE TABLE empleados (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  puesto VARCHAR(100),
  salario DECIMAL(12,2) DEFAULT 0,
  telefono VARCHAR(50),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- TABLA: proyectos
-- =========================================
DROP TABLE IF EXISTS proyectos;
CREATE TABLE proyectos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  nombre VARCHAR(200) NOT NULL,
  ubicacion VARCHAR(255),
  fecha_inicio DATE,
  fecha_fin DATE,
  estado ENUM('planificado','en_ejecucion','finalizado','cancelado') DEFAULT 'planificado',
  descripcion TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  avance DECIMAL(5,2) DEFAULT 0,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- =========================================
-- TABLA: materiales
-- =========================================
DROP TABLE IF EXISTS materiales;
CREATE TABLE materiales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  proyecto_id INT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  cantidad DECIMAL(12,3) DEFAULT 0,
  costo_unitario DECIMAL(12,2) DEFAULT 0,
  proveedor VARCHAR(150),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE
);

-- =========================================
-- TABLA: costos
-- =========================================
DROP TABLE IF EXISTS costos;
CREATE TABLE costos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  proyecto_id INT NOT NULL,
  costo_materiales DECIMAL(12,2) DEFAULT 0,
  costo_mano_obra DECIMAL(12,2) DEFAULT 0,
  otros_gastos DECIMAL(12,2) DEFAULT 0,
  total DECIMAL(12,2) DEFAULT 0,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE
);

-- =========================================
-- TABLA: pagos
-- =========================================
DROP TABLE IF EXISTS pagos;
CREATE TABLE pagos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  proyecto_id INT NOT NULL,
  descripcion VARCHAR(255),
  monto DECIMAL(12,2) NOT NULL,
  tipo ENUM('entrada','salida') DEFAULT 'salida',
  fecha DATE DEFAULT (CURRENT_DATE),
  metodo_pago ENUM('efectivo','transferencia','cheque') DEFAULT 'efectivo',
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE
);

-- =========================================
-- TABLA: proveedores
-- =========================================
DROP TABLE IF EXISTS proveedores;
CREATE TABLE proveedores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  telefono VARCHAR(50),
  correo VARCHAR(120),
  direccion VARCHAR(255)
);

-- =========================================
-- TABLA: tareas
-- =========================================
DROP TABLE IF EXISTS tareas;
CREATE TABLE tareas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  proyecto_id INT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT,
  estado ENUM('pendiente','en_progreso','completada') DEFAULT 'pendiente',
  fecha_inicio DATE,
  fecha_fin DATE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE
);

-- =========================================
-- TABLA: recursos_tareas
-- =========================================
DROP TABLE IF EXISTS recursos_tareas;
CREATE TABLE recursos_tareas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tarea_id INT NOT NULL,
  empleado_id INT,
  material_id INT,
  horas_trabajadas DECIMAL(8,2),
  cantidad_usada DECIMAL(10,2),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tarea_id) REFERENCES tareas(id) ON DELETE CASCADE,
  FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE SET NULL,
  FOREIGN KEY (material_id) REFERENCES materiales(id) ON DELETE SET NULL
);

-- =========================================
-- DATOS DE EJEMPLO
-- =========================================

INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Administrador General', 'admin@buildsmart.com', MD5('Admin123'), 'admin'),
('Carlos Ramirez', 'carlos@buildsmart.com', MD5('carlos123'), 'empleado'),
('Arq. Perez', 'perez@perez.com', MD5('perez123'), 'cliente');

INSERT INTO clientes (nombre, cedula, telefono, correo, direccion) VALUES
('Arq. Perez SRL','001-0101010-1','809-000-1111','contacto@perez.com','Av. Principal #123'),
('Constructora Nova','402-0202020-2','809-222-3333','info@novaconstr.com','Calle Secundaria #45');

INSERT INTO empleados (nombre, puesto, salario, telefono) VALUES
('Carlos Ramirez','Ingeniero Residente',50000,'809-444-5555'),
('María López','Capataz',25000,'809-666-7777');

INSERT INTO proyectos (cliente_id,nombre,ubicacion,fecha_inicio,fecha_fin,estado,descripcion) VALUES
(1,'Edificio Aurora','Santo Domingo','2025-10-01','2026-03-15','en_ejecucion','Edificio residencial 8 pisos'),
(2,'Villa Sol','Santiago','2025-11-10','2026-05-30','planificado','Villa de 3 niveles');

INSERT INTO materiales (proyecto_id,nombre,cantidad,costo_unitario,proveedor) VALUES
(1,'Cemento (funda)',300,6.50,'Cemento Rápido'),
(1,'Varilla 3/8',1000,1.20,'Hierro Norte'),
(2,'Block 20x20',2000,0.50,'Blocks RD');

INSERT INTO costos (proyecto_id,costo_materiales,costo_mano_obra,otros_gastos,total) VALUES
(1,20000,15000,2000,37000),
(2,8000,9000,1000,18000);

INSERT INTO pagos (proyecto_id,descripcion,monto,tipo,metodo_pago) VALUES
(1,'Compra de cemento',1950,'salida','transferencia'),
(1,'Pago de mano de obra',8000,'salida','efectivo'),
(2,'Anticipo del cliente',5000,'entrada','cheque');

INSERT INTO proveedores (nombre,telefono,correo,direccion) VALUES
('Cemento Rápido','809-555-0001','ventas@cementorapido.com','Zona Industrial #12'),
('Hierro Norte','809-555-2222','contacto@hierro.com','Av. Duarte #100'),
('Blocks RD','809-555-3333','info@blocksrd.com','Calle 8, Sto Dgo');

INSERT INTO tareas (proyecto_id,nombre,descripcion,estado,fecha_inicio,fecha_fin) VALUES
(1,'Fundir columnas','Fundición de columnas principales','en_progreso','2025-10-10','2025-10-20'),
(2,'Excavación','Inicio de excavación del terreno','pendiente','2025-11-12','2025-11-25');

INSERT INTO recursos_tareas (tarea_id,empleado_id,material_id,horas_trabajadas,cantidad_usada) VALUES
(1,1,1,8,50),
(1,2,2,5,100),
(2,1,3,3,20);
