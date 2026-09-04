<?php

/**
 * Modelo de Histórico de Solicitação
 * 
 * Registra todas as movimentações/eventos de uma solicitação.
 */

namespace App\Models;

use App\Database;

class Historico
{
    public int $id;
    public int $solicitacao_id;
    public int $usuario_id;
    public ?int $responsavel_suporte_id;
    public ?string $responsavel_suporte;
    public string $evento;
    public ?string $status_anterior;
    public ?string $status_novo;
    public ?string $descricao;
    public ?string $observacao;
    public ?string $versao_erp;
    public ?string $resultado_teste;
    public string $data_hora_evento;
    public string $created_at;
    public string $updated_at;

    // Relacionamentos
    public ?User $usuario = null;
    public ?Solicitacao $solicitacao = null;
    public ?ResponsavelSuporte $responsavel_suporte = null;

    // Tipos de evento
    public const EVENTO_SOLICITACAO_CRIADA = 'SOLICITACAO_CRIADA';
    public const EVENTO_STATUS_ALTERADO = 'STATUS_ALTERADO';
    public const EVENTO_SOLICITACAO_EDITADA = 'SOLICITACAO_EDITADA';
    public const EVENTO_ENVIADA_SUPORTE = 'ENVIADA_SUPORTE';
    public const EVENTO_ATENDIMENTO_INICIADO = 'ATENDIMENTO_INICIADO';
    public const EVENTO_ATUALIZACAO_ENTREGUE = 'ATUALIZACAO_ENTREGUE';
    public const EVENTO_TESTE_REALIZADO = 'TESTE_REALIZADO';
    public const EVENTO_OBSERVACAO_ADICIONADA = 'OBSERVACAO_ADICIONADA';
    public const EVENTO_SOLICITACAO_REABERTA = 'SOLICITACAO_REABERTA';
    public const EVENTO_SOLICITACAO_CANCELADA = 'SOLICITACAO_CANCELADA';
    public const EVENTO_SOLICITACAO_ENCERRADA = 'SOLICITACAO_ENCERRADA';
    public const EVENTO_RESPONSAVEL_ATRIBUIDO = 'RESPONSAVEL_ATRIBUIDO';

    // Lista de todos os eventos
    public const EVENTOS = [
        self::EVENTO_SOLICITACAO_CRIADA,
        self::EVENTO_STATUS_ALTERADO,
        self::EVENTO_SOLICITACAO_EDITADA,
        self::EVENTO_ENVIADA_SUPORTE,
        self::EVENTO_ATENDIMENTO_INICIADO,
        self::EVENTO_ATUALIZACAO_ENTREGUE,
        self::EVENTO_TESTE_REALIZADO,
        self::EVENTO_OBSERVACAO_ADICIONADA,
        self::EVENTO_SOLICITACAO_REABERTA,
        self::EVENTO_SOLICITACAO_CANCELADA,
        self::EVENTO_SOLICITACAO_ENCERRADA,
        self::EVENTO_RESPONSAVEL_ATRIBUIDO,
    ];

    // Labels legíveis para eventos
    public const EVENTOS_LABELS = [
        self::EVENTO_SOLICITACAO_CRIADA => 'Solicitação criada',
        self::EVENTO_STATUS_ALTERADO => 'Status alterado',
        self::EVENTO_SOLICITACAO_EDITADA => 'Solicitação editada',
        self::EVENTO_ENVIADA_SUPORTE => 'Enviada ao suporte',
        self::EVENTO_ATENDIMENTO_INICIADO => 'Atendimento iniciado',
        self::EVENTO_ATUALIZACAO_ENTREGUE => 'Atualização entregue',
        self::EVENTO_TESTE_REALIZADO => 'Teste realizado',
        self::EVENTO_OBSERVACAO_ADICIONADA => 'Observação adicionada',
        self::EVENTO_SOLICITACAO_REABERTA => 'Solicitação reaberta',
        self::EVENTO_SOLICITACAO_CANCELADA => 'Solicitação cancelada',
        self::EVENTO_SOLICITACAO_ENCERRADA => 'Solicitação encerrada',
        self::EVENTO_RESPONSAVEL_ATRIBUIDO => 'Responsável atribuído',
    ];

    // Resultados de teste válidos
    public const RESULTADOS_TESTE = ['FUNCIONOU', 'FUNCIONOU_COM_RESSALVA', 'NAO_FUNCIONOU'];

    // Labels para resultados de teste
    public const RESULTADOS_TESTE_LABELS = [
        'FUNCIONOU' => 'Funcionou',
        'FUNCIONOU_COM_RESSALVA' => 'Funcionou com ressalva',
        'NAO_FUNCIONOU' => 'Não funcionou',
    ];

    /**
     * Busca um histórico pelo ID.
     */
    public static function find(int $id): ?self
    {
        $stmt = Database::query(
            'SELECT * FROM solicitacao_historicos WHERE id = :id',
            ['id' => $id]
        );

        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return self::fromArray($data);
    }

    /**
     * Lista histórico de uma solicitação.
     */
    public static function listBySolicitacao(int $solicitacaoId, string $order = 'DESC'): array
    {
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $stmt = Database::query(
            "SELECT * FROM solicitacao_historicos WHERE solicitacao_id = :solicitacao_id ORDER BY data_hora_evento $order",
            ['solicitacao_id' => $solicitacaoId]
        );

        $historicos = [];
        while ($data = $stmt->fetch()) {
            $historicos[] = self::fromArray($data);
        }

        return $historicos;
    }

