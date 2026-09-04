-- Migration: Add responsavel_suporte_id to solicitacoes
-- Date: 2026-09-10
-- Description: Adds foreign key to track the current responsible for a solicitation

ALTER TABLE `solicitacoes` 
ADD COLUMN `responsavel_suporte_id` BIGINT UNSIGNED NULL COMMENT 'ID do responsável atual pelo suporte' AFTER `solicitante_id`,
ADD CONSTRAINT `fk_solicitacoes_responsavel_suporte` FOREIGN KEY (`responsavel_suporte_id`) REFERENCES `responsaveis_suporte`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
