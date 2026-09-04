<?php

/**
 * Controlador de Solicitações
 * 
 * Endpoints para gerenciamento de solicitações de alteração.
 */

namespace App\Http\Controllers;

use App\Models\Solicitacao;
use App\Models\Token;
use App\Models\User;

class SolicitacaoController
{
    /**
     * GET /api/solicitacoes
     * 
     * Lista solicitações com filtros opcionais.
     */
    public function index(): void
    {
        header('Content-Type: application/json');

        // Autenticação
        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        // Parse filtros
        $filters = [
            'numero' => $_GET['numero'] ?? '',
            'titulo' => $_GET['titulo'] ?? '',
            'status' => $_GET['status'] ?? '',
            'prioridade' => $_GET['prioridade'] ?? '',
        ];

        // Remove filtros vazios
        $filters = array_filter($filters, fn($v) => !empty($v));

        // Paginação
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? min(100, max(1, (int) $_GET['per_page'])) : 20;

        $result = Solicitacao::list($filters, $page, $perPage);

        // Carregar solicitantes
        foreach ($result['data'] as $solicitacao) {
            $solicitacao->loadSolicitante();
        }

        $data = array_map(fn($s) => $s->toArray(true), $result['data']);

        echo json_encode([
            'data' => $data,
            'pagination' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'per_page' => $result['per_page'],
                'total_pages' => $result['total_pages'],
            ],
        ]);
    }

    /**
     * GET /api/solicitacoes/{id}
     * 
     * Retorna uma solicitação específica.
     */
    public function show(int $id): void
    {
        header('Content-Type: application/json');

        // Autenticação
        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        $solicitacao = Solicitacao::find($id);

        if (!$solicitacao) {
            http_response_code(404);
            echo json_encode(['error' => 'Solicitação não encontrada']);
            return;
        }

        $solicitacao->loadSolicitante();

        echo json_encode([
            'data' => $solicitacao->toArray(true),
        ]);
    }

    /**
     * POST /api/solicitacoes
     * 
     * Cria uma nova solicitação.
     */
    public function store(): void
    {
        header('Content-Type: application/json');

        // Autenticação
        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        // Parse input
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input)) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos']);
            return;
        }

        // Validar
        $errors = Solicitacao::validate($input);

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            return;
        }

        // Criar - solicitante é o usuário autenticado
        $solicitacao = Solicitacao::create($input, $user->id);

        $solicitacao->loadSolicitante();

        http_response_code(201);
        echo json_encode([
            'message' => 'Solicitação criada com sucesso',
            'data' => $solicitacao->toArray(true),
        ]);
    }

    /**
     * PUT /api/solicitacoes/{id}
     * 
     * Atualiza uma solicitação existente.
     */
    public function update(int $id): void
    {
        header('Content-Type: application/json');

        // Autenticação
        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        $solicitacao = Solicitacao::find($id);

        if (!$solicitacao) {
            http_response_code(404);
            echo json_encode(['error' => 'Solicitação não encontrada']);
            return;
        }

        // Parse input
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input)) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos']);
            return;
        }

        // Validar (isUpdate = true)
        $errors = Solicitacao::validate($input, true);

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            return;
        }

        // Atualizar
        $solicitacao = $solicitacao->update($input);
        $solicitacao->loadSolicitante();

        echo json_encode([
            'message' => 'Solicitação atualizada com sucesso',
            'data' => $solicitacao->toArray(true),
        ]);
    }

    /**
     * DELETE /api/solicitacoes/{id}
     * 
     * Soft delete de uma solicitação.
     */
    public function destroy(int $id): void
    {
        header('Content-Type: application/json');

        // Autenticação
        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        $solicitacao = Solicitacao::find($id);

        if (!$solicitacao) {
            http_response_code(404);
            echo json_encode(['error' => 'Solicitação não encontrada']);
            return;
        }

        // Soft delete
        $solicitacao->delete();

        echo json_encode([
            'message' => 'Solicitação excluída com sucesso',
        ]);
    }

    /**
     * GET /api/solicitacoes/opcoes
     * 
     * Retorna as opções disponíveis para tipos, prioridades e status.
     */
    public function opcoes(): void
    {
        header('Content-Type: application/json');

        // Autenticação
        $user = $this->authenticate();
        if (!$user) {
            return;
        }

        echo json_encode([
            'tipos' => Solicitacao::TIPOS_LABELS,
            'prioridades' => Solicitacao::PRIORIDADES_LABELS,
            'status' => Solicitacao::STATUS_LABELS,
        ]);
    }

    /**
     * Autentica o usuário via Bearer token.
     * Retorna o usuário ou responde com erro 401.
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