    /**
     * Cria um novo registro de histórico.
     */
    public static function create(array $data): self
    {
        $stmt = Database::query(
            'INSERT INTO solicitacao_historicos
             (solicitacao_id, usuario_id, responsavel_suporte_id, responsavel_suporte, evento, status_anterior, status_novo, descricao, observacao, versao_erp, resultado_teste, data_hora_evento, created_at, updated_at)
             VALUES (:solicitacao_id, :usuario_id, :responsavel_suporte_id, :responsavel_suporte, :evento, :status_anterior, :status_novo, :descricao, :observacao, :versao_erp, :resultado_teste, :data_hora_evento, NOW(), NOW())',
            [
                'solicitacao_id' => $data['solicitacao_id'],
                'usuario_id' => $data['usuario_id'],
                'responsavel_suporte_id' => $data['responsavel_suporte_id'] ?? null,
                'responsavel_suporte' => $data['responsavel_suporte'] ?? null,
                'evento' => $data['evento'],
                'status_anterior' => $data['status_anterior'] ?? null,
                'status_novo' => $data['status_novo'] ?? null,
                'descricao' => $data['descricao'] ?? null,
                'observacao' => $data['observacao'] ?? null,
                'versao_erp' => $data['versao_erp'] ?? null,
                'resultado_teste' => $data['resultado_teste'] ?? null,
                'data_hora_evento' => $data['data_hora_evento'] ?? date('Y-m-d H:i:s'),
            ]
        );

        return self::find((int) Database::lastInsertId());
    }

    /**
     * Carrega o usuário que criou o registro.
     */
    public function loadUsuario(): void
    {
        $this->usuario = User::find($this->usuario_id);
    }

    /**
     * Carrega a solicitação relacionada.
     */
    public function loadSolicitacao(): void
    {
        $this->solicitacao = Solicitacao::find($this->solicitacao_id);
    }

    /**
     * Carrega o responsável do suporte relacionado.
     */
    public function loadResponsavelSuporte(): void
    {
        if ($this->responsavel_suporte_id) {
            $this->responsavel_suporte = ResponsavelSuporte::find($this->responsavel_suporte_id);
        }
    }

    /**
     * Retorna dados do histórico em array.
     */
    public function toArray(bool $includeRelations = false): array
    {
        $data = [
            'id' => $this->id,
            'solicitacao_id' => $this->solicitacao_id,
            'usuario_id' => $this->usuario_id,
            'responsavel_suporte_id' => $this->responsavel_suporte_id,
            'responsavel_suporte' => $this->responsavel_suporte,
            'evento' => $this->evento,
            'evento_label' => self::EVENTOS_LABELS[$this->evento] ?? $this->evento,
            'status_anterior' => $this->status_anterior,
            'status_anterior_label' => Solicitacao::STATUS_LABELS[$this->status_anterior] ?? $this->status_anterior,
            'status_novo' => $this->status_novo,
            'status_novo_label' => Solicitacao::STATUS_LABELS[$this->status_novo] ?? $this->status_novo,
            'descricao' => $this->descricao,
            'observacao' => $this->observacao,
            'versao_erp' => $this->versao_erp,
            'resultado_teste' => $this->resultado_teste,
            'resultado_teste_label' => self::RESULTADOS_TESTE_LABELS[$this->resultado_teste] ?? $this->resultado_teste,
            'data_hora_evento' => $this->data_hora_evento,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($includeRelations) {
            if ($this->usuario) {
                $data['usuario'] = $this->usuario->toArray();
            }
            if ($this->solicitacao) {
                $data['solicitacao'] = $this->solicitacao->toArray();
            }
            if ($this->responsavel_suporte) {
                $data['responsavel_suporte'] = $this->responsavel_suporte->toArray();
            }
        }

        return $data;
    }

    /**
     * Cria instância a partir de array.
     */
    private static function fromArray(array $data): self
    {
        $historico = new self();
        $historico->id = (int) $data['id'];
        $historico->solicitacao_id = (int) $data['solicitacao_id'];
        $historico->usuario_id = (int) $data['usuario_id'];
        $historico->responsavel_suporte_id = isset($data['responsavel_suporte_id']) ? (int) $data['responsavel_suporte_id'] : null;
        $historico->responsavel_suporte = $data['responsavel_suporte'] ?? null;
        $historico->evento = $data['evento'];
        $historico->status_anterior = $data['status_anterior'];
        $historico->status_novo = $data['status_novo'];
        $historico->descricao = $data['descricao'];
        $historico->observacao = $data['observacao'];
        $historico->versao_erp = $data['versao_erp'];
        $historico->resultado_teste = $data['resultado_teste'];
        $historico->data_hora_evento = $data['data_hora_evento'];
        $historico->created_at = $data['created_at'];
        $historico->updated_at = $data['updated_at'];
        return $historico;
    }

    /**
     * Valida os dados do histórico.
     */
    public static function validate(array $data): array
    {
        $errors = [];

        if (empty($data['solicitacao_id'])) {
            $errors[] = 'Solicitação é obrigatória';
        }

        if (empty($data['usuario_id'])) {
            $errors[] = 'Usuário é obrigatório';
        }

        if (empty($data['evento'])) {
            $errors[] = 'Evento é obrigatório';
        } elseif (!in_array($data['evento'], self::EVENTOS)) {
            $errors[] = 'Evento inválido';
        }

        if (!empty($data['resultado_teste']) && !in_array($data['resultado_teste'], self::RESULTADOS_TESTE)) {
            $errors[] = 'Resultado de teste inválido';
        }

        return $errors;
    }
}
