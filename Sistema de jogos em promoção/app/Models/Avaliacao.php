<?php
declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;

final class Avaliacao
{
    private ?int $id;
    private Usuario $usuario;
    private Promocao $promocao;
    private int $nota;
    private string $comentario;

    public function __construct(Usuario $usuario, Promocao $promocao, int $nota, string $comentario, ?int $id = null)
    {
        if ($nota < 1 || $nota > 5) {
            throw new InvalidArgumentException('A nota deve estar entre 1 e 5.');
        }

        $this->id = $id;
        $this->usuario = $usuario;
        $this->promocao = $promocao;
        $this->nota = $nota;
        $this->comentario = $comentario;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }
    public function getUsuario(): Usuario { return $this->usuario; }
    public function setUsuario(Usuario $usuario): void { $this->usuario = $usuario; }
    public function getPromocao(): Promocao { return $this->promocao; }
    public function setPromocao(Promocao $promocao): void { $this->promocao = $promocao; }
    public function getNota(): int { return $this->nota; }
    public function setNota(int $nota): void { $this->nota = $nota; }
    public function getComentario(): string { return $this->comentario; }
    public function setComentario(string $comentario): void { $this->comentario = $comentario; }
}
