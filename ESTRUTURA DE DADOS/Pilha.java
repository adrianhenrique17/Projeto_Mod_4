public class Pilha<T> {
    private Object[] elementos;
    private int topo;

    public Pilha() {
        elementos = new Object[10];
        topo = -1;
    }

    public void push(T elemento) {
        if (topo == elementos.length - 1) {
            aumentarCapacidade();
        }

        elementos[++topo] = elemento;
    }

    @SuppressWarnings("unchecked")
    public T pop() {
        if (isEmpty()) {
            return null;
        }

        T elemento = (T) elementos[topo];
        elementos[topo--] = null;
        return elemento;
    }

    @SuppressWarnings("unchecked")
    public T peek() {
        if (isEmpty()) {
            return null;
        }

        return (T) elementos[topo];
    }

    public boolean isEmpty() {
        return topo == -1;
    }

    public int tamanho() {
        return topo + 1;
    }

    public void listar() {
        if (isEmpty()) {
            System.out.println("O histórico está vazio.");
            return;
        }

        System.out.println("Histórico de páginas:");
        for (int indice = topo; indice >= 0; indice--) {
            System.out.println((topo - indice + 1) + ". " + elementos[indice]);
        }
    }

    private void aumentarCapacidade() {
        Object[] novosElementos = new Object[elementos.length * 2];
        for (int indice = 0; indice < elementos.length; indice++) {
            novosElementos[indice] = elementos[indice];
        }
        elementos = novosElementos;
    }
}