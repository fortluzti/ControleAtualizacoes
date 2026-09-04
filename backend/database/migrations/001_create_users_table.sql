-- Migration: Create users table
-- Date: 2026-09-04
-- Description: Creates the users table for authentication

CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `nome` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `perfil` ENUM('ADMIN', 'USUARIO') NOT NULL DEFAULT 'USUARIO',
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_username` (`username`),
    INDEX `idx_users_perfil` (`perfil`),
    INDEX `idx_users_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
