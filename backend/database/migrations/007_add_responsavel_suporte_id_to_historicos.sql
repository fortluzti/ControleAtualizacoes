-- Migration: Add responsavel_suporte_id to solicitacao_historicos
-- Date: 2026-09-10
-- Description: Adds foreign key reference to historico events for better tracking

ALTER TABLE `solicitacao_historicos` 
ADD COLUMN `responsavel_suporte_id` BIGINT UNSIGNED NULL COMMENT 'ID do responsável do suporte (quando aplicável)' AFTER `usuario_id`;
