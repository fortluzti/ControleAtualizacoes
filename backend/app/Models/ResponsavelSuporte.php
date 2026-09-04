<?php

/**
 * Modelo de Responsável do Suporte
 * 
 * Representa um responsável externo do suporte técnico.
 */

namespace App\Models;

use App\Database;

class ResponsavelSuporte
{
    public int $id;
    public string $nome;
    public ?string $telefone;
    public ?string $email;
    public ?string $empresa;
    public ?string $observacoes;
    public bool $ativo;
    public string $created_at;
    public string $updated_at;

    /**
     * Busca um responsável pelo ID.
     */
    public static function find(int $id): ?self
    {
        $stmt = Database::query(
            'SELECT * FROM responsaveis_suporte WHERE id = :id',
            ['id' => $id]
        );

        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return self::fromArray($data);
    }

    /**
     * Lista todos os responsáveis ativos.
     */
    public static function listAtivos(): array
    {
        $stmt = Database::query(
            'SELECT * FROM responsaveis_suporte WHERE ativo = 1 ORDER BY nome ASC'
        );

        $responsaveis = [];
        while ($data = $stmt->fetch()) {
            $responsaveis[] = self::fromArray($data);
        }

        return $responsaveis;
    }

    /**
     * Lista todos os responsáveis.
     */
    public static function listAll(): array
    {
        $stmt = Database::query(
            'SELECT * FROM responsaveis_suporte ORDER BY ativo DESC, nome ASC'
        );

        $responsaveis = [];
        while ($data = $stmt->fetch()) {
            $responsaveis[] = self::fromArray($data);
        }

        return $responsaveis;
    }

    /**
     * Cria um novo responsável.
     */
    public static function create(array $data): self
    {
        $stmt = Database::query(
            'INSERT INTO responsaveis_suporte (nome, telefone, email, empresa, observacoes, ativo, created_at, updated_at) 
             VALUES (:nome, :telefone, :email, :empresa, :observacoes, :ativo, NOW(), NOW())',
            [
                'nome' => $data['nome'],
                'telefone' => $data['telefone'] ?? null,
                'email' => $data['email'] ?? null,
                'empresa' => $data['empresa'] ?? null,
                'observacoes' => $data['observacoes'] ?? null,
                'ativo' => $data['ativo'] ?? true,
            ]
        );

        return self::find((int) Database::lastInsertId());
    }

    /**
     * Atualiza um responsável existente.
     */
    public function update(array $data): self
    {
        $fields = [];
        $params = ['id' => $this->id];

        $editableFields = ['nome', 'telefone', 'email', 'empresa', 'observacoes', 'ativo'];

        foreach ($editableFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $fields[] = 'updated_at = NOW()';
            $sql = 'UPDATE responsaveis_suporte SET ' . implode(', ', $fields) . ' WHERE id = :id';
            Database::query($sql, $params);
        }

        return self::find($this->id);
    }

    /**
     * Desativa um responsável.
     */
    public function deactivate(): bool
    {
        $stmt = Database::query(
            'UPDATE responsaveis_suporte SET ativo = 0, updated_at = NOW() WHERE id = :id',
            ['id' => $this->id]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Ativa um responsável.
     */
    public function activate(): bool
    {
        $stmt = Database::query(
            'UPDATE responsaveis_suporte SET ativo = 1, updated_at = NOW() WHERE id = :id',
            ['id' => $this->id]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Retorna dados do responsável em array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'telefone' => $this->telefone,
            'email' => $this->email,
            'empresa' => $this->empresa,
            'observacoes' => $this->observacoes,
            'ativo' => $this->ativo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Cria instância a partir de array.
     */
    private static function fromArray(array $data): self
    {
        $responsavel = new self();
        $responsavel->id = (int) $data['id'];
        $responsavel->nome = $data['nome'];
        $responsavel->telefone = $data['telefone'];
        $responsavel->email = $data['email'];
        $responsavel->empresa = $data['empresa'];
        $responsavel->observacoes = $data['observacoes'];
        $responsavel->ativo = (bool) $data['ativo'];
        $responsavel->created_at = $data['created_at'];
        $responsavel->updated_at = $data['updated_at'];
        return $responsavel;
    }

    /**
     * Valida os dados do responsável.
     */
    public static function validate(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        // Nome é obrigatório
        if (empty($data['nome'])) {
            $errors[] = 'Nome é obrigatório';
        } elseif (strlen($data['nome']) > 100) {
            $errors[] = 'Nome deve ter no máximo 100 caracteres';
        }

        // Email é opcional mas se fornecido deve ser válido
        if (!empty($data['email']) && strlen($data['email']) > 100) {
            $errors[] = 'Email deve ter no máximo 100 caracteres';
        }

        // Telefone é opcional mas se fornecido deve ter tamanho razoável
        if (!empty($data['telefone']) && strlen($data['telefone']) > 20) {
            $errors[] = 'Telefone deve ter no máximo 20 caracteres';
        }

        // Empresa é opcional mas se fornecida deve ter tamanho razoável
        if (!empty($data['empresa']) && strlen($data['empresa']) > 100) {
            $errors[] = 'Empresa deve ter no máximo 100 caracteres';
        }

        return $errors;
    }
}
