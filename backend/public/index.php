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

/**
 * Configura headers CORS para permitir requisições do frontend de desenvolvimento.
 * 
 * Frontend Vite usa portas 5173+ ( próxima porta disponível).
 * O header Authorization é necessário para Bearer Token.
 */
function setCorsHeaders(): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    // Verifica se é uma origem Vite válida (localhost:5173+)
    if (preg_match('#^http://localhost:517\d+$#', $origin)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
    }

    // Headers permitidos
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 86400'); // 24 hours cache for preflight

    // Para requisições OPTIONS, responder imediatamente
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// Aplicar CORS antes de qualquer outra coisa
setCorsHeaders();

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

// Rotas de solicitações (protegidas)
if (strpos($uri, '/api/solicitacoes') === 0) {
    require_once BASE_PATH . '/app/Http/Controllers/SolicitacaoController.php';
    $controller = new \App\Http\Controllers\SolicitacaoController();
    
    // GET /api/solicitacoes/opcoes - Opções para selects
    if ($uri === '/api/solicitacoes/opcoes' && $method === 'GET') {
        $controller->opcoes();
        exit;
    }
    
    // GET /api/solicitacoes - Listar
    if ($uri === '/api/solicitacoes' && $method === 'GET') {
        $controller->index();
        exit;
    }
    
    // POST /api/solicitacoes - Criar
    if ($uri === '/api/solicitacoes' && $method === 'POST') {
        $controller->store();
        exit;
    }
    
    // GET /api/solicitacoes/{id}/historico - Histórico
    if (preg_match('#^/api/solicitacoes/(\d+)/historico$#', $uri, $matches) && $method === 'GET') {
        $controller->historico((int) $matches[1]);
        exit;
    }
    
    // POST /api/solicitacoes/{id}/status - Alterar status
    if (preg_match('#^/api/solicitacoes/(\d+)/status$#', $uri, $matches) && $method === 'POST') {
        $controller->alterarStatus((int) $matches[1]);
        exit;
    }
    
    // POST /api/solicitacoes/{id}/entrega - Registrar entrega
    if (preg_match('#^/api/solicitacoes/(\d+)/entrega$#', $uri, $matches) && $method === 'POST') {
        $controller->registrarEntrega((int) $matches[1]);
        exit;
    }
    
    // POST /api/solicitacoes/{id}/teste - Registrar teste
    if (preg_match('#^/api/solicitacoes/(\d+)/teste$#', $uri, $matches) && $method === 'POST') {
        $controller->registrarTeste((int) $matches[1]);
        exit;
    }
    
    // POST /api/solicitacoes/{id}/atendimento - Registrar atendimento
    if (preg_match('#^/api/solicitacoes/(\d+)/atendimento$#', $uri, $matches) && $method === 'POST') {
        $controller->registrarAtendimento((int) $matches[1]);
        exit;
    }
    
    // POST /api/solicitacoes/{id}/enviar-suporte - Enviar ao suporte
    if (preg_match('#^/api/solicitacoes/(\d+)/enviar-suporte$#', $uri, $matches) && $method === 'POST') {
        $controller->enviarSuporte((int) $matches[1]);
        exit;
    }
    
    // POST /api/solicitacoes/{id}/reabrir - Reabrir
    if (preg_match('#^/api/solicitacoes/(\d+)/reabrir$#', $uri, $matches) && $method === 'POST') {
        $controller->reabrir((int) $matches[1]);
        exit;
    }
    
    // POST /api/solicitacoes/{id}/cancelar - Cancelar
    if (preg_match('#^/api/solicitacoes/(\d+)/cancelar$#', $uri, $matches) && $method === 'POST') {
        $controller->cancelar((int) $matches[1]);
        exit;
    }
    
    // POST /api/solicitacoes/{id}/encerrar - Encerrar
    if (preg_match('#^/api/solicitacoes/(\d+)/encerrar$#', $uri, $matches) && $method === 'POST') {
        $controller->encerrar((int) $matches[1]);
        exit;
    }
    
    // POST /api/solicitacoes/{id}/observacao - Adicionar observação
    if (preg_match('#^/api/solicitacoes/(\d+)/observacao$#', $uri, $matches) && $method === 'POST') {
        $controller->adicionarObservacao((int) $matches[1]);
        exit;
    }
    
    // POST /api/solicitacoes/{id}/atribuir-responsavel - Atribuir responsável
    if (preg_match('#^/api/solicitacoes/(\d+)/atribuir-responsavel$#', $uri, $matches) && $method === 'POST') {
        $controller->atribuirResponsavel((int) $matches[1]);
        exit;
    }
    
    // GET /api/solicitacoes/{id} - Visualizar
    if (preg_match('#^/api/solicitacoes/(\d+)$#', $uri, $matches) && $method === 'GET') {
        $controller->show((int) $matches[1]);
        exit;
    }
    
    // PUT /api/solicitacoes/{id} - Atualizar
    if (preg_match('#^/api/solicitacoes/(\d+)$#', $uri, $matches) && $method === 'PUT') {
        $controller->update((int) $matches[1]);
        exit;
    }
    
    // DELETE /api/solicitacoes/{id} - Excluir
    if (preg_match('#^/api/solicitacoes/(\d+)$#', $uri, $matches) && $method === 'DELETE') {
        $controller->destroy((int) $matches[1]);
        exit;
    }
}

// Rotas de responsáveis do suporte (protegidas)
if (strpos($uri, '/api/responsaveis-suporte') === 0) {
    require_once BASE_PATH . '/app/Http/Controllers/ResponsavelSuporteController.php';
    $controller = new \App\Http\Controllers\ResponsavelSuporteController();
    
    // GET /api/responsaveis-suporte - Listar todos
    if ($uri === '/api/responsaveis-suporte' && $method === 'GET') {
        $controller->index();
        exit;
    }
    
    // GET /api/responsaveis-suporte/ativos - Listar ativos
    if ($uri === '/api/responsaveis-suporte/ativos' && $method === 'GET') {
        $controller->ativos();
        exit;
    }
    
    // POST /api/responsaveis-suporte - Criar
    if ($uri === '/api/responsaveis-suporte' && $method === 'POST') {
        $controller->store();
        exit;
    }
    
    // GET /api/responsaveis-suporte/{id} - Visualizar
    if (preg_match('#^/api/responsaveis-suporte/(\d+)$#', $uri, $matches) && $method === 'GET') {
        $controller->show((int) $matches[1]);
        exit;
    }
    
    // PUT /api/responsaveis-suporte/{id} - Atualizar
    if (preg_match('#^/api/responsaveis-suporte/(\d+)$#', $uri, $matches) && $method === 'PUT') {
        $controller->update((int) $matches[1]);
        exit;
    }
    
    // POST /api/responsaveis-suporte/{id}/ativar
    if (preg_match('#^/api/responsaveis-suporte/(\d+)/ativar$#', $uri, $matches) && $method === 'POST') {
        $controller->ativar((int) $matches[1]);
        exit;
    }
    
    // POST /api/responsaveis-suporte/{id}/desativar
    if (preg_match('#^/api/responsaveis-suporte/(\d+)/desativar$#', $uri, $matches) && $method === 'POST') {
        $controller->desativar((int) $matches[1]);
        exit;
    }
}

// Se nenhuma rota corresponder, retorna 404
http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['error' => 'Not Found']);
