CREATE DATABASE IF NOT EXISTS promocoes_gamer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE promocoes_gamer;

DROP TABLE IF EXISTS avaliacoes;
DROP TABLE IF EXISTS usuarios_likes_promocoes;
DROP TABLE IF EXISTS alertas_precos;
DROP TABLE IF EXISTS comentarios;
DROP TABLE IF EXISTS promocoes;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(500) NOT NULL DEFAULT '',
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE promocoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(160) NOT NULL,
    plataforma ENUM('PC', 'PS5', 'Xbox', 'Switch') NOT NULL,
    preco_original DECIMAL(10,2) NOT NULL,
    preco_promocional DECIMAL(10,2) NOT NULL,
    url VARCHAR(500) NOT NULL DEFAULT '',
    imagem_capa VARCHAR(500) NOT NULL DEFAULT '',
    tipo_midia ENUM('digital', 'fisica') NOT NULL DEFAULT 'digital',
    is_prevenda TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('pendente', 'aprovada', 'rejeitada') NOT NULL DEFAULT 'pendente',
    usuario_id INT UNSIGNED DEFAULT NULL,
    temperatura ENUM('quente', 'fria', 'neutra') NOT NULL DEFAULT 'neutra',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_promocoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT chk_preco_promocional CHECK (preco_promocional > 0 AND preco_promocional < preco_original)
) ENGINE=InnoDB;

CREATE TABLE comentarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    promocao_id INT UNSIGNED NOT NULL,
    texto TEXT NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comentarios_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_comentarios_promocao FOREIGN KEY (promocao_id) REFERENCES promocoes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE usuarios_likes_promocoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    promocao_id INT UNSIGNED NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_likes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_likes_promocao FOREIGN KEY (promocao_id) REFERENCES promocoes(id) ON DELETE CASCADE,
    CONSTRAINT uq_like_unico UNIQUE (usuario_id, promocao_id)
) ENGINE=InnoDB;

CREATE TABLE alertas_precos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    nome_jogo VARCHAR(160) NOT NULL,
    preco_alvo DECIMAL(10,2) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_alertas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE avaliacoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    promocao_id INT UNSIGNED NOT NULL,
    nota TINYINT UNSIGNED NOT NULL,
    comentario VARCHAR(500) NOT NULL DEFAULT '',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_nota CHECK (nota BETWEEN 1 AND 5),
    CONSTRAINT fk_avaliacoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_avaliacoes_promocao FOREIGN KEY (promocao_id) REFERENCES promocoes(id) ON DELETE CASCADE,
    CONSTRAINT uq_avaliacao_usuario_promocao UNIQUE (usuario_id, promocao_id)
) ENGINE=InnoDB;

INSERT INTO usuarios (nome, email, senha, is_admin) VALUES
('Administrador', 'admin@ofertazero.com', '$2y$10$zob62HkpslrOqChX3hCRauJDP72tDWHXxx.O6eFgI24w..8bGdgHi', 1);

INSERT INTO promocoes (titulo, plataforma, preco_original, preco_promocional, url, imagem_capa, tipo_midia, is_prevenda, status, usuario_id, temperatura) VALUES
('Hades II', 'PC', 99.90, 74.90, 'https://store.steampowered.com/', 'https://images.unsplash.com/photo-1542751371-adc38448a05e', 'digital', 0, 'aprovada', 1, 'quente'),
('The Legend of Zelda: Tears of the Kingdom', 'Switch', 359.90, 249.90, 'https://store.nintendo.com.br/', 'https://images.unsplash.com/photo-1511512578047-dfb367046420', 'digital', 0, 'aprovada', 1, 'quente'),
('Forza Horizon 5', 'Xbox', 249.90, 124.90, 'https://www.xbox.com/', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f', 'digital', 0, 'aprovada', 1, 'quente'),
('EA SPORTS FC 25', 'PS5', 299.90, 179.90, 'https://store.playstation.com/', 'https://images.unsplash.com/photo-1542751371-adc38448a05e', 'digital', 1, 'aprovada', 1, 'neutra');

INSERT INTO comentarios (usuario_id, promocao_id, texto) VALUES
(1, 1, 'Vale a pena demais para quem curte ação roguelike.'),
(1, 2, 'Está com desconto bem interessante, mas ainda acho caro para Switch.'),
(1, 3, 'Ótima oferta se a pessoa gosta de corrida aberta.');

INSERT INTO usuarios_likes_promocoes (usuario_id, promocao_id) VALUES
(1, 1),
(1, 3);

INSERT INTO alertas_precos (usuario_id, nome_jogo, preco_alvo) VALUES
(1, 'Fortnite', 49.90),
(1, 'EA FC 26', 99.90);
