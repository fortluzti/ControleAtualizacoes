-- Migration: Create solicitacao_historicos table
-- Date: 2026-09-07
-- Description: Creates the historico table for tracking all movements/events of a solicitacao

CREATE TABLE IF NOT EXISTS `solicitacao_historicos` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `solicitacao_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID da solicitação',
    `usuario_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID do usuário que registrou o evento',
    `responsavel_suporte` VARCHAR(100) NULL COMMENT 'Nome do responsável do suporte externo (quando aplicável)',
    `evento` VARCHAR(50) NOT NULL COMMENT 'Tipo de evento (constante da aplicação)',
    `status_anterior` VARCHAR(30) NULL COMMENT 'Status anterior da solicitação (quando aplicável)',
    `status_novo` VARCHAR(30) NULL COMMENT 'Novo status da solicitação (quando aplicável)',
    `descricao` TEXT NULL COMMENT 'Descrição do evento',
    `observacao` TEXT NULL COMMENT 'Observação adicional (ex: resultado de teste, detalhes da ressalva)',
    `versao_erp` VARCHAR(50) NULL COMMENT 'Versão do ERP relacionada ao evento (texto livre)',
    `resultado_teste` ENUM('FUNCIONOU', 'FUNCIONOU_COM_RESSALVA', 'NAO_FUNCIONOU') NULL COMMENT 'Resultado do teste (quando aplicável)',
    `data_hora_evento` DATETIME NOT NULL COMMENT 'Data/hora em que o evento ocorreu',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_historico_solicitacao` FOREIGN KEY (`solicitacao_id`) REFERENCES `solicitacoes`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_historico_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_historico_solicitacao` (`solicitacao_id`),
    INDEX `idx_historico_evento` (`evento`),
    INDEX `idx_historico_data_hora` (`data_hora_evento`),
    INDEX `idx_historico_usuario` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
