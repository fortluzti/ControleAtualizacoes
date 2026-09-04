<?php

/**
 * Middleware de Autenticação
 * 
 * Protege rotas que requerem autenticação.
 */

namespace App\Http\Middleware;

use App\Models\Token;

function requireAuth(): void
{
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Token não fornecido']);
        exit;
    }

    $token = $matches[1];
    $user = Token::validate($token);

    if (!$user) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Token inválido ou expirado']);
        exit;
    }

    // Adiciona o usuário à requisição
    $_REQUEST['auth_user'] = $user;
}

function getAuthUser(): ?\App\Models\User
{
    return $_REQUEST['auth_user'] ?? null;
}
