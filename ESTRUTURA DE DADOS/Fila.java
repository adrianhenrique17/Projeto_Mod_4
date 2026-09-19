public class Fila<T> {
    private Object[] elementos;
    private int inicio;
    private int fim;
    private int quantidade;

    public Fila() {
        elementos = new Object[10];
        inicio = 0;
        fim = 0;
        quantidade = 0;
    }

    public void enqueue(T elemento) {
        if (quantidade == elementos.length) {
            aumentarCapacidade();
        }

        elementos[fim] = elemento;
        fim = (fim + 1) % elementos.length;
        quantidade++;
    }

    @SuppressWarnings("unchecked")
    public T dequeue() {
        if (isEmpty()) {
            return null;
        }

        T elemento = (T) elementos[inicio];
        elementos[inicio] = null;
        inicio = (inicio + 1) % elementos.length;
        quantidade--;
        return elemento;
    }

    @SuppressWarnings("unchecked")
    public T front() {
        if (isEmpty()) {
            return null;
        }

        return (T) elementos[inicio];
    }

    public boolean isEmpty() {
        return quantidade == 0;
    }

    public int tamanho() {
        return quantidade;
    }

    public void listar() {
        if (isEmpty()) {
            System.out.println("A fila está vazia.");
            return;
        }

        System.out.println("Fila de atendimento:");
        for (int posicao = 0; posicao < quantidade; posicao++) {
            int indice = (inicio + posicao) % elementos.length;
            System.out.println((posicao + 1) + ". " + elementos[indice]);
        }
    }

    private void aumentarCapacidade() {
        Object[] novosElementos = new Object[elementos.length * 2];
        for (int posicao = 0; posicao < quantidade; posicao++) {
            int indice = (inicio + posicao) % elementos.length;
            novosElementos[posicao] = elementos[indice];
        }
        elementos = novosElementos;
        inicio = 0;
        fim = quantidade;
    }
}