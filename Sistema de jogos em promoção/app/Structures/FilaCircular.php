<?php
declare(strict_types=1);

namespace App\Structures;

use UnderflowException;

final class FilaCircular
{
    private array $itens;
    private int $capacidade;
    private int $inicio = 0;
    private int $fim = 0;
    private int $quantidade = 0;

    public function __construct(int $capacidade = 10)
    {
        $this->capacidade = $capacidade;
        $this->itens = array_fill(0, $capacidade, null);
    }

    public function enfileirar(mixed $item): void
    {
        if ($this->estaCheia()) {
            throw new \OverflowException('A fila de moderação está cheia.');
        }

        $this->itens[$this->fim] = $item;
        $this->fim = ($this->fim + 1) % $this->capacidade;
        $this->quantidade++;
    }

    public function desenfileirar(): mixed
    {
        if ($this->estaVazia()) {
            throw new UnderflowException('A fila de moderação está vazia.');
        }

        $item = $this->itens[$this->inicio];
        $this->itens[$this->inicio] = null;
        $this->inicio = ($this->inicio + 1) % $this->capacidade;
        $this->quantidade--;
        return $item;
    }

    public function estaVazia(): bool { return $this->quantidade === 0; }
    public function estaCheia(): bool { return $this->quantidade === $this->capacidade; }
    public function tamanho(): int { return $this->quantidade; }
}
