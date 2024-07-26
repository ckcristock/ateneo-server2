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

-- Volcando datos para la tabla emcosoft_db.fiscal_responsibilities: ~39 rows (aproximadamente)
INSERT IGNORE INTO `fiscal_responsibilities` (`code`, `name`, `state`, `created_at`, `updated_at`) VALUES
	('1', 'Aporte especial para la administración de justicia', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('2', 'Gravamen a los movimientos financieros', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('3', 'Impuesto al patrimonio', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('4', 'Impuesto renta y complementario régimen especial', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('5', 'Impuesto renta y complementario régimen ordinario', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('6', 'Ingresos y patrimonio', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('7', 'Retención en la fuente a título de renta', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('8', 'Retención timbre nacional', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('9', 'Retención en la fuente en el impuesto sobre las ve', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('10', 'Obligado aduanero', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('13', 'Gran contribuyente', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('14', 'Informante de exógena', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('15', 'Autorretenedor', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('16', 'Obligación facturar por ingresos bienes y/o servic', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('17', 'Profesionales de compra y venta de divisas', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('18', 'Precios de transferencia', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('19', 'Productor de bienes y/o servicios exentos', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('20', 'Obtención NIT', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('21', 'Declarar ingreso o salida del país de divisas o mo', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('22', 'Obligado a cumplir deberes formales a nombre de te', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('23', 'Agente de retención en ventas', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('24', 'Declaración consolidada precios de transferencia', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('26', 'Declaración individual precios de transferencia', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('32', 'Impuesto nacional a la gasolina y al ACPM', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('33', 'Impuesto nacional al consumo', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('36', 'Establecimiento Permanente', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('37', 'Obligado a Facturar Electrónicamente', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('38', 'Facturación Electrónica Voluntaria', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('39', 'Proveedor de Servicios Tecnológicos PST', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('41', 'Declaración anual de activos en el exterior', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('45', 'Autorretenedor de rendimientos financieros', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('46', 'IVA Prestadores de Servicios desde el Exterior', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('47', 'Régimen Simple de Tributación - SIMPLE', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('48', 'Impuesto sobre las ventas - IVA', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('49', 'No responsable de IVA', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('50', 'No responsable de Consumo restaurantes y bares', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('51', 'Agente retención impoconsumo de bienes inmuebles', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('52', 'Facturador electrónico', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
	('53', 'Persona Jurídica No Responsable de IVA', 'Activo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
