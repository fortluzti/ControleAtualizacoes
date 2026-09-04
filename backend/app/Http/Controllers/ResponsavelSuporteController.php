<?php

/**
 * Controlador de Responsáveis do Suporte
 * 
 * Endpoints para gerenciamento de responsáveis externos do suporte.
 */

namespace App\Http\Controllers;

use App\Models\ResponsavelSuporte;
use App\Models\Token;
use App\Models\User;

class ResponsavelSuporteController
{
    /**
     * GET /api/responsaveis-suporte
     * 
     * Lista todos os responsáveis do suporte.
     */
    public function index(): void
    {
        header('Content-Type: application/json');

        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        $responsaveis = ResponsavelSuporte::listAll();

        $data = array_map(fn($r) => $r->toArray(), $responsaveis);

        echo json_encode([
            'data' => $data,
        ]);
    }

    /**
     * GET /api/responsaveis-suporte/ativos
     * 
     * Lista responsáveis ativos.
     */
    public function ativos(): void
    {
        header('Content-Type: application/json');

        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        $responsaveis = ResponsavelSuporte::listAtivos();

        $data = array_map(fn($r) => $r->toArray(), $responsaveis);

        echo json_encode([
            'data' => $data,
        ]);
    }

    /**
     * GET /api/responsaveis-suporte/{id}
     * 
     * Retorna um responsável específico.
     */
    public function show(int $id): void
    {
        header('Content-Type: application/json');

        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        $responsavel = ResponsavelSuporte::find($id);

        if (!$responsavel) {
            http_response_code(404);
            echo json_encode(['error' => 'Responsável não encontrado']);
            return;
        }

        echo json_encode([
            'data' => $responsavel->toArray(),
        ]);
    }

    /**
     * POST /api/responsaveis-suporte
     * 
     * Cria um novo responsável.
     */
    public function store(): void
    {
        header('Content-Type: application/json');

        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input)) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos']);
            return;
        }

        $errors = ResponsavelSuporte::validate($input);

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            return;
        }

        $responsavel = ResponsavelSuporte::create($input);

        http_response_code(201);
        echo json_encode([
            'message' => 'Responsável criado com sucesso',
            'data' => $responsavel->toArray(),
        ]);
    }

    /**
     * PUT /api/responsaveis-suporte/{id}
     * 
     * Atualiza um responsável existente.
     */
    public function update(int $id): void
    {
        header('Content-Type: application/json');

        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        $responsavel = ResponsavelSuporte::find($id);

        if (!$responsavel) {
            http_response_code(404);
            echo json_encode(['error' => 'Responsável não encontrado']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input)) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos']);
            return;
        }

        $errors = ResponsavelSuporte::validate($input, true);

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            return;
        }

        $responsavel = $responsavel->update($input);

        echo json_encode([
            'message' => 'Responsável atualizado com sucesso',
            'data' => $responsavel->toArray(),
        ]);
    }

    /**
     * POST /api/responsaveis-suporte/{id}/ativar
     * 
     * Ativa um responsável.
     */
    public function ativar(int $id): void
    {
        header('Content-Type: application/json');

        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        $responsavel = ResponsavelSuporte::find($id);

        if (!$responsavel) {
            http_response_code(404);
            echo json_encode(['error' => 'Responsável não encontrado']);
            return;
        }

        $responsavel->activate();

        echo json_encode([
            'message' => 'Responsável ativado com sucesso',
            'data' => $responsavel->toArray(),
        ]);
    }

    /**
     * POST /api/responsaveis-suporte/{id}/desativar
     * 
     * Desativa um responsável.
     */
    public function desativar(int $id): void
    {
        header('Content-Type: application/json');

        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        $responsavel = ResponsavelSuporte::find($id);

        if (!$responsavel) {
            http_response_code(404);
            echo json_encode(['error' => 'Responsável não encontrado']);
            return;
        }

        $responsavel->deactivate();

        echo json_encode([
            'message' => 'Responsável desativado com sucesso',
            'data' => $responsavel->toArray(),
        ]);
    }

    /**
     * Autentica o usuário via Bearer token.
     */
    private function authenticate(): ?User
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token não fornecido']);
            return null;
        }

        $token = $matches[1];
        $user = Token::validate($token);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido ou expirado']);
            return null;
        }

        return $user;
    }
}
