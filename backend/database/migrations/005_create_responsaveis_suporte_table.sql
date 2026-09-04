-- Migration: Create responsaveis_suporte table
-- Date: 2026-09-10
-- Description: Creates the responsaveis_suporte table for managing external support contacts

CREATE TABLE IF NOT EXISTS `responsaveis_suporte` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL COMMENT 'Nome do responsável do suporte externo',
    `telefone` VARCHAR(20) NULL COMMENT 'Telefone de contato',
    `email` VARCHAR(100) NULL COMMENT 'Email de contato',
    `empresa` VARCHAR(100) NULL COMMENT 'Empresa/Organização do suporte',
    `observacoes` TEXT NULL COMMENT 'Observações sobre o responsável',
    `ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Se o responsável está ativo',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_responsaveis_nome` (`nome`),
    INDEX `idx_responsaveis_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
