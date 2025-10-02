-- Base de datos: `sistema_cobros`
--
-- Estructura de tabla para `usuarios` (para el login)
CREATE TABLE `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre_usuario` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `nombre_completo` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre_usuario` (`nombre_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar un usuario de ejemplo para poder iniciar sesión
-- Contraseña: 'admin' (se guarda encriptada)
INSERT INTO `usuarios` (`nombre_usuario`, `password`, `nombre_completo`) VALUES
('admin', '$2y$10$I/p.K.A.Y.s2mB0g2d2e/u2b.f3gHhJ5.Y.k6L9qO3iR8eX7zW1gC', 'Administrador del Sistema');

-- Estructura de tabla para `clientes`
CREATE TABLE `clientes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(50) DEFAULT NULL,
  `direccion` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Estructura de tabla para `creditos`
CREATE TABLE `creditos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` INT(11) NOT NULL,
  `zona` INT(11) NOT NULL,
  `dia_pago` VARCHAR(20) NOT NULL,
  `monto_total` DECIMAL(10,2) NOT NULL,
  `total_cuotas` INT(11) NOT NULL,
  `cuotas_pagadas` INT(11) NOT NULL DEFAULT 0,
  `monto_cuota` DECIMAL(10,2) NOT NULL,
  `ultimo_pago` DATE DEFAULT NULL,
  `estado` ENUM('Activo','Pagado') NOT NULL DEFAULT 'Activo',
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  CONSTRAINT `creditos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar los datos de ejemplo en las tablas
INSERT INTO `clientes` (`id`, `nombre`) VALUES
(1, 'ACOSTA FRANCISCO'),
(2, 'ALBORNOZ JULIO'),
(3, 'FELAJ MARCELA'),
(4, 'ALBARRACIN FIAMA'),
(5, 'ALARCON TOMAS'),
(6, 'ACOSTA LUJAN'),
(7, 'GOMEZ JUAN'),
(8, 'PEREZ MARIA');

INSERT INTO `creditos` (`cliente_id`, `zona`, `dia_pago`, `monto_total`, `total_cuotas`, `cuotas_pagadas`, `monto_cuota`, `ultimo_pago`, `estado`) VALUES
(1, 2, 'Lunes', 700000.00, 20, 9, 35000.00, '2025-06-18', 'Activo'),
(2, 2, 'Lunes', 1312500.00, 15, 5, 87500.00, '2025-08-04', 'Activo'),
(3, 2, 'Lunes', 1440000.00, 24, 6, 60000.00, '2025-08-18', 'Activo'),
(4, 2, 'Martes', 700000.00, 20, 20, 35000.00, '2025-08-13', 'Pagado'),
(5, 2, 'Martes', 630000.00, 18, 2, 35000.00, '2025-07-12', 'Activo'),
(6, 2, 'Miércoles', 660000.00, 20, 16, 33000.00, '2025-08-13', 'Activo'),
(7, 1, 'Lunes', 300000.00, 12, 3, 25000.00, '2025-08-11', 'Activo'),
(8, 1, 'Viernes', 400000.00, 10, 10, 40000.00, '2025-08-15', 'Pagado');
