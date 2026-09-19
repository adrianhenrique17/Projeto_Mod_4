<?php
declare(strict_types=1);

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$sucesso = $_GET['sucesso'] ?? null;
$erro = $_GET['erro'] ?? ($mensagem ?? null);
$modo = $modo ?? 'home';
$usuario = $usuario ?? null;
$promocoes = $promocoes ?? [];
$comentarios = $comentarios ?? [];
$promocao = $promocao ?? null;
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oferta Zero | Promoções gamer</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../../assets/css/style.css') ?>">
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php"><span>OZ</span> Oferta Zero</a>
    <nav class="main-nav">
        <a href="index.php">Home</a>
        <?php if ($usuario !== null): ?>
            <?php if (!empty($usuario['is_admin'])): ?>
                <a href="index.php?action=moderacao">Painel Admin</a>
            <?php endif; ?>
            <a href="index.php?action=minhas-salvas">Ofertas salvas</a>
            <a href="index.php?action=perfil">Perfil</a>
            <a href="index.php?action=logout">Sair</a>
        <?php else: ?>
            <a href="index.php?action=login">Entrar</a>
        <?php endif; ?>
        <button class="hotbar-button" type="button" onclick="window.location.href='index.php?action=<?= $usuario !== null ? 'novo' : 'login' ?>'">Postar promoção</button>
    </nav>
</header>

<?php if ($sucesso !== null): ?>
    <div class="notice success"><?= $escape($sucesso) ?></div>
<?php endif; ?>
<?php if ($erro !== null): ?>
    <div class="notice error"><?= $escape($erro) ?></div>
<?php endif; ?>

<?php if ($modo === 'login'): ?>
    <main class="auth-shell">
        <section class="auth-box">
            <div class="auth-header">
                <p class="eyebrow">Acesso</p>
                <h1>Entre na comunidade</h1>
            </div>
            <div class="auth-grid">
                <form class="auth-form" action="index.php?action=entrar" method="post">
                    <h2>Login</h2>
                    <label>E-mail<input type="email" name="email" required></label>
                    <label>Senha<input type="password" name="senha" required></label>
                    <button type="submit">Entrar</button>
                </form>
                <form class="auth-form" action="index.php?action=registrar" method="post">
                    <h2>Cadastro</h2>
                    <label>Nome<input type="text" name="nome" required maxlength="120"></label>
                    <label>E-mail<input type="email" name="email" required maxlength="180"></label>
                    <label>Senha<input type="password" name="senha" required minlength="6"></label>
                    <button type="submit">Criar conta</button>
                </form>
            </div>
        </section>
    </main>
<?php elseif ($modo === 'perfil'): ?>
    <main class="layout single-form">
        <section class="submit-panel wide-panel">
            <div class="panel-heading"><span class="step">02</span><h2>Perfil</h2></div>
            <form action="index.php?action=atualizar-perfil" method="post" enctype="multipart/form-data">
                <div class="profile-avatar-preview">
                    <?php $fotoPerfil = (string) ($usuario['foto_perfil'] ?? ''); ?>
                    <?php if ($fotoPerfil !== ''): ?>
                        <img src="<?= $escape($fotoPerfil) ?>" alt="Foto de perfil" class="profile-avatar-image">
                    <?php else: ?>
                        <div class="profile-avatar-placeholder"><?= $escape(substr((string) ($usuario['nome'] ?? 'U'), 0, 1)) ?></div>
                    <?php endif; ?>
                </div>

                <label>Nome <input name="nome" value="<?= $escape((string) ($usuario['nome'] ?? '')) ?>" required maxlength="120"></label>

                <div class="fields-two">
                    <label>Foto de perfil
                        <input type="file" name="foto_perfil" accept="image/png,image/jpeg,image/webp">
                    </label>
                    <label>&nbsp;</label>
                </div>

                <button type="submit">Salvar perfil</button>
            </form>
        </section>
    </main>
