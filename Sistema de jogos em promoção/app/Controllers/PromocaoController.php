<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Avaliacao;
use App\Models\Database;
use App\Models\Usuario;
use App\Models\Promocao;
use App\Structures\FilaCircular;
use App\Structures\Pilha;
use InvalidArgumentException;
use PDO;
use Throwable;

final class PromocaoController
{
    private Database $database;
    private FilaCircular $filaModeracao;
    private Pilha $historico;

    public function __construct()
    {
        $this->database = new Database();
        $this->filaModeracao = new FilaCircular(20);
        $this->historico = new Pilha();
    }

    public function index(): void
    {
        $modo = 'home';
        $usuario = $this->usuarioLogado();
        $promocoes = $this->listarPromocoes($this->obterFiltros());
        $mensagem = $_GET['erro'] ?? null;
        $sucesso = $_GET['sucesso'] ?? null;

        require __DIR__ . '/../Views/promocoes/index.php';
    }

    public function login(): void
    {
        $modo = 'login';
        $usuario = $this->usuarioLogado();
        $mensagem = $_GET['erro'] ?? null;
        $sucesso = $_GET['sucesso'] ?? null;

        require __DIR__ . '/../Views/promocoes/index.php';
    }

    public function perfil(): void
    {
        $usuario = $this->usuarioLogado();
        if ($usuario === null) {
            header('Location: index.php?action=login&erro=faça-login-para-acessar-o-perfil');
            return;
        }

        $modo = 'perfil';
        $mensagem = $_GET['erro'] ?? null;
        $sucesso = $_GET['sucesso'] ?? null;

        require __DIR__ . '/../Views/promocoes/index.php';
    }

    public function novo(): void
    {
        if ($this->usuarioLogado() === null) {
            header('Location: index.php?action=login&erro=faça-login-para-adicionar-uma-oferta');
            return;
        }

        $modo = 'novo';
        $usuario = $this->usuarioLogado();
        $mensagem = $_GET['erro'] ?? null;
        $sucesso = $_GET['sucesso'] ?? null;

        require __DIR__ . '/../Views/promocoes/index.php';
    }

    public function atualizarPerfil(array $dados, array $arquivos = []): void
    {
        $usuario = $this->usuarioLogado();
        if ($usuario === null) {
            header('Location: index.php?action=login&erro=faça-login-para-atualizar-seu-perfil');
            return;
        }

        $nome = trim((string) ($dados['nome'] ?? ''));
        if ($nome === '') {
            header('Location: index.php?action=perfil&erro=nome-e-obrigatorio');
            return;
        }

        try {
            $foto = $this->processarFotoPerfil($arquivos['foto_perfil'] ?? null, '');

            $connection = $this->database->connect();
            $statement = $connection->prepare('UPDATE usuarios SET nome = :nome, foto_perfil = :foto_perfil WHERE id = :id');
            $statement->execute([
                ':nome' => $nome,
                ':foto_perfil' => $foto,
                ':id' => (int) $usuario['id'],
            ]);

            $_SESSION['usuario']['nome'] = $nome;
            $_SESSION['usuario']['foto_perfil'] = $foto;

            header('Location: index.php?action=perfil&sucesso=perfil-atualizado');
        } catch (Throwable $exception) {
            header('Location: index.php?action=perfil&erro=' . rawurlencode($exception->getMessage()));
        }
    }

    public function detalhes(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $usuario = $this->usuarioLogado();
        $promocao = $this->obterPromocaoDetalhada($id);

        if ($promocao === null) {
            header('Location: index.php?erro=promocao-nao-encontrada');
            return;
        }

        $modo = 'detalhes';
        $mensagem = $_GET['erro'] ?? null;
        $sucesso = $_GET['sucesso'] ?? null;
        $comentarios = $this->listarComentariosDoBanco($id);

        require __DIR__ . '/../Views/promocoes/index.php';
    }

