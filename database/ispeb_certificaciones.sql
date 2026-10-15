SET FOREIGN_KEY_CHECKS=0;
-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando estructura para tabla ispeb_certificaciones.funcionarios_registrados
CREATE TABLE IF NOT EXISTS `funcionarios_registrados` (
  `id_funcionario` int unsigned NOT NULL AUTO_INCREMENT,
  `cedula` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_completo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cargo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `departamento` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_qr` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `registrado_por` int unsigned NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_funcionario`),
  UNIQUE KEY `uq_funcionarios_cedula` (`cedula`),
  UNIQUE KEY `uq_funcionarios_token_qr` (`token_qr`),
  KEY `fk_funcionarios_usuario_registro` (`registrado_por`),
  KEY `idx_funcionarios_departamento` (`departamento`),
  KEY `idx_funcionarios_fecha_registro` (`fecha_registro`),
  CONSTRAINT `fk_funcionarios_usuario_registro` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios_sistema` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla ispeb_certificaciones.funcionarios_registrados: ~0 rows (aproximadamente)

-- Volcando estructura para tabla ispeb_certificaciones.usuarios_sistema
CREATE TABLE IF NOT EXISTS `usuarios_sistema` (
  `id_usuario` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'estandar',
  `estado` enum('Activo','Inactivo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Activo',
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uq_usuarios_correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla ispeb_certificaciones.usuarios_sistema: ~2 rows (aproximadamente)
INSERT INTO `usuarios_sistema` (`id_usuario`, `nombre`, `correo`, `password_hash`, `rol`, `estado`, `fecha_registro`) VALUES
	(1, 'Albert Rodriguez', 'admin@ispeb.gob.ve', '$2y$10$j.csPahckQJ.JDT9w8YWdeZ/QAViviB4PWcUM.SJTCsEusZfngYly', 'Administrador', 'Activo', '2026-10-13 21:21:28'),
	(2, 'Albert Rodriguez', 'user@ispeb.gob.ve', '$2y$10$oysrhY/REpkWzhlxLiRi/OTFUKYOQ.pDmjmCPNARg4UAzAT1Zfk96', 'estandar', 'Activo', '2026-10-14 22:14:45');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