<?php elseif ($modo === 'novo'): ?>
    <main class="layout single-form">
        <section class="submit-panel wide-panel">
            <div class="panel-heading"><span class="step">01</span><h2>Adicionar oferta</h2></div>
            <form action="index.php?action=store" method="post" enctype="multipart/form-data">
                <label>Título do jogo <input name="titulo" required maxlength="160" placeholder="Ex.: Hades II"></label>
                <div class="fields-two">
                    <label>Plataforma
                        <select name="plataforma">
                            <option>PC</option>
                            <option>PS5</option>
                            <option>Xbox</option>
                            <option>Switch</option>
                        </select>
                    </label>
                    <label>Tipo de mídia
                        <select name="tipo_midia">
                            <option value="digital">Digital</option>
                            <option value="fisica">Física</option>
                        </select>
                    </label>
                </div>
                <div class="fields-two">
                    <label>Preço original <input type="number" name="preco_original" min="0.01" step="0.01" required placeholder="199.90"></label>
                    <label>Preço promocional <input type="number" name="preco_promocional" min="0.01" step="0.01" required placeholder="99.90"></label>
                </div>
                <div class="fields-two">
                    <label>Imagem da capa <input type="file" name="imagem_capa" accept="image/png,image/jpeg,image/webp" capture="environment"></label>
                    <label>Link da loja <input type="url" name="url" placeholder="https://..."></label>
                </div>
                <label class="checkbox-line"><input type="checkbox" name="is_prevenda" value="1"> É pré-venda</label>
                <button type="submit">Enviar para fila <span>↗</span></button>
            </form>
        </section>
    </main>
<?php elseif ($modo === 'moderacao'): ?>
    <main class="layout single-form">
        <section class="submit-panel wide-panel admin-shell">
            <div class="panel-heading"><span class="step">MOD</span><h2>Painel de moderação</h2></div>
            <p class="helper-text">Acesso restrito ao administrador. Usuário especial: admin@ofertazero.com / Mod@2026</p>

            <div class="admin-layout">
                <section id="mod-ofertas" class="admin-panel admin-column ofertas-column">
                    <div class="admin-box">
                        <div class="column-header">
                            <h3>Ofertas</h3>
                            <form action="index.php?action=moderacao" method="get" class="mini-search-form">
                                <input type="text" name="pesquisa_oferta" value="<?= $escape($_GET['pesquisa_oferta'] ?? '') ?>" placeholder="Buscar oferta">
                                <button type="submit">Buscar</button>
                            </form>
                        </div>

                        <div class="admin-box-subsection">
                            <h4>Promoções pendentes</h4>
                            <?php if (empty($promocoesPendentes)): ?>
                                <p class="empty-state small">Nenhuma promoção pendente.</p>
                            <?php else: ?>
                                <?php foreach ($promocoesPendentes as $promocaoItem): ?>
                                    <div class="moderation-item">
                                        <strong><?= $escape($promocaoItem['titulo']) ?></strong>
                                        <p><?= $escape($promocaoItem['plataforma']) ?> · R$ <?= number_format((float) $promocaoItem['preco_promocional'], 2, ',', '.') ?></p>
                                        <div class="actions-row">
                                            <form action="index.php?action=aprovar-promocao" method="post" class="inline-form">
                                                <input type="hidden" name="id" value="<?= (int) $promocaoItem['id'] ?>">
                                                <button type="submit">Aprovar</button>
                                            </form>
                                            <form action="index.php?action=rejeitar-promocao" method="post" class="inline-form">
                                                <input type="hidden" name="id" value="<?= (int) $promocaoItem['id'] ?>">
                                                <button type="submit" class="secondary">Rejeitar</button>
                                            </form>
                                            <form action="index.php?action=excluir-promocao" method="post" class="inline-form">
                                                <input type="hidden" name="id" value="<?= (int) $promocaoItem['id'] ?>">
                                                <button type="submit" class="danger">Excluir</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="admin-box-subsection">
                            <h4>Promoções aprovadas</h4>
                            <?php if (empty($promocoesAprovadas)): ?>
                                <p class="empty-state small">Nenhuma promoção aprovada.</p>
                            <?php else: ?>
                                <?php foreach ($promocoesAprovadas as $promocaoItem): ?>
                                    <div class="moderation-item">
                                        <strong><?= $escape($promocaoItem['titulo']) ?></strong>
                                        <p><?= $escape($promocaoItem['plataforma']) ?> · <?= $escape($promocaoItem['status']) ?></p>
                                        <form action="index.php?action=salvar-promocao" method="post" class="compact-form">
                                            <input type="hidden" name="id" value="<?= (int) $promocaoItem['id'] ?>">
                                            <label>Título<input type="text" name="titulo" value="<?= $escape($promocaoItem['titulo']) ?>" required></label>
                                            <label>Preço original<input type="number" name="preco_original" min="0.01" step="0.01" value="<?= (float) $promocaoItem['preco_original'] ?>" required></label>
                                            <label>Preço promocional<input type="number" name="preco_promocional" min="0.01" step="0.01" value="<?= (float) $promocaoItem['preco_promocional'] ?>" required></label>
                                            <label>URL<input type="url" name="url" value="<?= $escape($promocaoItem['url']) ?>" required></label>
                                            <label>Plataforma
                                                <select name="plataforma">
                                                    <option value="PC" <?= ($promocaoItem['plataforma'] === 'PC') ? 'selected' : '' ?>>PC</option>
                                                    <option value="PS5" <?= ($promocaoItem['plataforma'] === 'PS5') ? 'selected' : '' ?>>PS5</option>
                                                    <option value="Xbox" <?= ($promocaoItem['plataforma'] === 'Xbox') ? 'selected' : '' ?>>Xbox</option>
                                                    <option value="Switch" <?= ($promocaoItem['plataforma'] === 'Switch') ? 'selected' : '' ?>>Switch</option>
                                                </select>
                                            </label>
                                            <label>Mídia
                                                <select name="tipo_midia">
                                                    <option value="digital" <?= ($promocaoItem['tipo_midia'] === 'digital') ? 'selected' : '' ?>>Digital</option>
                                                    <option value="fisica" <?= ($promocaoItem['tipo_midia'] === 'fisica') ? 'selected' : '' ?>>Física</option>
                                                </select>
                                            </label>
                                            <button type="submit">Salvar edição</button>
                                        </form>

                                        <form action="index.php?action=excluir-promocao" method="post" class="inline-form">
                                            <input type="hidden" name="id" value="<?= (int) $promocaoItem['id'] ?>">
                                            <button type="submit" class="danger">Excluir promoção</button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <section id="mod-usuarios" class="admin-panel admin-column usuarios-column">
                    <div class="admin-box">
                        <div class="column-header">
                            <h3>Usuários</h3>
                            <form action="index.php?action=moderacao" method="get" class="mini-search-form">
                                <input type="text" name="pesquisa_usuario" value="<?= $escape($_GET['pesquisa_usuario'] ?? '') ?>" placeholder="Buscar usuário">
                                <button type="submit">Buscar</button>
                            </form>
                        </div>

                        <?php if (empty($usuarios)): ?>
                            <p class="empty-state small">Nenhum usuário cadastrado.</p>
                        <?php else: ?>
                            <?php foreach ($usuarios as $usuarioItem): ?>
                                <div class="moderation-item user-item">
                                    <div class="user-head">
                                        <div>
                                            <strong><?= $escape($usuarioItem['nome']) ?></strong>
                                            <p><?= $escape($usuarioItem['email']) ?> · <?= !empty($usuarioItem['is_admin']) ? 'Admin' : 'Usuário' ?></p>
                                        </div>
                                        <?php if ((int) $usuarioItem['id'] !== (int) ($usuario['id'] ?? 0)): ?>
                                            <form action="index.php?action=excluir-usuario" method="post" class="inline-form">
                                                <input type="hidden" name="id" value="<?= (int) $usuarioItem['id'] ?>">
                                                <button type="submit" class="danger">Excluir</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>

                                    <form action="index.php?action=salvar-usuario" method="post" class="compact-form">
                                        <input type="hidden" name="id" value="<?= (int) $usuarioItem['id'] ?>">
                                        <label>Nome<input type="text" name="nome" value="<?= $escape($usuarioItem['nome']) ?>" required></label>
                                        <label>E-mail<input type="email" name="email" value="<?= $escape($usuarioItem['email']) ?>" required></label>
                                        <label>Nova senha<input type="password" name="senha" placeholder="Opcional"></label>
                                        <button type="submit">Salvar dados</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </section>
    </main>
