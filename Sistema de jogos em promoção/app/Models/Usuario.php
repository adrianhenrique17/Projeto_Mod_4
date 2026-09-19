<?php
declare(strict_types=1);

namespace App\Models;

final class Usuario
{
    private ?int $id;
    private string $nome;
    private string $email;
    private string $senha;
    private bool $isAdmin;

    public function __construct(string $nome, string $email, string $senha = '', ?int $id = null, bool $isAdmin = false)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->isAdmin = $isAdmin;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }
    public function getNome(): string { return $this->nome; }
    public function setNome(string $nome): void { $this->nome = $nome; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function getSenha(): string { return $this->senha; }
    public function setSenha(string $senha): void { $this->senha = password_hash($senha, PASSWORD_DEFAULT); }
    public function isAdmin(): bool { return $this->isAdmin; }
    public function setIsAdmin(bool $isAdmin): void { $this->isAdmin = $isAdmin; }
    public function verificarSenha(string $senha): bool { return password_verify($senha, $this->senha); }
    public static function fromArray(array $row): self
    {
        $usuario = new self(
            (string) ($row['nome'] ?? ''),
            (string) ($row['email'] ?? ''),
            (string) ($row['senha'] ?? ''),
            isset($row['id']) ? (int) $row['id'] : null,
            !empty($row['is_admin'])
        );

        return $usuario;
    }
}
