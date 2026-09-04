<?php

/**
 * Modelo de Solicitação
 * 
 * Representa uma solicitação de alteração, correção ou melhoria do ERP.
 */

namespace App\Models;

use App\Database;

class Solicitacao
{
    public int $id;
    public string $numero;
    public string $titulo;
    public string $descricao;
    public string $tipo;
    public string $prioridade;
    public string $status;
    public int $solicitante_id;
    public string $versao_erp;
    public ?string $observacoes;
    public string $created_at;
    public string $updated_at;
    public ?string $deleted_at;

    // Relacionamento com solicitante (carregado manualmente)
    public ?User $solicitante = null;

    // Tipos válidos
    public const TIPOS = ['CORRECAO', 'ALTERACAO', 'MELHORIA', 'DUVIDA'];

    // Prioridades válidas
    public const PRIORIDADES = ['BAIXA', 'NORMAL', 'ALTA', 'URGENTE'];

    // Status válidos
    public const STATUS = [
        'ABERTA',
        'ENVIADA_AO_SUPORTE',
        'EM_ANALISE',
        'EM_DESENVOLVIMENTO',
        'AGUARDANDO_RETORNO',
        'ENTREGUE',
        'EM_TESTE',
        'FUNCIONOU',
        'FUNCIONOU_COM_RESSALVA',
        'NAO_FUNCIONOU',
        'REABERTA',
        'CANCELADA',
        'ENCERRADA',
    ];

    // Labels legíveis para tipos
    public const TIPOS_LABELS = [
        'CORRECAO' => 'Correção',
        'ALTERACAO' => 'Alteração',
        'MELHORIA' => 'Melhoria',
        'DUVIDA' => 'Dúvida',
    ];

    // Labels legíveis para prioridades
    public const PRIORIDADES_LABELS = [
        'BAIXA' => 'Baixa',
        'NORMAL' => 'Normal',
        'ALTA' => 'Alta',
        'URGENTE' => 'Urgente',
    ];

    // Labels legíveis para status
    public const STATUS_LABELS = [
        'ABERTA' => 'Aberta',
        'ENVIADA_AO_SUPORTE' => 'Enviada ao suporte',
        'EM_ANALISE' => 'Em análise',
        'EM_DESENVOLVIMENTO' => 'Em desenvolvimento',
        'AGUARDANDO_RETORNO' => 'Aguardando retorno',
        'ENTREGUE' => 'Entregue',
        'EM_TESTE' => 'Em teste',
        'FUNCIONOU' => 'Funcionou',
        'FUNCIONOU_COM_RESSALVA' => 'Funcionou com ressalva',
        'NAO_FUNCIONOU' => 'Não funcionou',
        'REABERTA' => 'Reaberta',
        'CANCELADA' => 'Cancelada',
        'ENCERRADA' => 'Encerrada',
    ];

