<?php

/**
 * ControleAtualizacoes API
 * 
 * Backend API para o sistema de controle de solicitações.
 * 
 * @author Alex Fabiano Longo
 */

// Define o caminho base
define('BASE_PATH', dirname(__DIR__));

// Carrega configurações
require_once BASE_PATH . '/bootstrap/app.php';

// Autoloader simples
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Roteamento
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Prefixo /api
if (strpos($uri, '/api/') !== 0) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not Found']);
    exit;
}

// Rotas públicas
if ($uri === '/api/health' && $method === 'GET') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit;
}

// Rotas de autenticação
if ($uri === '/api/auth/login' && $method === 'POST') {
    require_once BASE_PATH . '/app/Http/Controllers/AuthController.php';
    $controller = new \App\Http\Controllers\AuthController();
    $controller->login();
    exit;
}

if ($uri === '/api/auth/logout' && $method === 'POST') {
    require_once BASE_PATH . '/app/Http/Controllers/AuthController.php';
    $controller = new \App\Http\Controllers\AuthController();
    $controller->logout();
    exit;
}

if ($uri === '/api/auth/me' && $method === 'GET') {
    require_once BASE_PATH . '/app/Http/Controllers/AuthController.php';
    require_once BASE_PATH . '/app/Http/Middleware/AuthMiddleware.php';
    require_once BASE_PATH . '/app/Models/Token.php';
    require_once BASE_PATH . '/app/Models/User.php';
    
    // Middleware inline para proteção
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Token não fornecido']);
        exit;
    }
    
    $token = $matches[1];
    $user = \App\Models\Token::validate($token);
    
    if (!$user) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Token inválido ou expirado']);
        exit;
    }
    
    $controller = new \App\Http\Controllers\AuthController();
    $_REQUEST['auth_user'] = $user;
    $controller->me();
    exit;
}

// Se nenhuma rota corresponder, retorna 404
http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['error' => 'Not Found']);