    public function minhasSalvas(): void
    {
        $usuario = $this->usuarioLogado();
        if ($usuario === null) {
            header('Location: index.php?action=login&erro=faça-login-para-acessar-suas-ofertas-salvas');
            return;
        }

        $modo = 'minhas-salvas';
        $mensagem = $_GET['erro'] ?? null;
        $sucesso = $_GET['sucesso'] ?? null;
        $promocoes = $this->listarPromocoesSalvas((int) $usuario['id']);

        require __DIR__ . '/../Views/promocoes/index.php';
    }

    public function entrar(array $dados): void
    {
        $email = trim((string) ($dados['email'] ?? ''));
        $senha = (string) ($dados['senha'] ?? '');

        if ($email === '' || $senha === '') {
            header('Location: index.php?action=login&erro=preencha-email-e-senha');
            return;
        }

        try {
            $query = $this->database->connect()->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
            $query->execute([':email' => $email]);
            $usuario = $query->fetch();

            if ($usuario === false || !password_verify($senha, (string) $usuario['senha'])) {
                throw new InvalidArgumentException('Credenciais inválidas.');
            }

            $_SESSION['usuario'] = [
                'id' => (int) $usuario['id'],
                'nome' => (string) $usuario['nome'],
                'email' => (string) $usuario['email'],
                'foto_perfil' => (string) ($usuario['foto_perfil'] ?? ''),
                'is_admin' => !empty($usuario['is_admin']),
            ];

            if (!empty($usuario['is_admin'])) {
                header('Location: index.php?action=moderacao&sucesso=bem-vindo-moderador');
                return;
            }

            header('Location: index.php?sucesso=bem-vindo');
        } catch (Throwable $exception) {
            header('Location: index.php?action=login&erro=' . rawurlencode($exception->getMessage()));
        }
    }

    public function registrar(array $dados): void
    {
        $nome = trim((string) ($dados['nome'] ?? ''));
        $email = trim((string) ($dados['email'] ?? ''));
        $senha = (string) ($dados['senha'] ?? '');

        if ($nome === '' || $email === '' || strlen($senha) < 6) {
            header('Location: index.php?action=login&erro=nome-email-e-senha-validos-sao-obrigatorios');
            return;
        }

        try {
            $connection = $this->database->connect();
            $verifica = $connection->prepare('SELECT id FROM usuarios WHERE email = :email');
            $verifica->execute([':email' => $email]);

            if ($verifica->fetch() !== false) {
                throw new InvalidArgumentException('Este e-mail já está cadastrado.');
            }

            $statement = $connection->prepare(
                'INSERT INTO usuarios (nome, email, senha, is_admin) VALUES (:nome, :email, :senha, 0)'
            );
            $statement->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':senha' => password_hash($senha, PASSWORD_DEFAULT),
            ]);

