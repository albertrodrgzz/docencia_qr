-- =============================================================================
-- Sistema de Certificación Institucional - ISPEB
-- Base de Datos: ispeb_certificaciones
-- Motor: MySQL 8.0+ / MariaDB 10.4+ | Motor de almacenamiento: InnoDB
-- Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `ispeb_certificaciones`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `ispeb_certificaciones`;

-- -----------------------------------------------------------------------------
-- Tabla: usuarios_sistema
-- Descripción: Operadores con acceso al panel administrativo y de emisión.
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `funcionarios_registrados`;
DROP TABLE IF EXISTS `usuarios_sistema`;

CREATE TABLE `usuarios_sistema` (
    `id_usuario` INT UNSIGNED AUTO_INCREMENT,
    `correo` VARCHAR(150) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `rol` ENUM('Administrador', 'Estandar') NOT NULL DEFAULT 'Estandar',
    `estado` ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    `fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `pk_usuarios_sistema` PRIMARY KEY (`id_usuario`),
    CONSTRAINT `uq_usuarios_correo` UNIQUE (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Tabla: funcionarios_registrados
-- Descripción: Data importada de funcionarios sujetos a emisión y verificación QR.
-- -----------------------------------------------------------------------------
CREATE TABLE `funcionarios_registrados` (
    `id_funcionario` INT UNSIGNED AUTO_INCREMENT,
    `cedula` VARCHAR(20) NOT NULL,
    `nombre_completo` VARCHAR(150) NOT NULL,
    `cargo` VARCHAR(150) NOT NULL,
    `departamento` VARCHAR(150) NOT NULL,
    `token_qr` VARCHAR(64) NOT NULL,
    `registrado_por` INT UNSIGNED NOT NULL,
    `fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `pk_funcionarios_registrados` PRIMARY KEY (`id_funcionario`),
    CONSTRAINT `uq_funcionarios_cedula` UNIQUE (`cedula`),
    CONSTRAINT `uq_funcionarios_token_qr` UNIQUE (`token_qr`),
    CONSTRAINT `fk_funcionarios_usuario_registro` 
        FOREIGN KEY (`registrado_por`) 
        REFERENCES `usuarios_sistema` (`id_usuario`)
        ON UPDATE CASCADE 
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices de optimización para búsquedas frecuentes
CREATE INDEX `idx_funcionarios_departamento` ON `funcionarios_registrados` (`departamento`);
CREATE INDEX `idx_funcionarios_fecha_registro` ON `funcionarios_registrados` (`fecha_registro`);

-- -----------------------------------------------------------------------------
-- Inserción Inicial: Administrador del Sistema
-- Clave en texto plano: admin123
-- Hash: Bcrypt ($2y$10$) generado con password_hash('admin123', PASSWORD_BCRYPT)
-- -----------------------------------------------------------------------------
INSERT INTO `usuarios_sistema` (
    `correo`,
    `password_hash`,
    `rol`,
    `estado`,
    `fecha_registro`
) VALUES (
    'admin@ispeb.gob.ve',
    '$2y$10$j.csPahckQJ.JDT9w8YWdeZ/QAViviB4PWcUM.SJTCsEusZfngYly',
    'Administrador',
    'Activo',
    NOW()
);
