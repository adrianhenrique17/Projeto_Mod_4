import java.util.Scanner;

public class Main {
    private static final Scanner scanner = new Scanner(System.in);
    private static final Pilha<String> historico = new Pilha<>();
    private static final Fila<String> fila = new Fila<>();

    public static void main(String[] args) {
        int opcao;

        do {
            exibirMenuPrincipal();
            opcao = lerInteiro("Escolha uma opção: ");

            switch (opcao) {
                case 1 -> menuHistorico();
                case 2 -> menuFila();
                case 0 -> System.out.println("Programa encerrado.");
                default -> System.out.println("Opção inválida.");
            }
        } while (opcao != 0);

        scanner.close();
    }

    private static void exibirMenuPrincipal() {
        System.out.println("\n=== ESTRUTURAS DE DADOS ===");
        System.out.println("1. Histórico de navegação - Pilha");
        System.out.println("2. Atendimento de clientes - Fila");
        System.out.println("0. Sair");
    }

    private static void menuHistorico() {
        int opcao;

        do {
            System.out.println("\n=== HISTÓRICO DE NAVEGAÇÃO ===");
            System.out.println("1. Adicionar página");
            System.out.println("2. Voltar para a página anterior");
            System.out.println("3. Visualizar página atual");
            System.out.println("4. Verificar se está vazio");
            System.out.println("5. Listar páginas");
            System.out.println("0. Voltar ao menu principal");
            opcao = lerInteiro("Escolha uma opção: ");

            switch (opcao) {
                case 1 -> adicionarPagina();
                case 2 -> voltarPagina();
                case 3 -> visualizarPaginaAtual();
                case 4 -> verificarHistorico();
                case 5 -> historico.listar();
                case 0 -> System.out.println("Voltando ao menu principal.");
                default -> System.out.println("Opção inválida.");
            }
        } while (opcao != 0);
    }

    private static void adicionarPagina() {
        String pagina = lerTexto("Digite o endereço da página: ");
        historico.push(pagina);
        System.out.println("Página adicionada ao histórico.");
    }

    private static void voltarPagina() {
        String paginaRemovida = historico.pop();

        if (paginaRemovida == null) {
            System.out.println("Não há páginas para voltar.");
            return;
        }

        System.out.println("Página removida: " + paginaRemovida);
        visualizarPaginaAtual();
    }

    private static void visualizarPaginaAtual() {
        String paginaAtual = historico.peek();

        if (paginaAtual == null) {
            System.out.println("Não há página atual.");
            return;
        }

        System.out.println("Página atual: " + paginaAtual);
    }

    private static void verificarHistorico() {
        if (historico.isEmpty()) {
            System.out.println("O histórico está vazio.");
        } else {
            System.out.println("O histórico possui " + historico.tamanho() + " página(s).");
        }
    }

    private static void menuFila() {
        int opcao;

        do {
            System.out.println("\n=== ATENDIMENTO DE CLIENTES ===");
            System.out.println("1. Adicionar cliente");
            System.out.println("2. Atender próximo cliente");
            System.out.println("3. Visualizar próximo cliente");
            System.out.println("4. Verificar se está vazia");
            System.out.println("5. Listar clientes");
            System.out.println("0. Voltar ao menu principal");
            opcao = lerInteiro("Escolha uma opção: ");

            switch (opcao) {
                case 1 -> adicionarCliente();
                case 2 -> atenderCliente();
                case 3 -> visualizarProximoCliente();
                case 4 -> verificarFila();
                case 5 -> fila.listar();
                case 0 -> System.out.println("Voltando ao menu principal.");
                default -> System.out.println("Opção inválida.");
            }
        } while (opcao != 0);
    }

    private static void adicionarCliente() {
        String cliente = lerTexto("Digite o nome do cliente: ");
        fila.enqueue(cliente);
        System.out.println("Cliente adicionado à fila.");
    }

    private static void atenderCliente() {
        String cliente = fila.dequeue();

        if (cliente == null) {
            System.out.println("Não há clientes aguardando atendimento.");
            return;
        }

        System.out.println("Atendendo: " + cliente);
    }

    private static void visualizarProximoCliente() {
        String cliente = fila.front();

        if (cliente == null) {
            System.out.println("Não há clientes aguardando atendimento.");
            return;
        }

        System.out.println("Próximo cliente: " + cliente);
    }

    private static void verificarFila() {
        if (fila.isEmpty()) {
            System.out.println("A fila está vazia.");
        } else {
            System.out.println("A fila possui " + fila.tamanho() + " cliente(s).");
        }
    }

    private static int lerInteiro(String mensagem) {
        while (true) {
            try {
                System.out.print(mensagem);
                return Integer.parseInt(scanner.nextLine());
            } catch (NumberFormatException erro) {
                System.out.println("Digite um número válido.");
            }
        }
    }

    private static String lerTexto(String mensagem) {
        String texto;

        do {
            System.out.print(mensagem);
            texto = scanner.nextLine().trim();
            if (texto.isEmpty()) {
                System.out.println("O valor não pode ficar vazio.");
            }
        } while (texto.isEmpty());

        return texto;
    }
}