    /**
     * Gera o próximo número de solicitação.
     */
    public static function generateNumero(): string
    {
        $stmt = Database::query(
            'SELECT MAX(CAST(SUBSTRING(numero, 5) AS UNSIGNED)) as max_num FROM solicitacoes WHERE numero LIKE "SOL-%"'
        );
        $result = $stmt->fetch();
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return 'SOL-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Busca uma solicitação pelo ID.
     */
    public static function find(int $id): ?self
    {
        $stmt = Database::query(
            'SELECT * FROM solicitacoes WHERE id = :id AND deleted_at IS NULL',
            ['id' => $id]
        );

        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return self::fromArray($data);
    }

    /**
     * Busca uma solicitação pelo número.
     */
    public static function findByNumero(string $numero): ?self
    {
        $stmt = Database::query(
            'SELECT * FROM solicitacoes WHERE numero = :numero AND deleted_at IS NULL',
            ['numero' => $numero]
        );

        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return self::fromArray($data);
    }

    /**
     * Lista solicitações com filtros opcionais.
     */
    public static function list(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['deleted_at IS NULL'];
        $params = [];

        // Filtro por número (busca)
        if (!empty($filters['numero'])) {
            $where[] = 'numero LIKE :numero';
            $params['numero'] = '%' . $filters['numero'] . '%';
        }

        // Filtro por título (busca)
        if (!empty($filters['titulo'])) {
            $where[] = 'titulo LIKE :titulo';
            $params['titulo'] = '%' . $filters['titulo'] . '%';
        }

        // Filtro por status
        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params['status'] = $filters['status'];
        }

        // Filtro por prioridade
        if (!empty($filters['prioridade'])) {
            $where[] = 'prioridade = :prioridade';
            $params['prioridade'] = $filters['prioridade'];
        }

        $whereClause = implode(' AND ', $where);

        // Contar total
        $countStmt = Database::query("SELECT COUNT(*) as total FROM solicitacoes WHERE $whereClause", $params);
        $total = (int) $countStmt->fetch()['total'];

        // Calcular offset
        $offset = ($page - 1) * $perPage;

        // Buscar registros
        $sql = "SELECT * FROM solicitacoes WHERE $whereClause ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = Database::query($sql, array_merge($params, ['limit' => $perPage, 'offset' => $offset]));

        $solicitacoes = [];
        while ($data = $stmt->fetch()) {
            $solicitacoes[] = self::fromArray($data);
        }

        return [
            'data' => $solicitacoes,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Cria uma nova solicitação.
     */
    public static function create(array $data, int $solicitanteId): self
    {
        $numero = self::generateNumero();

        $stmt = Database::query(
            'INSERT INTO solicitacoes (numero, titulo, descricao, tipo, prioridade, status, solicitante_id, versao_erp, observacoes, created_at, updated_at) 
             VALUES (:numero, :titulo, :descricao, :tipo, :prioridade, :status, :solicitante_id, :versao_erp, :observacoes, NOW(), NOW())',
            [
                'numero' => $numero,
                'titulo' => $data['titulo'],
                'descricao' => $data['descricao'],
                'tipo' => $data['tipo'],
                'prioridade' => $data['prioridade'] ?? 'NORMAL',
                'status' => 'ABERTA',
                'solicitante_id' => $solicitanteId,
                'versao_erp' => $data['versao_erp'],
                'observacoes' => $data['observacoes'] ?? null,
            ]
        );

        return self::find((int) Database::lastInsertId());
    }

    /**
     * Atualiza uma solicitação existente.
     */
    public function update(array $data): self
    {
        $fields = [];
        $params = ['id' => $this->id];

        // Campos que podem ser editados nesta fase
        $editableFields = ['titulo', 'descricao', 'tipo', 'prioridade', 'status', 'versao_erp', 'observacoes'];

        foreach ($editableFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $fields[] = 'updated_at = NOW()';
            $sql = 'UPDATE solicitacoes SET ' . implode(', ', $fields) . ' WHERE id = :id';
            Database::query($sql, $params);
        }

        return self::find($this->id);
    }

    /**
     * Soft delete - marca como excluído.
     */
    public function delete(): bool
    {
        $stmt = Database::query(
            'UPDATE solicitacoes SET deleted_at = NOW() WHERE id = :id',
            ['id' => $this->id]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Carrega o solicitante.
     */
    public function loadSolicitante(): void
    {
        $this->solicitante = User::find($this->solicitante_id);
    }

    /**
     * Retorna dados da solicitação em array.
     */
    public function toArray(bool $includeSolicitante = false): array
    {
        $data = [
            'id' => $this->id,
            'numero' => $this->numero,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'tipo' => $this->tipo,
            'tipo_label' => self::TIPOS_LABELS[$this->tipo] ?? $this->tipo,
            'prioridade' => $this->prioridade,
            'prioridade_label' => self::PRIORIDADES_LABELS[$this->prioridade] ?? $this->prioridade,
            'status' => $this->status,
            'status_label' => self::STATUS_LABELS[$this->status] ?? $this->status,
            'solicitante_id' => $this->solicitante_id,
            'versao_erp' => $this->versao_erp,
            'observacoes' => $this->observacoes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];

        if ($includeSolicitante && $this->solicitante) {
            $data['solicitante'] = $this->solicitante->toArray();
        }

        return $data;
    }

    /**
     * Cria instância a partir de array.
     */
    private static function fromArray(array $data): self
    {
        $solicitacao = new self();
        $solicitacao->id = (int) $data['id'];
        $solicitacao->numero = $data['numero'];
        $solicitacao->titulo = $data['titulo'];
        $solicitacao->descricao = $data['descricao'];
        $solicitacao->tipo = $data['tipo'];
        $solicitacao->prioridade = $data['prioridade'];
        $solicitacao->status = $data['status'];
        $solicitacao->solicitante_id = (int) $data['solicitante_id'];
        $solicitacao->versao_erp = $data['versao_erp'];
        $solicitacao->observacoes = $data['observacoes'];
        $solicitacao->created_at = $data['created_at'];
        $solicitacao->updated_at = $data['updated_at'];
        $solicitacao->deleted_at = $data['deleted_at'] ?? null;
        return $solicitacao;
    }

    /**
     * Valida os dados da solicitação.
     * Para updates, apenas valida os campos que estão sendo fornecidos.
     */
    public static function validate(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        // Título
        if (array_key_exists('titulo', $data)) {
            if (empty($data['titulo'])) {
                $errors[] = 'Título não pode estar vazio';
            } elseif (strlen($data['titulo']) > 255) {
                $errors[] = 'Título deve ter no máximo 255 caracteres';
            }
        } elseif (!$isUpdate) {
            $errors[] = 'Título é obrigatório';
        }

        // Descrição
        if (array_key_exists('descricao', $data)) {
            if (empty($data['descricao'])) {
                $errors[] = 'Descrição não pode estar vazia';
            }
        } elseif (!$isUpdate) {
            $errors[] = 'Descrição é obrigatória';
        }

        // Tipo
        if (array_key_exists('tipo', $data)) {
            if (empty($data['tipo'])) {
                $errors[] = 'Tipo não pode estar vazio';
            } elseif (!in_array($data['tipo'], self::TIPOS)) {
                $errors[] = 'Tipo inválido';
            }
        } elseif (!$isUpdate) {
            $errors[] = 'Tipo é obrigatório';
        }

        // Prioridade
        if (array_key_exists('prioridade', $data)) {
            if (empty($data['prioridade'])) {
                $errors[] = 'Prioridade não pode estar vazia';
            } elseif (!in_array($data['prioridade'], self::PRIORIDADES)) {
                $errors[] = 'Prioridade inválida';
            }
        } elseif (!$isUpdate) {
            $errors[] = 'Prioridade é obrigatória';
        }

        // Status (apenas para updates)
        if ($isUpdate && array_key_exists('status', $data) && !empty($data['status'])) {
            if (!in_array($data['status'], self::STATUS)) {
                $errors[] = 'Status inválido';
            }
        }

        // Versão ERP
        if (array_key_exists('versao_erp', $data)) {
            if (empty($data['versao_erp'])) {
                $errors[] = 'Versão do ERP não pode estar vazia';
            } elseif (strlen($data['versao_erp']) > 50) {
                $errors[] = 'Versão do ERP deve ter no máximo 50 caracteres';
            }
        } elseif (!$isUpdate) {
            $errors[] = 'Versão do ERP é obrigatória';
        }

        // Observações é opcional, mas se fornecida deve ter tamanho razoável
        if (!empty($data['observacoes']) && strlen($data['observacoes']) > 5000) {
            $errors[] = 'Observações deve ter no máximo 5000 caracteres';
        }

        return $errors;
    }
}
