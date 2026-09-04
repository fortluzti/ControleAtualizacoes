<?php

/**
 * Controlador de Autenticação
 * 
 * Endpoints para login, logout e informações do usuário.
 */

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Token;

class AuthController
{
    /**
     * POST /api/auth/login
     * 
     * Autentica o usuário e retorna o token.
     */
    public function login(): void
    {
        header('Content-Type: application/json');

        // Validar entrada
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['username']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nome de usuário e senha são obrigatórios']);
            return;
        }

        $username = trim($input['username']);
        $password = $input['password'];

        // Buscar usuário
        $user = User::findByUsername($username);

        if (!$user) {
            // Não revelar se o usuário existe
            http_response_code(401);
            echo json_encode(['error' => 'Credenciais inválidas']);
            return;
        }

        // Verificar se está ativo
        if (!$user->ativo) {
            http_response_code(401);
            echo json_encode(['error' => 'Usuário inativo']);
            return;
        }

        // Verificar senha
        if (!$user->verifyPassword($password)) {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciais inválidas']);
            return;
        }

        // Gerar token
        $token = Token::generate($user->id);

        http_response_code(200);
        echo json_encode([
            'message' => 'Login realizado com sucesso',
            'token' => $token->token,
            'user' => $user->toArray(),
        ]);
    }

    /**
     * POST /api/auth/logout
     * 
     * Invalida o token atual.
     */
    public function logout(): void
    {
        header('Content-Type: application/json');

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token não fornecido']);
            return;
        }

        $token = $matches[1];
        Token::invalidate($token);

        echo json_encode(['message' => 'Logout realizado com sucesso']);
    }

    /**
     * GET /api/auth/me
     * 
     * Retorna informações do usuário autenticado.
     */
    public function me(): void
    {
        header('Content-Type: application/json');

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token não fornecido']);
            return;
        }

        $token = $matches[1];
        $user = Token::validate($token);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido ou expirado']);
            return;
        }

        echo json_encode([
            'user' => $user->toArray(),
        ]);
    }
}
