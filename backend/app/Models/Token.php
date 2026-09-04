<?php

/**
 * Modelo de Token de API
 * 
 * Gerencia tokens de autenticação para a API REST.
 */

namespace App\Models;

use App\Database;
use Random\RandomException;

class Token
{
    public int $id;
    public int $user_id;
    public string $token;
    public string $created_at;
    public ?string $expires_at;
    public ?string $last_used_at;

    private const TOKEN_LENGTH = 64;

    /**
     * Gera um novo token para o usuário.
     */
    public static function generate(int $userId, ?int $expiresInDays = 7): self
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $expiresAt = $expiresInDays ? date('Y-m-d H:i:s', strtotime("+{$expiresInDays} days")) : null;

        Database::query(
            'INSERT INTO api_tokens (user_id, token, created_at, expires_at) VALUES (:user_id, :token, NOW(), :expires_at)',
            [
                'user_id' => $userId,
                'token' => hash('sha256', $token),
                'expires_at' => $expiresAt,
            ]
        );

        $stmt = Database::query('SELECT * FROM api_tokens WHERE id = :id', ['id' => Database::lastInsertId()]);
        $data = $stmt->fetch();

        $tokenObj = new self();
        $tokenObj->id = (int) $data['id'];
        $tokenObj->user_id = (int) $data['user_id'];
        $tokenObj->token = $token; // Return plain token (will be hashed in DB)
        $tokenObj->created_at = $data['created_at'];
        $tokenObj->expires_at = $data['expires_at'];
        $tokenObj->last_used_at = $data['last_used_at'];

        return $tokenObj;
    }

    /**
     * Valida um token e retorna o usuário associado.
     */
    public static function validate(string $plainToken): ?User
    {
        $hashedToken = hash('sha256', $plainToken);

        $stmt = Database::query(
            'SELECT t.*, u.* FROM api_tokens t 
             JOIN users u ON t.user_id = u.id 
             WHERE t.token = :token AND t.deleted_at IS NULL AND u.ativo = 1',
            ['token' => $hashedToken]
        );

        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        // Check expiration
        if ($data['expires_at'] && strtotime($data['expires_at']) < time()) {
            return null;
        }

        // Update last used
        Database::query(
            'UPDATE api_tokens SET last_used_at = NOW() WHERE id = :id',
            ['id' => $data['id']]
        );

        return User::find((int) $data['user_id']);
    }

    /**
     * Invalida um token específico.
     */
    public static function invalidate(string $plainToken): bool
    {
        $hashedToken = hash('sha256', $plainToken);

        $stmt = Database::query(
            'UPDATE api_tokens SET deleted_at = NOW() WHERE token = :token AND deleted_at IS NULL',
            ['token' => $hashedToken]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Invalida todos os tokens de um usuário.
     */
    public static function invalidateAllForUser(int $userId): int
    {
        $stmt = Database::query(
            'UPDATE api_tokens SET deleted_at = NOW() WHERE user_id = :user_id AND deleted_at IS NULL',
            ['user_id' => $userId]
        );

        return $stmt->rowCount();
    }

    /**
     * Limpa tokens expirados.
     */
    public static function cleanupExpired(): int
    {
        $stmt = Database::query(
            'UPDATE api_tokens SET deleted_at = NOW() WHERE expires_at < NOW() AND deleted_at IS NULL'
        );

        return $stmt->rowCount();
    }
}
