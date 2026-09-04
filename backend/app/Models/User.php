<?php

/**
 * Modelo de Usuário
 * 
 * Representa um usuário do sistema.
 */

namespace App\Models;

use App\Database;

class User
{
    public int $id;
    public string $username;
    public string $nome;
    public string $password;
    public string $perfil;
    public bool $ativo;
    public string $created_at;
    public string $updated_at;

    /**
     * Busca usuário pelo nome de usuário.
     */
    public static function findByUsername(string $username): ?self
    {
        $stmt = Database::query(
            'SELECT * FROM users WHERE username = :username',
            ['username' => $username]
        );

        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return self::fromArray($data);
    }

    /**
     * Busca usuário pelo ID.
     */
    public static function find(int $id): ?self
    {
        $stmt = Database::query(
            'SELECT * FROM users WHERE id = :id',
            ['id' => $id]
        );

        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return self::fromArray($data);
    }

    /**
     * Cria um novo usuário.
     */
    public static function create(array $data): self
    {
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = Database::query(
            'INSERT INTO users (username, nome, password, perfil, ativo, created_at, updated_at) 
             VALUES (:username, :nome, :password, :perfil, :ativo, NOW(), NOW())',
            [
                'username' => $data['username'],
                'nome' => $data['nome'],
                'password' => $hashedPassword,
                'perfil' => $data['perfil'] ?? 'USUARIO',
                'ativo' => $data['ativo'] ?? true,
            ]
        );

        $user = self::find((int) Database::lastInsertId());
        return $user;
    }

    /**
     * Verifica se a senha está correta.
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * Retorna dados públicos do usuário (sem senha).
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'nome' => $this->nome,
            'perfil' => $this->perfil,
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
        $user = new self();
        $user->id = (int) $data['id'];
        $user->username = $data['username'];
        $user->nome = $data['nome'];
        $user->password = $data['password'];
        $user->perfil = $data['perfil'];
        $user->ativo = (bool) $data['ativo'];
        $user->created_at = $data['created_at'];
        $user->updated_at = $data['updated_at'];
        return $user;
    }
}
