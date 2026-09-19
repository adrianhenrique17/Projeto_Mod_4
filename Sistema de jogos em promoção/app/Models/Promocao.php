<?php
declare(strict_types=1);

namespace App\Models;

final class Promocao
{
    private ?int $id;
    private string $titulo;
    private string $plataforma;
    private float $precoOriginal;
    private float $precoPromocional;
    private string $url;
    private string $imagemCapa;
    private string $tipoMidia;
    private bool $isPrevenda;
    private string $status;
    private ?int $usuarioId;

    public function __construct(
        string $titulo,
        string $plataforma,
        float $precoOriginal,
        float $precoPromocional,
        string $url,
        string $status = 'pendente',
        string $imagemCapa = '',
        string $tipoMidia = 'digital',
        bool $isPrevenda = false,
        ?int $usuarioId = null,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->plataforma = $plataforma;
        $this->precoOriginal = $precoOriginal;
        $this->precoPromocional = $precoPromocional;
        $this->url = $url;
        $this->imagemCapa = $imagemCapa;
        $this->tipoMidia = $tipoMidia;
        $this->isPrevenda = $isPrevenda;
        $this->status = $status;
        $this->usuarioId = $usuarioId;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }
    public function getTitulo(): string { return $this->titulo; }
    public function setTitulo(string $titulo): void { $this->titulo = $titulo; }
    public function getPlataforma(): string { return $this->plataforma; }
    public function setPlataforma(string $plataforma): void { $this->plataforma = $plataforma; }
    public function getPrecoOriginal(): float { return $this->precoOriginal; }
    public function setPrecoOriginal(float $precoOriginal): void { $this->precoOriginal = $precoOriginal; }
    public function getPrecoPromocional(): float { return $this->precoPromocional; }
    public function setPrecoPromocional(float $precoPromocional): void { $this->precoPromocional = $precoPromocional; }
    public function getUrl(): string { return $this->url; }
    public function setUrl(string $url): void { $this->url = $url; }
    public function getImagemCapa(): string { return $this->imagemCapa; }
    public function setImagemCapa(string $imagemCapa): void { $this->imagemCapa = $imagemCapa; }
    public function getTipoMidia(): string { return $this->tipoMidia; }
    public function setTipoMidia(string $tipoMidia): void { $this->tipoMidia = $tipoMidia; }
    public function isPrevenda(): bool { return $this->isPrevenda; }
    public function setIsPrevenda(bool $isPrevenda): void { $this->isPrevenda = $isPrevenda; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function getUsuarioId(): ?int { return $this->usuarioId; }
    public function setUsuarioId(?int $usuarioId): void { $this->usuarioId = $usuarioId; }
}