<?php elseif ($modo === 'detalhes' && $promocao !== null): ?>
    <main class="detail-shell">
        <article class="detail-card">
            <div class="detail-cover" style="background-image: url('<?= $escape($promocao['imagem_capa'] !== '' ? $promocao['imagem_capa'] : 'https://images.unsplash.com/photo-1542751371-adc38448a05e') ?>');">
                <span class="platform"><?= $escape($promocao['plataforma']) ?></span>
            </div>
            <div class="detail-content">
                <div class="detail-head">
                    <div>
                        <p class="eyebrow">Oferta em destaque</p>
                        <h1><?= $escape($promocao['titulo']) ?></h1>
                    </div>
                    <a class="cta-link" href="<?= $escape($promocao['url']) ?>" target="_blank" rel="noopener">Ver loja</a>
                </div>
                <div class="prices">
                    <del>R$ <?= number_format((float) $promocao['preco_original'], 2, ',', '.') ?></del>
                    <strong>R$ <?= number_format((float) $promocao['preco_promocional'], 2, ',', '.') ?></strong>
                </div>
                <p class="meta">Mídia: <?= $escape($promocao['tipo_midia']) ?> · Pré-venda: <?= $promocao['is_prevenda'] ? 'Sim' : 'Não' ?> · Likes: <?= (int) $promocao['likes'] ?></p>
                <div class="actions-row">
                    <a class="mini-btn" href="index.php?action=like&id=<?= (int) $promocao['id'] ?>">Salvar oferta</a>
                    <a class="mini-btn alt" href="index.php">Voltar</a>
                </div>
                <section class="comments-box">
                    <h2>Comentários da comunidade</h2>
                    <?php if ($comentarios === []): ?>
                        <p class="empty-state small">Ainda não há comentários. Seja o primeiro a opinar.</p>
                    <?php else: ?>
                        <ul class="comment-list">
                            <?php foreach ($comentarios as $comentario): ?>
                                <li>
                                    <strong><?= $escape($comentario['nome_usuario']) ?></strong>
                                    <p><?= $escape($comentario['texto']) ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if ($usuario !== null): ?>
                        <form id="comment-form" action="index.php?action=comentar" method="post" class="comment-form">
                            <input type="hidden" name="promocao_id" value="<?= (int) $promocao['id'] ?>">
                            <textarea name="texto" required maxlength="500" placeholder="Vale a pena comprar por esse preço?"></textarea>
                            <button type="submit">Publicar comentário</button>
                        </form>
                    <?php else: ?>
                        <div class="login-gate">
                            <a href="index.php?action=login&redirect=detalhes&id=<?= (int) $promocao['id'] ?>">Faça login para participar da discussão</a>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </article>
    </main>
