<?php
declare(strict_types=1);

namespace App\Models;

final class AlertaPreco
{
    private ?int $id;
    private Usuario $usuario;
    private string $nomeJogo;
    private float $precoAlvo;

    public function __construct(Usuario $usuario, string $nomeJogo, float $precoAlvo, ?int $id = null)
    {
        $this->id = $id;
        $this->usuario = $usuario;
        $this->nomeJogo = trim($nomeJogo);
        $this->precoAlvo = $precoAlvo;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }
    public function getUsuario(): Usuario { return $this->usuario; }
    public function setUsuario(Usuario $usuario): void { $this->usuario = $usuario; }
    public function getNomeJogo(): string { return $this->nomeJogo; }
    public function setNomeJogo(string $nomeJogo): void { $this->nomeJogo = trim($nomeJogo); }
    public function getPrecoAlvo(): float { return $this->precoAlvo; }
    public function setPrecoAlvo(float $precoAlvo): void { $this->precoAlvo = $precoAlvo; }
}
