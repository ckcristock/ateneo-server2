-- --------------------------------------------------------
-- Host:                         emcosoft.com.co
-- Versión del servidor:         5.7.44 - MySQL Community Server (GPL)
-- SO del servidor:              Linux
-- HeidiSQL Versión:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando datos para la tabla emcosoft_db.benefits_plans: ~15 rows (aproximadamente)
INSERT IGNORE INTO `benefits_plans` (`name`, `description`, `created_at`, `updated_at`) VALUES
	('Plan de beneficios en salud financiado con UPC', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('Presupuesto máximo', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('Prima EPS/EOC no asegurado SOAT', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('Cobertura póliza SOAT', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('Cobertura ARL', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('Cobertura ADRES', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('Cobertura salud pública', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('Cobertura entidad territorial. Recursos de oferta', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('Urgencias población migrante', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('Plan complementario en salud', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('Plan medicina prepagada', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('Otras pólizas en salud', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('Cobertura regimen especial o excepción ', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('Cobertura fondo nacional de salud de las personas privadas de la libertad', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('Particular', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
