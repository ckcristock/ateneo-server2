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

-- Volcando datos para la tabla emcosoft_db.eps: ~21 rows (aproximadamente)
INSERT IGNORE INTO `eps` (`name`, `code`, `nit`, `logo`, `address`, `agreements_id`, `category`, `city`, `country_code`, `creation_date`, `disabled`, `epss_id`, `email`, `encoding_characters`, `interface_id`, `parent_id`, `pbx`, `regional_id`, `send_email`, `settings`, `slogan`, `state`, `telephone`, `type`, `created_at`, `updated_at`, `status`) VALUES
	('MEDIMÁS EPS SAS', 'EPS044', '901097473-5', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('ECOOPSOS EPS SAS', 'ESSC91', '901093846-0', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('NUEVA EPS', 'EPS037', '900156264-2', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('EPS SURA', 'EPS010', '800088702-2', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('ALIANSALUD EPS', 'EPS001', '830113831', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('Salud Total S.A.', 'EPS002', '800130907-4', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('Cafesalud EPS', 'EPS003', '800140949-6', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('E.P.S Sanitas', 'EPS005', '800251440-6', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('Compensar Entidad Promotora de Salud', 'EPS008', '860066942-7', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('Coomeva EPS', 'EPS016', '805000427-1', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('Famisanar', 'EPS017', '830003564-7', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('Servicio Occidental de Salud S.O.S. S.A.', 'EPS018 ', '805001157-2', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('Cruz Blanca S.A', 'EPS023', '830009783-0', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('Saludvida S.A. E.P.S.', 'EPS033 ', '830074184-5', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('Capital Salud EPSS S.A.S.', 'EPSC34', '900298372-9', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('Asociacion Mutual Ser Empresa Solidaria de Salud ESS', 'ESS207', '806008394-7', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('Cooperativa de Salud Comunitaria "Comparta"', 'ESSC33 ', '804002105', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('COOSALUD EPS-S', 'ESSC24', '900226715-3', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Activo'),
	('ASOCIACIÓN INDÍGENA DEL CAUCA - AIC -CM', 'EPSIC3 ', '817001773-3', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, NULL, NULL, 'Activo'),
	('ASOCIACIÓN MUTUAL LA ESPERANZA - ASMET SALUD -CM', 'ESSC62', '900935126-7', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, NULL, NULL, 'Activo'),
	('EPS PIJAOS SALUD', 'EPSIC6', '809008362-2', '', '', 0, '', '', '', '', 0, 0, '', '', 0, 0, '', 0, 0, '', '', '', '', 0, NULL, NULL, 'Activo');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
