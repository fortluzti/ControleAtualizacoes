<?php

/**
 * Script de Setup do Banco de Dados
 * 
 * Executa as migrations e seeders do sistema.
 * 
 * Uso: php setup-database.php
 */

define('BASE_PATH', __DIR__);

// Carrega configurações
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

$config = require BASE_PATH . '/config/database.php';

echo "=== Setup do Banco de Dados ===\n\n";

try {
    // Conectar ao MySQL sem especificar banco para criar o banco se necessário
    $dsn = sprintf(
        'mysql:host=%s;port=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['charset']
    );

    $pdo = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "✓ Conectado ao MySQL\n";

    // Criar banco se não existir
    $dbName = $config['database'];
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Banco de dados '{$dbName}' verificado/criado\n";

    // Conectar ao banco específico
    $pdo->exec("USE `{$dbName}`");

    // Executar migrations
    echo "\n--- Executando Migrations ---\n";
    
    $migrations = glob(BASE_PATH . '/database/migrations/*.sql');
    sort($migrations);

    foreach ($migrations as $migration) {
        $filename = basename($migration);
        echo "Executando: {$filename}... ";
        
        $sql = file_get_contents($migration);
        $pdo->exec($sql);
        
        echo "OK\n";
    }

    // Executar seeders
    echo "\n--- Executando Seeders ---\n";
    
    $seeders = glob(BASE_PATH . '/database/seeders/*.sql');
    sort($seeders);

    foreach ($seeders as $seeder) {
        $filename = basename($seeder);
        echo "Executando: {$filename}... ";
        
        $sql = file_get_contents($seeder);
        $pdo->exec($sql);
        
        echo "OK\n";
    }

    echo "\n=== Setup Concluído ===\n";
    echo "\nUsuário admin criado:\n";
    echo "  Username: admin\n";
    echo "  Senha: Admin@123456\n";
    echo "\n⚠️  IMPORTANTE: Altere a senha após o primeiro login!\n";

} catch (PDOException $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
