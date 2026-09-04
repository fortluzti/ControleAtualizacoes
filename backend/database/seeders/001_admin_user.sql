-- Seeder: Create admin user
-- Date: 2026-09-04
-- Description: Creates the initial admin user
-- 
-- IMPORTANT: Change this password after first login!
-- This is a temporary password for initial setup only.

-- Password: Admin@123456
-- Hash: $2y$12$EHlYxSH0XjiZB2OL50TD3.4uB0PPKkLrkwgmet96G.Yes8XYc.gVq

INSERT INTO `users` (`username`, `nome`, `password`, `perfil`, `ativo`, `created_at`, `updated_at`) 
VALUES ('admin', 'Administrador', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4bUKQKKP6z7Ki.yy', 'ADMIN', 1, NOW(), NOW());
