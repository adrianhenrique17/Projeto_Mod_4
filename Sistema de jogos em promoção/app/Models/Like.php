<?php
declare(strict_types=1);

namespace App\Models;

final class Like
{
    private ?int $id;
    private Usuario $usuario;
    private Promocao $promocao;

    public function __construct(Usuario $usuario, Promocao $promocao, ?int $id = null)
    {
        $this->id = $id;
        $this->usuario = $usuario;
        $this->promocao = $promocao;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }
    public function getUsuario(): Usuario { return $this->usuario; }
    public function setUsuario(Usuario $usuario): void { $this->usuario = $usuario; }
    public function getPromocao(): Promocao { return $this->promocao; }
    public function setPromocao(Promocao $promocao): void { $this->promocao = $promocao; }
}
