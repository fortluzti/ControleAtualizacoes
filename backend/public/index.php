<?php

/**
 * ControleAtualizacoes API
 * 
 * Backend Laravel API para o sistema de controle de solicitações.
 * 
 * @author Alex Fabiano Longo
 */

// Define o caminho base
define('BASE_PATH', dirname(__DIR__));

// Carrega configurações
require_once BASE_PATH . '/bootstrap/app.php';

// Roteamento simples
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Rotas da API
if ($uri === '/api/health' && $method === 'GET') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit;
}

// Se nenhuma rota corresponder, retorna 404
http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['error' => 'Not Found']);
