<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/app/Models/Database.php';
require_once __DIR__ . '/app/Models/Usuario.php';
require_once __DIR__ . '/app/Models/Promocao.php';
require_once __DIR__ . '/app/Models/Avaliacao.php';
require_once __DIR__ . '/app/Models/Comentario.php';
require_once __DIR__ . '/app/Models/Like.php';
require_once __DIR__ . '/app/Models/AlertaPreco.php';
require_once __DIR__ . '/app/Structures/FilaCircular.php';
require_once __DIR__ . '/app/Structures/Pilha.php';
require_once __DIR__ . '/app/Controllers/PromocaoController.php';

use App\Controllers\PromocaoController;

$controller = new PromocaoController();
$action = $_GET['action'] ?? 'index';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['store', 'entrar', 'registrar', 'comentar', 'alertar-preco', 'salvar-usuario', 'salvar-promocao', 'aprovar-promocao', 'rejeitar-promocao', 'excluir-promocao', 'excluir-usuario', 'atualizar-perfil'], true)) {
    if ($action === 'store') {
        $controller->store($_POST, $_FILES);
        exit;
    }

    if ($action === 'entrar') {
        $controller->entrar($_POST);
        exit;
    }

    if ($action === 'registrar') {
        $controller->registrar($_POST);
        exit;
    }

    if ($action === 'comentar') {
        $controller->comentar($_POST);
        exit;
    }

    if ($action === 'alertar-preco') {
        $controller->alertarPreco($_POST);
        exit;
    }

    if ($action === 'salvar-usuario') {
        $controller->editarUsuario($_POST);
        exit;
    }

    if ($action === 'atualizar-perfil') {
        $controller->atualizarPerfil($_POST, $_FILES);
        exit;
    }

    if ($action === 'salvar-promocao') {
        $controller->editarPromocao($_POST);
        exit;
    }

    if ($action === 'aprovar-promocao') {
        $controller->aprovarPromocao($_POST);
        exit;
    }

    if ($action === 'rejeitar-promocao') {
        $controller->rejeitarPromocao($_POST);
        exit;
    }

    if ($action === 'excluir-promocao') {
        $controller->excluirPromocao($_POST);
        exit;
    }

    if ($action === 'excluir-usuario') {
        $controller->excluirUsuario($_POST);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'avaliar') {
    $controller->avaliar($_POST);
    exit;
}

if ($action === 'logout') {
    $controller->logout();
    exit;
}

if ($action === 'like') {
    $controller->like();
    exit;
}

if ($action === 'minhas-salvas') {
    $controller->minhasSalvas();
    exit;
}

if ($action === 'perfil') {
    $controller->perfil();
    exit;
}

if ($action === 'novo') {
    $controller->novo();
    exit;
}

if ($action === 'login') {
    $controller->login();
    exit;
}

if ($action === 'detalhes') {
    $controller->detalhes();
    exit;
}

if ($action === 'moderacao') {
    $controller->moderacao();
    exit;
}

if ($action === 'aprovar-promocao') {
    $controller->aprovarPromocao($_GET);
    exit;
}

if ($action === 'rejeitar-promocao') {
    $controller->rejeitarPromocao($_GET);
    exit;
}

if ($action === 'excluir-promocao') {
    $controller->excluirPromocao($_GET);
    exit;
}

if ($action === 'excluir-usuario') {
    $controller->excluirUsuario($_GET);
    exit;
}

$controller->{$action}();