            header('Location: index.php?action=login&sucesso=usuario-cadastrado');
        } catch (Throwable $exception) {
            header('Location: index.php?action=login&erro=' . rawurlencode($exception->getMessage()));
        }
    }

    public function moderacao(): void
    {
        $usuario = $this->usuarioLogado();
        if (!$this->podeAcessarModeracao()) {
            header('Location: index.php?action=login&erro=acesso-restrito-a-moderacao');
            return;
        }

        $modo = 'moderacao';
        $mensagem = $_GET['erro'] ?? null;
        $sucesso = $_GET['sucesso'] ?? null;
        $pesquisaOferta = trim((string) ($_GET['pesquisa_oferta'] ?? ''));
        $pesquisaUsuario = trim((string) ($_GET['pesquisa_usuario'] ?? ''));

        $usuarios = $this->listarUsuarios();
        if ($pesquisaUsuario !== '') {
            $usuarios = array_values(array_filter(
                $usuarios,
                static fn (array $item): bool => stripos((string) ($item['nome'] ?? ''), $pesquisaUsuario) !== false
                    || stripos((string) ($item['email'] ?? ''), $pesquisaUsuario) !== false
            ));
        }

        $promocoesPendentes = $this->listarPromocoesPendentes();
        if ($pesquisaOferta !== '') {
            $promocoesPendentes = array_values(array_filter(
                $promocoesPendentes,
                static fn (array $item): bool => stripos((string) ($item['titulo'] ?? ''), $pesquisaOferta) !== false
                    || stripos((string) ($item['plataforma'] ?? ''), $pesquisaOferta) !== false
            ));
        }

        $promocoesAprovadas = $this->listarPromocoesAprovadas();
        if ($pesquisaOferta !== '') {
            $promocoesAprovadas = array_values(array_filter(
                $promocoesAprovadas,
                static fn (array $item): bool => stripos((string) ($item['titulo'] ?? ''), $pesquisaOferta) !== false
                    || stripos((string) ($item['plataforma'] ?? ''), $pesquisaOferta) !== false
            ));
        }

        require __DIR__ . '/../Views/promocoes/index.php';
    }

    public function aprovarPromocao(array $dados): void
    {
        if (!$this->podeAcessarModeracao()) {
            header('Location: index.php?action=login&erro=acesso-restrito-a-moderacao');
            return;
        }

        $id = (int) ($dados['id'] ?? 0);
        if ($id <= 0) {
            header('Location: index.php?action=moderacao&erro=promocao-invalida');
            return;
        }

        try {
            $statement = $this->database->connect()->prepare('UPDATE promocoes SET status = :status WHERE id = :id');
            $statement->execute([':status' => 'aprovada', ':id' => $id]);
            header('Location: index.php?action=moderacao&sucesso=promocao-aprovada');
        } catch (Throwable $exception) {
            header('Location: index.php?action=moderacao&erro=' . rawurlencode($exception->getMessage()));
        }
    }

    public function rejeitarPromocao(array $dados): void
    {
        if (!$this->podeAcessarModeracao()) {
            header('Location: index.php?action=login&erro=acesso-restrito-a-moderacao');
            return;
        }

        $id = (int) ($dados['id'] ?? 0);
        if ($id <= 0) {
            header('Location: index.php?action=moderacao&erro=promocao-invalida');
            return;
        }

        try {
            $statement = $this->database->connect()->prepare('UPDATE promocoes SET status = :status WHERE id = :id');
            $statement->execute([':status' => 'rejeitada', ':id' => $id]);
            header('Location: index.php?action=moderacao&sucesso=promocao-rejeitada');
        } catch (Throwable $exception) {
            header('Location: index.php?action=moderacao&erro=' . rawurlencode($exception->getMessage()));
        }
    }

    public function excluirPromocao(array $dados): void
    {
        if (!$this->podeAcessarModeracao()) {
            header('Location: index.php?action=login&erro=acesso-restrito-a-moderacao');
            return;
        }

        $id = (int) ($dados['id'] ?? 0);
        if ($id <= 0) {
            header('Location: index.php?action=moderacao&erro=promocao-invalida');
            return;
        }

        try {
            $statement = $this->database->connect()->prepare('DELETE FROM promocoes WHERE id = :id');
            $statement->execute([':id' => $id]);
            header('Location: index.php?action=moderacao&sucesso=promocao-excluida');
        } catch (Throwable $exception) {
            header('Location: index.php?action=moderacao&erro=' . rawurlencode($exception->getMessage()));
        }
    }

    public function editarUsuario(array $dados): void
    {
        if (!$this->podeAcessarModeracao()) {
            header('Location: index.php?action=login&erro=acesso-restrito-a-moderacao');
            return;
        }

        $id = (int) ($dados['id'] ?? 0);
        $nome = trim((string) ($dados['nome'] ?? ''));
        $email = trim((string) ($dados['email'] ?? ''));
        $senha = (string) ($dados['senha'] ?? '');

        if ($id <= 0 || $nome === '' || $email === '') {
            header('Location: index.php?action=moderacao&erro=dados-do-usuario-invalidos');
            return;
        }

        try {
            $connection = $this->database->connect();
            if ($senha !== '') {
                $statement = $connection->prepare('UPDATE usuarios SET nome = :nome, email = :email, senha = :senha WHERE id = :id');
                $statement->execute([
                    ':nome' => $nome,
                    ':email' => $email,
                    ':senha' => password_hash($senha, PASSWORD_DEFAULT),
                    ':id' => $id,
                ]);
            } else {
                $statement = $connection->prepare('UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id');
                $statement->execute([
                    ':nome' => $nome,
                    ':email' => $email,
                    ':id' => $id,
                ]);
            }

            header('Location: index.php?action=moderacao&sucesso=usuario-atualizado');
        } catch (Throwable $exception) {
            header('Location: index.php?action=moderacao&erro=' . rawurlencode($exception->getMessage()));
        }
    }

    public function excluirUsuario(array $dados): void
    {
        if (!$this->podeAcessarModeracao()) {
            header('Location: index.php?action=login&erro=acesso-restrito-a-moderacao');
            return;
        }

        $id = (int) ($dados['id'] ?? 0);
        $usuarioAtual = $this->usuarioLogado();

        if ($id <= 0 || $usuarioAtual === null || (int) $usuarioAtual['id'] === $id) {
            header('Location: index.php?action=moderacao&erro=nao-e-possivel-excluir-este-usuario');
            return;
        }

        try {
            $statement = $this->database->connect()->prepare('DELETE FROM usuarios WHERE id = :id');
            $statement->execute([':id' => $id]);
            header('Location: index.php?action=moderacao&sucesso=usuario-excluido');
        } catch (Throwable $exception) {
            header('Location: index.php?action=moderacao&erro=' . rawurlencode($exception->getMessage()));
        }
    }

    public function editarPromocao(array $dados): void
    {
        if (!$this->podeAcessarModeracao()) {
            header('Location: index.php?action=login&erro=acesso-restrito-a-moderacao');
            return;
        }

        $id = (int) ($dados['id'] ?? 0);
        $titulo = trim((string) ($dados['titulo'] ?? ''));
        $plataforma = (string) ($dados['plataforma'] ?? 'PC');
        $precoOriginal = (float) ($dados['preco_original'] ?? 0);
        $precoPromocional = (float) ($dados['preco_promocional'] ?? 0);
        $url = trim((string) ($dados['url'] ?? ''));
        $tipoMidia = (string) ($dados['tipo_midia'] ?? 'digital');

        if ($id <= 0 || $titulo === '' || $precoOriginal <= 0 || $precoPromocional <= 0 || $url === '') {
            header('Location: index.php?action=moderacao&erro=dados-da-promocao-invalidos');
            return;
        }

        try {
            $statement = $this->database->connect()->prepare(
                'UPDATE promocoes SET titulo = :titulo, plataforma = :plataforma, preco_original = :preco_original, preco_promocional = :preco_promocional, url = :url, tipo_midia = :tipo_midia WHERE id = :id'
            );
            $statement->execute([
                ':titulo' => $titulo,
                ':plataforma' => $plataforma,
                ':preco_original' => $precoOriginal,
                ':preco_promocional' => $precoPromocional,
                ':url' => $url,
                ':tipo_midia' => $tipoMidia,
                ':id' => $id,
            ]);

            header('Location: index.php?action=moderacao&sucesso=promocao-atualizada');
        } catch (Throwable $exception) {
            header('Location: index.php?action=moderacao&erro=' . rawurlencode($exception->getMessage()));
        }
    }

    public function podeAcessarModeracao(): bool
    {
        $usuario = $this->usuarioLogado();
        return $usuario !== null && !empty($usuario['is_admin']);
    }

    public function store(array $dados, array $arquivos = []): void
    {
        $usuario = $this->usuarioLogado();
        if ($usuario === null) {
            header('Location: index.php?action=login&erro=faça-login-para-adicionar-uma-promocao');
            return;
        }

        try {
            $imagemCapa = $this->processarImagemCapa($arquivos['imagem_capa'] ?? null, trim((string) ($dados['imagem_capa'] ?? '')));

            $promocao = new Promocao(
                trim((string) ($dados['titulo'] ?? '')),
                (string) ($dados['plataforma'] ?? 'PC'),
                (float) ($dados['preco_original'] ?? 0),
                (float) ($dados['preco_promocional'] ?? 0),
                trim((string) ($dados['url'] ?? '')),
                'pendente',
                $imagemCapa,
                (string) ($dados['tipo_midia'] ?? 'digital'),
                !empty($dados['is_prevenda']),
                (int) $usuario['id']
            );

            if ($promocao->getTitulo() === '' || $promocao->getPrecoPromocional() <= 0 || $promocao->getPrecoOriginal() <= 0) {
                header('Location: index.php?action=novo&erro=preencha-os-campos-obrigatorios');
                return;
            }

            $sql = 'INSERT INTO promocoes (titulo, plataforma, preco_original, preco_promocional, url, imagem_capa, tipo_midia, is_prevenda, status, usuario_id) VALUES (:titulo, :plataforma, :preco_original, :preco_promocional, :url, :imagem_capa, :tipo_midia, :is_prevenda, :status, :usuario_id)';
            $statement = $this->database->connect()->prepare($sql);
            $statement->execute([
                ':titulo' => $promocao->getTitulo(),
                ':plataforma' => $promocao->getPlataforma(),
                ':preco_original' => $promocao->getPrecoOriginal(),
                ':preco_promocional' => $promocao->getPrecoPromocional(),
                ':url' => $promocao->getUrl(),
                ':imagem_capa' => $promocao->getImagemCapa(),
                ':tipo_midia' => $promocao->getTipoMidia(),
                ':is_prevenda' => $promocao->isPrevenda() ? 1 : 0,
                ':status' => $promocao->getStatus(),
                ':usuario_id' => $promocao->getUsuarioId(),
            ]);

            $this->filaModeracao->enfileirar($promocao);
            $this->historico->empilhar('Cadastro: ' . $promocao->getTitulo());
            header('Location: index.php?sucesso=promocao-enviada-para-moderacao');
        } catch (Throwable $exception) {
            header('Location: index.php?action=novo&erro=' . rawurlencode($exception->getMessage()));
        }
    }

    private function processarImagemCapa(?array $arquivoUpload, string $urlAtual = ''): string
    {
        if (is_array($arquivoUpload) && !empty($arquivoUpload['name'])) {
            if (!isset($arquivoUpload['tmp_name']) || !is_uploaded_file($arquivoUpload['tmp_name'])) {
                throw new InvalidArgumentException('Arquivo de imagem inválido.');
            }

            $nomeOriginal = basename((string) $arquivoUpload['name']);
            $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($extensao, $permitidas, true)) {
                throw new InvalidArgumentException('Formato de imagem inválido. Use JPG, PNG ou WEBP.');
            }

            $diretorio = __DIR__ . '/../../uploads/promocoes/';
            if (!is_dir($diretorio) && !mkdir($diretorio, 0777, true) && !is_dir($diretorio)) {
                throw new InvalidArgumentException('Não foi possível criar a pasta de uploads.');
            }

            $nomeArquivo = 'promo_' . uniqid('', true) . '.' . $extensao;
            $destino = $diretorio . $nomeArquivo;

            if (!move_uploaded_file($arquivoUpload['tmp_name'], $destino)) {
                throw new InvalidArgumentException('Não foi possível salvar a imagem no servidor.');
            }

            return 'uploads/promocoes/' . $nomeArquivo;
        }

        return trim($urlAtual);
    }

    private function processarFotoPerfil(?array $arquivoUpload, string $urlAtual = ''): string
    {
        if (is_array($arquivoUpload) && !empty($arquivoUpload['name'])) {
            if (!isset($arquivoUpload['tmp_name']) || !is_uploaded_file($arquivoUpload['tmp_name'])) {
                throw new InvalidArgumentException('Arquivo de foto inválido.');
            }

            $nomeOriginal = basename((string) $arquivoUpload['name']);
            $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($extensao, $permitidas, true)) {
                throw new InvalidArgumentException('Formato de foto inválido. Use JPG, PNG ou WEBP.');
            }

            $diretorio = __DIR__ . '/../../uploads/perfis/';
            if (!is_dir($diretorio) && !mkdir($diretorio, 0777, true) && !is_dir($diretorio)) {
                throw new InvalidArgumentException('Não foi possível criar a pasta de fotos de perfil.');
            }

            $nomeArquivo = 'perfil_' . uniqid('', true) . '.' . $extensao;
            $destino = $diretorio . $nomeArquivo;

            if (!move_uploaded_file($arquivoUpload['tmp_name'], $destino)) {
                throw new InvalidArgumentException('Não foi possível salvar a foto no servidor.');
            }

            return 'uploads/perfis/' . $nomeArquivo;
        }

        if (trim($urlAtual) !== '') {
            return trim($urlAtual);
        }

        return '';
    }

    public function avaliar(array $dados): void
    {
        try {
            $usuario = $this->usuarioLogado();
            if ($usuario === null) {
                throw new InvalidArgumentException('Você precisa estar logado para avaliar.');
            }

            $connection = $this->database->connect();
            $promotionQuery = $connection->prepare('SELECT * FROM promocoes WHERE id = :id AND status = \'aprovada\'');
            $promotionQuery->execute([':id' => (int) ($dados['promocao_id'] ?? 0)]);
            $promotionRow = $promotionQuery->fetch();

            if ($promotionRow === false) {
                throw new InvalidArgumentException('Promoção inválida.');
            }

            $promocao = new Promocao(
                (string) $promotionRow['titulo'],
                (string) $promotionRow['plataforma'],
                (float) $promotionRow['preco_original'],
                (float) $promotionRow['preco_promocional'],
                (string) $promotionRow['url'],
                (string) $promotionRow['status'],
                (string) $promotionRow['imagem_capa'],
                (string) $promotionRow['tipo_midia'],
                (bool) $promotionRow['is_prevenda'],
                (int) $promotionRow['usuario_id'],
                (int) $promotionRow['id']
            );

            $usuarioModel = new Usuario((string) $usuario['nome'], (string) $usuario['email'], '', (int) $usuario['id']);
            $avaliacao = new Avaliacao(
                $usuarioModel,
                $promocao,
                (int) ($dados['nota'] ?? 0),
                trim((string) ($dados['comentario'] ?? ''))
            );

            $statement = $connection->prepare(
                'INSERT INTO avaliacoes (usuario_id, promocao_id, nota, comentario) VALUES (:usuario, :promocao, :nota, :comentario)'
            );
            $statement->execute([
                ':usuario' => $avaliacao->getUsuario()->getId(),
                ':promocao' => $avaliacao->getPromocao()->getId(),
                ':nota' => $avaliacao->getNota(),
                ':comentario' => $avaliacao->getComentario(),
            ]);

            header('Location: index.php?sucesso=avaliacao-enviada');
        } catch (Throwable $exception) {
            header('Location: index.php?erro=' . rawurlencode($exception->getMessage()));
        }
    }

    public function comentar(array $dados): void
    {
        $usuario = $this->usuarioLogado();
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($usuario === null) {
            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Faça login para participar da discussão.']);
                return;
            }

            header('Location: index.php?action=login&erro=faça-login-para-comentar');
            return;
        }

        $idPromocao = (int) ($dados['promocao_id'] ?? 0);
        $texto = trim((string) ($dados['texto'] ?? ''));

        if ($idPromocao <= 0 || $texto === '') {
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Comentário inválido.']);
                return;
            }

            header('Location: index.php?erro=comentario-invalido');
            return;
        }

        try {
            $connection = $this->database->connect();
            $promocao = $connection->prepare('SELECT id FROM promocoes WHERE id = :id AND status = \'aprovada\'');
            $promocao->execute([':id' => $idPromocao]);
            if ($promocao->fetch() === false) {
                throw new InvalidArgumentException('Promoção não encontrada.');
            }

            $insert = $connection->prepare('INSERT INTO comentarios (usuario_id, promocao_id, texto) VALUES (:usuario_id, :promocao_id, :texto)');
            $insert->execute([
                ':usuario_id' => (int) $usuario['id'],
                ':promocao_id' => $idPromocao,
                ':texto' => $texto,
            ]);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'comentario-publicado',
                    'nome_usuario' => (string) $usuario['nome'],
                    'texto' => $texto,
                ]);
                return;
            }

            header('Location: index.php?action=detalhes&id=' . $idPromocao . '&sucesso=comentario-publicado');
        } catch (Throwable $exception) {
            if ($isAjax) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
                return;
            }

            header('Location: index.php?action=detalhes&id=' . $idPromocao . '&erro=' . rawurlencode($exception->getMessage()));
        }
    }

    public function like(): void
    {
        $usuario = $this->usuarioLogado();
        if ($usuario === null) {
            header('Location: index.php?action=login&erro=faça-login-para-salvar-ofertas');
            return;
        }

        $idPromocao = (int) ($_GET['id'] ?? 0);
        if ($idPromocao <= 0) {
            header('Location: index.php?erro=promocao-invalida');
            return;
        }

        try {
            $connection = $this->database->connect();
            $exists = $connection->prepare('SELECT id FROM usuarios_likes_promocoes WHERE usuario_id = :usuario_id AND promocao_id = :promocao_id');
            $exists->execute([':usuario_id' => (int) $usuario['id'], ':promocao_id' => $idPromocao]);

            if ($exists->fetch() !== false) {
                $delete = $connection->prepare('DELETE FROM usuarios_likes_promocoes WHERE usuario_id = :usuario_id AND promocao_id = :promocao_id');
                $delete->execute([':usuario_id' => (int) $usuario['id'], ':promocao_id' => $idPromocao]);
            } else {
                $insert = $connection->prepare('INSERT INTO usuarios_likes_promocoes (usuario_id, promocao_id) VALUES (:usuario_id, :promocao_id)');
                $insert->execute([':usuario_id' => (int) $usuario['id'], ':promocao_id' => $idPromocao]);
            }

            header('Location: index.php');
        } catch (Throwable $exception) {
            header('Location: index.php?erro=' . rawurlencode($exception->getMessage()));
        }
    }

    public function alertarPreco(array $dados): void
    {
        $usuario = $this->usuarioLogado();
        if ($usuario === null) {
            header('Location: index.php?action=login&erro=faça-login-para-criar-alerta');
            return;
        }

        $nomeJogo = trim((string) ($dados['nome_jogo'] ?? ''));
        $precoAlvo = (float) ($dados['preco_alvo'] ?? 0);

        if ($nomeJogo === '' || $precoAlvo <= 0) {
            header('Location: index.php?erro=insira-nome-do-jogo-e-preco-alvo');
            return;
        }

        try {
            $insert = $this->database->connect()->prepare('INSERT INTO alertas_precos (usuario_id, nome_jogo, preco_alvo) VALUES (:usuario_id, :nome_jogo, :preco_alvo)');
            $insert->execute([
                ':usuario_id' => (int) $usuario['id'],
                ':nome_jogo' => $nomeJogo,
                ':preco_alvo' => $precoAlvo,
            ]);
            header('Location: index.php?sucesso=alerta-cadastrado');
        } catch (Throwable $exception) {
            header('Location: index.php?erro=' . rawurlencode($exception->getMessage()));
        }
    }

    public function logout(): void
    {
        unset($_SESSION['usuario']);
        header('Location: index.php');
    }

    private function usuarioLogado(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    private function listarUsuarios(): array
    {
        $statement = $this->database->connect()->query('SELECT id, nome, email, is_admin, criado_em FROM usuarios ORDER BY nome ASC');
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listarPromocoesPendentes(): array
    {
        $statement = $this->database->connect()->prepare('SELECT p.*, u.nome AS usuario_nome FROM promocoes p LEFT JOIN usuarios u ON u.id = p.usuario_id WHERE p.status = :status ORDER BY p.criado_em DESC');
        $statement->execute([':status' => 'pendente']);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listarPromocoesAprovadas(): array
    {
        $statement = $this->database->connect()->prepare('SELECT p.*, u.nome AS usuario_nome FROM promocoes p LEFT JOIN usuarios u ON u.id = p.usuario_id WHERE p.status = :status ORDER BY p.criado_em DESC');
        $statement->execute([':status' => 'aprovada']);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obterFiltros(): array
    {
        return [
            'plataforma' => (string) ($_GET['plataforma'] ?? ''),
            'faixa_preco' => (string) ($_GET['faixa_preco'] ?? ''),
            'tipo_midia' => (string) ($_GET['tipo_midia'] ?? ''),
            'status' => (string) ($_GET['status'] ?? ''),
            'pesquisa' => trim((string) ($_GET['pesquisa'] ?? '')),
        ];
    }

    private function listarPromocoes(array $filtros): array
    {
        $sql = 'SELECT p.*, (SELECT COUNT(*) FROM usuarios_likes_promocoes ulp WHERE ulp.promocao_id = p.id) AS likes FROM promocoes p WHERE p.status = :status';
        $params = [':status' => 'aprovada'];
        $where = [];

        if ($filtros['plataforma'] !== '') {
            $where[] = 'p.plataforma = :plataforma';
            $params[':plataforma'] = $filtros['plataforma'];
        }

        if ($filtros['tipo_midia'] !== '') {
            $where[] = 'p.tipo_midia = :tipo_midia';
            $params[':tipo_midia'] = $filtros['tipo_midia'];
        }

        if ($filtros['status'] !== '') {
            $where[] = 'p.status = :status_promocao';
            $params[':status_promocao'] = $filtros['status'];
        }

        if ($filtros['pesquisa'] !== '') {
            $where[] = 'p.titulo LIKE :pesquisa';
            $params[':pesquisa'] = '%' . $filtros['pesquisa'] . '%';
        }

        if ($filtros['faixa_preco'] !== '') {
            if ($filtros['faixa_preco'] === 'gratis') {
                $where[] = 'p.preco_promocional = 0';
            } elseif ($filtros['faixa_preco'] === 'ate_20') {
                $where[] = 'p.preco_promocional <= 20';
            } elseif ($filtros['faixa_preco'] === 'ate_50') {
                $where[] = 'p.preco_promocional <= 50';
            }
        }

        if ($where !== []) {
            $sql .= ' AND ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY p.criado_em DESC';

        $statement = $this->database->connect()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listarPromocoesSalvas(int $usuarioId): array
    {
        $sql = 'SELECT p.* FROM promocoes p INNER JOIN usuarios_likes_promocoes ulp ON ulp.promocao_id = p.id WHERE ulp.usuario_id = :usuario_id ORDER BY p.criado_em DESC';
        $statement = $this->database->connect()->prepare($sql);
        $statement->execute([':usuario_id' => $usuarioId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obterPromocaoDetalhada(int $id): ?array
    {
        $statement = $this->database->connect()->prepare('SELECT p.*, (SELECT COUNT(*) FROM usuarios_likes_promocoes ulp WHERE ulp.promocao_id = p.id) AS likes FROM promocoes p WHERE p.id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    private function listarComentariosDoBanco(int $promocaoId): array
    {
        $sql = 'SELECT c.*, u.nome AS nome_usuario FROM comentarios c INNER JOIN usuarios u ON u.id = c.usuario_id WHERE c.promocao_id = :promocao_id ORDER BY c.criado_em ASC';
        $statement = $this->database->connect()->prepare($sql);
        $statement->execute([':promocao_id' => $promocaoId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
