

ALTER TABLE `cont_persona`
ADD COLUMN `ubigeo` CHAR(6) NULL DEFAULT NULL ;

ALTER TABLE `cont_contrato`
ADD COLUMN `solicitante_emisor` INT(10) NULL DEFAULT NULL AFTER `tipo_contrato_id`,
ADD COLUMN `solicitante_receptor` INT(10) NULL DEFAULT NULL AFTER `solicitante_emisor`,
ADD CONSTRAINT `fk_cont_contrato_solicitante_emisor`
    FOREIGN KEY (`solicitante_emisor`) REFERENCES `cont_persona` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `fk_cont_contrato_solicitante_receptor`
    FOREIGN KEY (`solicitante_receptor`) REFERENCES `cont_persona` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;



ALTER TABLE `cont_contrato`

DROP COLUMN `ruc`,
DROP COLUMN `razon_social`,
DROP COLUMN `nombre_comercial`,
DROP COLUMN `vigencia`;
DROP COLUMN `tipo_contrato_proveedor_id`,
DROP COLUMN `categoria_id`,
DROP COLUMN `persona_responsable_id`,
DROP COLUMN `nombre_tienda`,
DROP COLUMN `emisor`,
DROP COLUMN `emisor`,
DROP COLUMN `emisor`,
DROP COLUMN `emisor`,
DROP COLUMN `emisor`,
DROP COLUMN `emisor`,
DROP COLUMN `emisor`,
DROP COLUMN `emisor`,
DROP COLUMN `emisor`,
DROP COLUMN `emisor`,
DROP COLUMN `emisor`,