<?php elseif ($modo === 'minhas-salvas'): ?>
    <main class="layout">
        <section class="offers full-width">
            <div class="section-heading"><div><p class="eyebrow">Minha lista</p><h2>Ofertas salvas</h2></div></div>
            <?php if ($promocoes === []): ?>
                <div class="empty-state">Você ainda não salvou nenhuma oferta.</div>
            <?php else: ?>
                <div class="offer-grid">
                    <?php foreach ($promocoes as $promocao): ?>
                        <article class="offer-card">
                            <div class="card-top"><span class="platform"><?= $escape($promocao['plataforma']) ?></span><span class="approved">salva</span></div>
                            <h3><?= $escape($promocao['titulo']) ?></h3>
                            <div class="prices"><del>R$ <?= number_format((float) $promocao['preco_original'], 2, ',', '.') ?></del><strong>R$ <?= number_format((float) $promocao['preco_promocional'], 2, ',', '.') ?></strong></div>
                            <a class="visit" href="index.php?action=detalhes&id=<?= (int) $promocao['id'] ?>">Abrir detalhes ↗</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
<?php else: ?>
    <main class="layout home-layout">
        <section class="offers full-width">
            <div class="search-toolbar">
                <form action="index.php" method="get" class="search-form">
                    <label class="sr-only" for="busca-jogo">Buscar jogo</label>
                    <input id="busca-jogo" type="text" name="pesquisa" value="<?= $escape($_GET['pesquisa'] ?? '') ?>" placeholder="Ex.: Hades">
                    <button type="submit" class="search-button">Buscar</button>
                    <button type="button" class="filter-toggle" data-filter-toggle>Filtros</button>
                </form>

                <div class="filter-drawer" id="filterDrawer">
                    <form action="index.php" method="get" class="filter-form">
                        <div class="filter-grid">
                            <label>Plataforma
                                <select name="plataforma">
                                    <option value="">Todas</option>
                                    <option value="PC" <?= (($_GET['plataforma'] ?? '') === 'PC') ? 'selected' : '' ?>>PC</option>
                                    <option value="PS5" <?= (($_GET['plataforma'] ?? '') === 'PS5') ? 'selected' : '' ?>>PS5</option>
                                    <option value="Xbox" <?= (($_GET['plataforma'] ?? '') === 'Xbox') ? 'selected' : '' ?>>Xbox</option>
                                    <option value="Switch" <?= (($_GET['plataforma'] ?? '') === 'Switch') ? 'selected' : '' ?>>Switch</option>
                                </select>
                            </label>
                            <label>Faixa de preço
                                <select name="faixa_preco">
                                    <option value="">Qualquer</option>
                                    <option value="gratis" <?= (($_GET['faixa_preco'] ?? '') === 'gratis') ? 'selected' : '' ?>>Grátis</option>
                                    <option value="ate_20" <?= (($_GET['faixa_preco'] ?? '') === 'ate_20') ? 'selected' : '' ?>>Até R$ 20</option>
                                    <option value="ate_50" <?= (($_GET['faixa_preco'] ?? '') === 'ate_50') ? 'selected' : '' ?>>Até R$ 50</option>
                                </select>
                            </label>
                            <label>Mídia
                                <select name="tipo_midia">
                                    <option value="">Todas</option>
                                    <option value="digital" <?= (($_GET['tipo_midia'] ?? '') === 'digital') ? 'selected' : '' ?>>Digital</option>
                                    <option value="fisica" <?= (($_GET['tipo_midia'] ?? '') === 'fisica') ? 'selected' : '' ?>>Física</option>
                                </select>
                            </label>
                            <label>Status
                                <select name="status">
                                    <option value="">Todos</option>
                                    <option value="aprovada" <?= (($_GET['status'] ?? '') === 'aprovada') ? 'selected' : '' ?>>Aprovada</option>
                                    <option value="pendente" <?= (($_GET['status'] ?? '') === 'pendente') ? 'selected' : '' ?>>Pendente</option>
                                </select>
                            </label>
                        </div>
                        <button type="submit" class="primary-filter-button">Aplicar filtros</button>
                    </form>

                    <form action="index.php?action=alertar-preco" method="post" class="filter-form margin-top">
                        <h3>Alerta de preço</h3>
                        <?php if ($usuario === null): ?>
                            <p class="helper-text">Faça login para receber alertas.</p>
                        <?php else: ?>
                            <label>Nome do jogo<input type="text" name="nome_jogo" required></label>
                            <label>Preço alvo<input type="number" name="preco_alvo" min="0.01" step="0.01" required></label>
                            <button type="submit">Salvar alerta</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="section-heading">
                <div><p class="eyebrow">Ofertas aprovadas</p><h2>Jogos em destaque</h2></div>
                <span class="count"><?= count($promocoes) ?> ofertas</span>
            </div>
            <?php if ($promocoes === []): ?>
                <div class="empty-state">Nenhuma oferta corresponde aos filtros atuais.</div>
            <?php else: ?>
                <div class="offer-grid">
                    <?php foreach ($promocoes as $promocaoItem): ?>
                        <article class="offer-card">
                            <div class="card-top">
                                <span class="platform"><?= $escape($promocaoItem['plataforma']) ?></span>
                                <span class="approved"><?= $escape($promocaoItem['temperatura'] ?? 'quente') ?></span>
                            </div>
                            <div class="game-cover" style="background-image: url('<?= $escape($promocaoItem['imagem_capa'] !== '' ? $promocaoItem['imagem_capa'] : 'https://images.unsplash.com/photo-1542751371-adc38448a05e') ?>');"></div>
                            <h3><?= $escape($promocaoItem['titulo']) ?></h3>
                            <div class="prices">
                                <del>R$ <?= number_format((float) $promocaoItem['preco_original'], 2, ',', '.') ?></del>
                                <strong>R$ <?= number_format((float) $promocaoItem['preco_promocional'], 2, ',', '.') ?></strong>
                            </div>
                            <p class="meta">Likes: <?= (int) ($promocaoItem['likes'] ?? 0) ?> · <?= $escape($promocaoItem['tipo_midia'] ?? 'digital') ?></p>
                            <div class="actions-row">
                                <a class="mini-btn" href="index.php?action=like&id=<?= (int) $promocaoItem['id'] ?>">Curtir</a>
                                <a class="visit" href="index.php?action=detalhes&id=<?= (int) $promocaoItem['id'] ?>">Detalhes ↗</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
<?php endif; ?>
<script src="assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../assets/js/app.js') ?>"></script>
<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-branding">
            <a class="brand" href="index.php"><span>OZ</span> Oferta Zero</a>
            <p>Seu guia para encontrar as melhores promoções gamer, acompanhar ofertas em alta e descobrir jogos que valem a pena.</p>
        </div>

        <div class="footer-column">
            <h3>Guia rápido</h3>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php?action=login">Entrar</a></li>
                <li><a href="index.php?action=novo">Postar promoção</a></li>
                <li><a href="index.php?action=minhas-salvas">Ofertas salvas</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h3>Sobre o site</h3>
            <ul>
                <li>Promoções gamer</li>
                <li>Filtros por plataforma</li>
                <li>Busca por nome do jogo</li>
                <li>Comunidade e comentários</li>
            </ul>
        </div>

        <div class="footer-column">
            <h3>Créditos</h3>
            <p>Desenvolvido por <strong>Adrian Silva</strong></p>
            <p>Projeto acadêmico em PHP + MVC + MySQL</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 Oferta Zero. Todos os direitos reservados.</p>
    </div>
</footer>
</body>
</html>
