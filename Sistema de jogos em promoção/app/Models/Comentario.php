<?php
declare(strict_types=1);

namespace App\Models;

final class Comentario
{
    private ?int $id;
    private Usuario $usuario;
    private Promocao $promocao;
    private string $texto;

    public function __construct(Usuario $usuario, Promocao $promocao, string $texto, ?int $id = null)
    {
        $this->id = $id;
        $this->usuario = $usuario;
        $this->promocao = $promocao;
        $this->texto = trim($texto);
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }
    public function getUsuario(): Usuario { return $this->usuario; }
    public function setUsuario(Usuario $usuario): void { $this->usuario = $usuario; }
    public function getPromocao(): Promocao { return $this->promocao; }
    public function setPromocao(Promocao $promocao): void { $this->promocao = $promocao; }
    public function getTexto(): string { return $this->texto; }
    public function setTexto(string $texto): void { $this->texto = trim($texto); }
}
