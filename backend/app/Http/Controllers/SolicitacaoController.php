<?php

/**
 * Controlador de Solicitações
 * 
 * Endpoints para gerenciamento de solicitações de alteração.
 */

namespace App\Http\Controllers;

use App\Models\Solicitacao;
use App\Models\Historico;
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

        // Atualizar (passa user ID para tracking de histórico se status mudou)
        $solicitacao = $solicitacao->update($input, $user->id);
        $solicitacao->loadSolicitante();

        echo json_encode([
            'message' => 'Solicitação atualizada com sucesso',
            'data' => $solicitacao->toArray(true),
        ]);
    }

    /**
     * PUT /api/solicitacoes/{id}/status
     * 
     * Altera o status da solicitação com histórico.
     */
    public function alterarStatus(int $id): void
    {
        header('Content-Type: application/json');

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

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input) || empty($input['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Status é obrigatório']);
            return;
        }

        $novoStatus = $input['status'];
        $descricao = $input['descricao'] ?? null;

        if (!in_array($novoStatus, Solicitacao::STATUS)) {
            http_response_code(422);
            echo json_encode(['error' => 'Status inválido']);
            return;
        }

        if (!$solicitacao->alterarStatus($novoStatus, $user->id, $descricao)) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao alterar status']);
            return;
        }

        $solicitacao->loadSolicitante();

        echo json_encode([
            'message' => 'Status alterado com sucesso',
            'data' => $solicitacao->toArray(true),
        ]);
    }

    /**
     * POST /api/solicitacoes/{id}/entrega
     * 
     * Registra uma entrega de atualização.
     */
    public function registrarEntrega(int $id): void
    {
        header('Content-Type: application/json');

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

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input) || empty($input['versao_entregue'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Versão entregue é obrigatória']);
            return;
        }

        $versaoEntregue = trim($input['versao_entregue']);
        $descricao = $input['descricao'] ?? null;
        $responsavelSuporte = isset($input['responsavel_suporte']) ? trim($input['responsavel_suporte']) : null;

        if (strlen($versaoEntregue) > 50) {
            http_response_code(422);
            echo json_encode(['error' => 'Versão entregue deve ter no máximo 50 caracteres']);
            return;
        }

        if (!$solicitacao->registrarEntrega($user->id, $versaoEntregue, $descricao, $responsavelSuporte)) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao registrar entrega']);
            return;
        }

        $solicitacao->loadSolicitante();

        echo json_encode([
            'message' => 'Entrega registrada com sucesso',
            'data' => $solicitacao->toArray(true),
        ]);
    }

    /**
     * POST /api/solicitacoes/{id}/teste
     * 
     * Registra o resultado de um teste.
     */
    public function registrarTeste(int $id): void
    {
        header('Content-Type: application/json');

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

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input) || empty($input['resultado'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Resultado é obrigatório']);
            return;
        }

        $resultado = $input['resultado'];
        $observacao = isset($input['observacao']) ? trim($input['observacao']) : null;
        $versaoTestada = isset($input['versao_testada']) ? trim($input['versao_testada']) : null;

        if (!in_array($resultado, Historico::RESULTADOS_TESTE)) {
            http_response_code(422);
            echo json_encode(['error' => 'Resultado inválido']);
            return;
        }

        if (!$solicitacao->registrarTeste($user->id, $resultado, $observacao, $versaoTestada)) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao registrar teste']);
            return;
        }

        $solicitacao->loadSolicitante();

        echo json_encode([
            'message' => 'Teste registrado com sucesso',
            'data' => $solicitacao->toArray(true),
        ]);
    }

    /**
     * POST /api/solicitacoes/{id}/atendimento
     * 
     * Registra o início do atendimento pelo suporte.
     */
    public function registrarAtendimento(int $id): void
    {
        header('Content-Type: application/json');

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

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input) || empty($input['responsavel_suporte'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Responsável do suporte é obrigatório']);
            return;
        }

        $responsavelSuporte = trim($input['responsavel_suporte']);
        $descricao = isset($input['descricao']) ? trim($input['descricao']) : null;

        if (strlen($responsavelSuporte) > 100) {
            http_response_code(422);
            echo json_encode(['error' => 'Nome do responsável deve ter no máximo 100 caracteres']);
            return;
        }

        if (!$solicitacao->registrarAtendimentoIniciado($user->id, $responsavelSuporte, $descricao)) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao registrar atendimento']);
            return;
        }

        $solicitacao->loadSolicitante();

        echo json_encode([
            'message' => 'Atendimento registrado com sucesso',
            'data' => $solicitacao->toArray(true),
        ]);
    }

    /**
     * POST /api/solicitacoes/{id}/enviar-suporte
     * 
     * Registra o envio da solicitação ao suporte.
     */
    public function enviarSuporte(int $id): void
    {
        header('Content-Type: application/json');

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

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $descricao = isset($input['descricao']) ? trim($input['descricao']) : null;

        if (!$solicitacao->registrarEnviadaSuporte($user->id, $descricao)) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao enviar para suporte']);
            return;
        }

        $solicitacao->loadSolicitante();

        echo json_encode([
            'message' => 'Enviada ao suporte com sucesso',
            'data' => $solicitacao->toArray(true),
        ]);
    }

    /**
     * POST /api/solicitacoes/{id}/reabrir
     * 
     * Registra a reabertura da solicitação.
     */
    public function reabrir(int $id): void
    {
        header('Content-Type: application/json');

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

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $descricao = isset($input['descricao']) ? trim($input['descricao']) : null;

        if (!$solicitacao->registrarReabertura($user->id, $descricao)) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao reabrir solicitação']);
            return;
        }

        $solicitacao->loadSolicitante();

        echo json_encode([
            'message' => 'Solicitação reaberta com sucesso',
            'data' => $solicitacao->toArray(true),
        ]);
    }

    /**
     * POST /api/solicitacoes/{id}/cancelar
     * 
     * Registra o cancelamento da solicitação.
     */
    public function cancelar(int $id): void
    {
        header('Content-Type: application/json');

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

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $descricao = isset($input['descricao']) ? trim($input['descricao']) : null;

        if (!$solicitacao->registrarCancelamento($user->id, $descricao)) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao cancelar solicitação']);
            return;
        }

        $solicitacao->loadSolicitante();

        echo json_encode([
            'message' => 'Solicitação cancelada com sucesso',
            'data' => $solicitacao->toArray(true),
        ]);
    }

    /**
     * POST /api/solicitacoes/{id}/encerrar
     * 
     * Registra o encerramento da solicitação.
     */
    public function encerrar(int $id): void
    {
        header('Content-Type: application/json');

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

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $descricao = isset($input['descricao']) ? trim($input['descricao']) : null;

        if (!$solicitacao->registrarEncerramento($user->id, $descricao)) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao encerrar solicitação']);
            return;
        }

        $solicitacao->loadSolicitante();

        echo json_encode([
            'message' => 'Solicitação encerrada com sucesso',
            'data' => $solicitacao->toArray(true),
        ]);
    }

    /**
     * POST /api/solicitacoes/{id}/observacao
     * 
     * Adiciona uma observação ao histórico.
     */
    public function adicionarObservacao(int $id): void
    {
        header('Content-Type: application/json');

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

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input) || empty($input['observacao'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Observação é obrigatória']);
            return;
        }

        $observacao = trim($input['observacao']);

        if (strlen($observacao) > 5000) {
            http_response_code(422);
            echo json_encode(['error' => 'Observação deve ter no máximo 5000 caracteres']);
            return;
        }

        if (!$solicitacao->adicionarObservacao($user->id, $observacao)) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao adicionar observação']);
            return;
        }

        $solicitacao->loadSolicitante();

        echo json_encode([
            'message' => 'Observação adicionada com sucesso',
            'data' => $solicitacao->toArray(true),
        ]);
    }

    /**
     * GET /api/solicitacoes/{id}/historico
     * 
     * Retorna o histórico da solicitação.
     */
    public function historico(int $id): void
    {
        header('Content-Type: application/json');

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

        $order = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';

        $historicos = Historico::listBySolicitacao($id, $order);

        // Carrega usuários
        foreach ($historicos as $historico) {
            $historico->loadUsuario();
        }

        $data = array_map(fn($h) => $h->toArray(true), $historicos);

        echo json_encode([
            'data' => $data,
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
