-- Migration: Create solicitacoes table
-- Date: 2026-09-04
-- Description: Creates the solicitacoes table for managing change requests

CREATE TABLE IF NOT EXISTS `solicitacoes` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `numero` VARCHAR(20) NOT NULL UNIQUE COMMENT 'Número único da solicitação (ex: SOL-000001)',
    `titulo` VARCHAR(255) NOT NULL,
    `descricao` TEXT NOT NULL,
    `tipo` ENUM('CORRECAO', 'ALTERACAO', 'MELHORIA', 'DUVIDA') NOT NULL DEFAULT 'CORRECAO',
    `prioridade` ENUM('BAIXA', 'NORMAL', 'ALTA', 'URGENTE') NOT NULL DEFAULT 'NORMAL',
    `status` ENUM('ABERTA', 'ENVIADA_AO_SUPORTE', 'EM_ANALISE', 'EM_DESENVOLVIMENTO', 'AGUARDANDO_RETORNO', 'ENTREGUE', 'EM_TESTE', 'FUNCIONOU', 'FUNCIONOU_COM_RESSALVA', 'NAO_FUNCIONOU', 'REABERTA', 'CANCELADA', 'ENCERRADA') NOT NULL DEFAULT 'ABERTA',
    `solicitante_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID do usuário que criou a solicitação',
    `versao_erp` VARCHAR(50) NOT NULL COMMENT 'Versão do ERP informada pelo solicitante (texto livre)',
    `observacoes` TEXT NULL COMMENT 'Observações adicionais',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL COMMENT 'Soft delete',
    CONSTRAINT `fk_solicitacoes_solicitante` FOREIGN KEY (`solicitante_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_solicitacoes_numero` (`numero`),
    INDEX `idx_solicitacoes_tipo` (`tipo`),
    INDEX `idx_solicitacoes_prioridade` (`prioridade`),
    INDEX `idx_solicitacoes_status` (`status`),
    INDEX `idx_solicitacoes_solicitante` (`solicitante_id`),
    INDEX `idx_solicitacoes_created_at` (`created_at`),
    INDEX `idx_solicitacoes_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
