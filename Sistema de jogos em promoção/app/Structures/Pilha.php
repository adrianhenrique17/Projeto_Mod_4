<?php
declare(strict_types=1);

namespace App\Structures;

use UnderflowException;

final class Pilha
{
    private array $itens = [];
    private int $topo = -1;

    public function empilhar(mixed $item): void
    {
        $this->topo++;
        $this->itens[$this->topo] = $item;
    }

    public function desempilhar(): mixed
    {
        if ($this->estaVazia()) {
            throw new UnderflowException('A pilha de ações está vazia.');
        }

        $item = $this->itens[$this->topo];
        unset($this->itens[$this->topo]);
        $this->topo--;
        return $item;
    }

    public function estaVazia(): bool { return $this->topo === -1; }
    public function tamanho(): int { return $this->topo + 1; }
}
