-- -- CREATE DATABASE IF NOT EXISTS EcoCollect
-- --   CHARACTER SET utf8mb4
-- --   COLLATE utf8mb4_unicode_ci;

-- -- USE EcoCollect;

-- -- CREATE TABLE Usuarios (
-- --     id INT PRIMARY KEY AUTO_INCREMENT,
-- --     nome VARCHAR(100) NOT NULL,
-- --     email VARCHAR(100) NOT NULL UNIQUE,
-- --     senha VARCHAR(255) NOT NULL,
-- --     telefone VARCHAR(14) NOT NULL
-- -- ) ENGINE=InnoDB;

-- -- CREATE TABLE PontosDeColeta (
-- --     id INT PRIMARY KEY AUTO_INCREMENT,
-- --     cep VARCHAR(9) NOT NULL,
-- --     status ENUM('disponivel', 'indisponivel') NOT NULL DEFAULT 'disponivel',
-- --     INDEX idx_pontos_status (status),
-- --     INDEX idx_pontos_cep (cep)
-- -- ) ENGINE=InnoDB;

-- -- CREATE TABLE Material (
-- --     id INT PRIMARY KEY AUTO_INCREMENT,
-- --     tipo ENUM('plasticos', 'metais', 'papeis', 'vidros', 'organico') NOT NULL UNIQUE,
-- --     valorUnidade DECIMAL(10,2) NOT NULL DEFAULT 1.00
-- -- ) ENGINE=InnoDB;

-- -- CREATE TABLE PontoMaterial (
-- --     id INT PRIMARY KEY AUTO_INCREMENT,
-- --     idPonto INT NOT NULL,
-- --     idMaterial INT NOT NULL,
-- --     UNIQUE KEY uk_ponto_material (idPonto, idMaterial),
-- --     INDEX idx_pm_material (idMaterial),
-- --     CONSTRAINT fk_pm_ponto FOREIGN KEY (idPonto) REFERENCES PontosDeColeta(id) ON DELETE CASCADE,
-- --     CONSTRAINT fk_pm_material FOREIGN KEY (idMaterial) REFERENCES Material(id) ON DELETE CASCADE
-- -- ) ENGINE=InnoDB;

-- -- CREATE TABLE Coleta (
-- --     id INT PRIMARY KEY AUTO_INCREMENT,
-- --     idPonto INT NOT NULL,
-- --     idMaterial INT NOT NULL,
-- --     idUsuario INT NOT NULL,
-- --     dataColeta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
-- --     quantidadeColetada DECIMAL(10,2) NOT NULL,
-- --     pontuacao DECIMAL(10,2) NOT NULL,
-- --     INDEX idx_coleta_usuario_data (idUsuario, dataColeta),
-- --     INDEX idx_coleta_ponto_data (idPonto, dataColeta),
-- --     INDEX idx_coleta_material (idMaterial),
-- --     CONSTRAINT fk_coleta_ponto FOREIGN KEY (idPonto) REFERENCES PontosDeColeta(id),
-- --     CONSTRAINT fk_coleta_material FOREIGN KEY (idMaterial) REFERENCES Material(id),
-- --     CONSTRAINT fk_coleta_usuario FOREIGN KEY (idUsuario) REFERENCES Usuarios(id)
-- -- ) ENGINE=InnoDB;

-- -- INSERT INTO Material (tipo, valorUnidade) VALUES 
-- -- ('plasticos', 1.00),
-- -- ('metais', 5.00),
-- -- ('papeis', 1.00),
-- -- ('vidros', 3.00),
-- -- ('organico', 0.50);

-- -- CREATE OR REPLACE VIEW ViewPontosDeColeta AS
-- -- SELECT
-- --     p.id,
-- --     p.cep,
-- --     p.status,
-- --     COALESCE(
-- --         GROUP_CONCAT(
-- --             m.tipo
-- --             ORDER BY m.tipo
-- --             SEPARATOR ', '
-- --         ),
-- --         'Nenhum material cadastrado'
-- --     ) AS materiais
-- -- FROM PontosDeColeta p
-- -- LEFT JOIN PontoMaterial pm ON p.id = pm.idPonto
-- -- LEFT JOIN Material m ON pm.idMaterial = m.id
-- -- GROUP BY p.id, p.cep, p.status;

-- -- CREATE OR REPLACE VIEW ViewMateriaisPorPonto AS
-- -- SELECT m.tipo 
-- -- FROM Material m
-- -- INNER JOIN PontoMaterial pm ON m.id = pm.idMaterial
-- -- WHERE pm.idPonto = 1;

-- -- CREATE OR REPLACE VIEW ViewMateriaisDoPonto AS
-- -- SELECT 
-- --     pm.idPonto,
-- --     m.id AS idMaterial,
-- --     m.tipo AS material,
-- --     m.valorUnidade
-- -- FROM PontoMaterial pm
-- -- INNER JOIN Material m ON pm.idMaterial = m.id;

-- -- CREATE OR REPLACE VIEW ViewPontosFiltro AS
-- -- SELECT
-- --     p.id AS idPonto,
-- --     p.cep,
-- --     p.status,
-- --     pm.idMaterial
-- -- FROM PontosDeColeta p
-- -- LEFT JOIN PontoMaterial pm ON p.id = pm.idPonto;


-- CREATE DATABASE IF NOT EXISTS EcoCollect
--   CHARACTER SET utf8mb4
--   COLLATE utf8mb4_unicode_ci;

-- USE EcoCollect;

-- CREATE TABLE Usuarios (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     nome VARCHAR(100) NOT NULL,
--     email VARCHAR(100) NOT NULL UNIQUE,
--     senha VARCHAR(255) NOT NULL,
--     telefone VARCHAR(14) NOT NULL,
--     saldoPontos DECIMAL(10,2) NOT NULL DEFAULT 0.00
-- ) ENGINE=InnoDB;

-- CREATE TABLE PontosDeColeta (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     cep VARCHAR(9) NOT NULL,
--     status ENUM('disponivel', 'indisponivel') NOT NULL DEFAULT 'disponivel',
--     INDEX idx_pontos_status (status),
--     INDEX idx_pontos_cep (cep)
-- ) ENGINE=InnoDB;

-- CREATE TABLE Material (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     tipo ENUM('plasticos', 'metais', 'papeis', 'vidros', 'organico') NOT NULL UNIQUE,
--     valorUnidade DECIMAL(10,2) NOT NULL DEFAULT 1.00
-- ) ENGINE=InnoDB;

-- CREATE TABLE PontoMaterial (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     idPonto INT NOT NULL,
--     idMaterial INT NOT NULL,
--     UNIQUE KEY uk_ponto_material (idPonto, idMaterial),
--     INDEX idx_pm_material (idMaterial),
--     CONSTRAINT fk_pm_ponto FOREIGN KEY (idPonto) REFERENCES PontosDeColeta(id) ON DELETE CASCADE,
--     CONSTRAINT fk_pm_material FOREIGN KEY (idMaterial) REFERENCES Material(id) ON DELETE CASCADE
-- ) ENGINE=InnoDB;

-- CREATE TABLE Coleta (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     idPonto INT NOT NULL,
--     idMaterial INT NOT NULL,
--     idUsuario INT NOT NULL,
--     dataColeta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
--     quantidadeColetada DECIMAL(10,2) NOT NULL,
--     pontuacao DECIMAL(10,2) NOT NULL,
--     INDEX idx_coleta_usuario_data (idUsuario, dataColeta),
--     INDEX idx_coleta_ponto_data (idPonto, dataColeta),
--     INDEX idx_coleta_material (idMaterial),
--     CONSTRAINT fk_coleta_ponto FOREIGN KEY (idPonto) REFERENCES PontosDeColeta(id) ON DELETE CASCADE,
--     CONSTRAINT fk_coleta_material FOREIGN KEY (idMaterial) REFERENCES Material(id),
--     CONSTRAINT fk_coleta_usuario FOREIGN KEY (idUsuario) REFERENCES Usuarios(id) ON DELETE CASCADE
-- ) ENGINE=InnoDB;

-- -- ==========================================
-- -- NOVAS TABELAS: LOJA DE BRINDES
-- -- ==========================================

-- CREATE TABLE Brinde (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     nome VARCHAR(100) NOT NULL,
--     descricao TEXT,
--     custoPontos DECIMAL(10,2) NOT NULL,
--     estoque INT NOT NULL DEFAULT 0,
--     imagem VARCHAR(255) DEFAULT NULL,
--     ativo TINYINT(1) NOT NULL DEFAULT 1,
--     INDEX idx_brinde_ativo (ativo)
-- ) ENGINE=InnoDB;

-- CREATE TABLE ResgateBrinde (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     idUsuario INT NOT NULL,
--     idBrinde INT NOT NULL,
--     pontosGastos DECIMAL(10,2) NOT NULL,
--     codigoResgate VARCHAR(20) NOT NULL UNIQUE,
--     status ENUM('pendente', 'retirado', 'cancelado') NOT NULL DEFAULT 'pendente',
--     dataResgate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
--     INDEX idx_resgate_usuario (idUsuario, dataResgate),
--     CONSTRAINT fk_resgate_usuario FOREIGN KEY (idUsuario) REFERENCES Usuarios(id) ON DELETE CASCADE,
--     CONSTRAINT fk_resgate_brinde FOREIGN KEY (idBrinde) REFERENCES Brinde(id) ON DELETE RESTRICT
-- ) ENGINE=InnoDB;

-- -- ==========================================
-- -- NOVAS TABELAS: CAMPANHAS DE COLETA
-- -- ==========================================

-- CREATE TABLE Campanha (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     idUsuario INT NOT NULL,
--     idPonto INT DEFAULT NULL,
--     titulo VARCHAR(120) NOT NULL,
--     descricao TEXT NOT NULL,
--     dataInicio DATE NOT NULL,
--     dataFim DATE NOT NULL,
--     criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
--     INDEX idx_campanha_datas (dataInicio, dataFim),
--     CONSTRAINT fk_campanha_usuario FOREIGN KEY (idUsuario) REFERENCES Usuarios(id) ON DELETE CASCADE,
--     CONSTRAINT fk_campanha_ponto FOREIGN KEY (idPonto) REFERENCES PontosDeColeta(id) ON DELETE SET NULL
-- ) ENGINE=InnoDB;

-- CREATE TABLE CampanhaMaterial (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     idCampanha INT NOT NULL,
--     idMaterial INT NOT NULL,
--     UNIQUE KEY uk_campanha_material (idCampanha, idMaterial),
--     INDEX idx_cm_material (idMaterial),
--     CONSTRAINT fk_cm_campanha FOREIGN KEY (idCampanha) REFERENCES Campanha(id) ON DELETE CASCADE,
--     CONSTRAINT fk_cm_material FOREIGN KEY (idMaterial) REFERENCES Material(id) ON DELETE CASCADE
-- ) ENGINE=InnoDB;

-- -- ==========================================
-- -- DADOS INICIAIS
-- -- ==========================================

-- INSERT INTO Material (tipo, valorUnidade) VALUES 
-- ('plasticos', 1.00),
-- ('metais', 5.00),
-- ('papeis', 1.00),
-- ('vidros', 3.00),
-- ('organico', 0.50);

-- INSERT INTO Brinde (nome, descricao, custoPontos, estoque) VALUES
-- ('Ecobag EcoCollect', 'Sacola retornável de algodão cru reforçado.', 50.00, 30),
-- ('Garrafa Térmica Inox 500ml', 'Garrafa reutilizável para reduzir o consumo de plástico.', 120.00, 15),
-- ('Kit Canudos de Inox + Escova', 'Conjunto sustentável com estojo de transporte.', 35.00, 40),
-- ('Muda de Planta Nativa', 'Muda pronta para plantio com adubo orgânico.', 25.00, 50);

-- -- ==========================================
-- -- VIEWS EXISTENTES E NOVAS
-- -- ==========================================

-- CREATE OR REPLACE VIEW ViewPontosDeColeta AS
-- SELECT
--     p.id,
--     p.cep,
--     p.status,
--     COALESCE(
--         GROUP_CONCAT(
--             m.tipo
--             ORDER BY m.tipo
--             SEPARATOR ', '
--         ),
--         'Nenhum material cadastrado'
--     ) AS materiais
-- FROM PontosDeColeta p
-- LEFT JOIN PontoMaterial pm ON p.id = pm.idPonto
-- LEFT JOIN Material m ON pm.idMaterial = m.id
-- GROUP BY p.id, p.cep, p.status;

-- CREATE OR REPLACE VIEW ViewMateriaisPorPonto AS
-- SELECT m.tipo 
-- FROM Material m
-- INNER JOIN PontoMaterial pm ON m.id = pm.idMaterial
-- WHERE pm.idPonto = 1;

-- CREATE OR REPLACE VIEW ViewMateriaisDoPonto AS
-- SELECT 
--     pm.idPonto,
--     m.id AS idMaterial,
--     m.tipo AS material,
--     m.valorUnidade
-- FROM PontoMaterial pm
-- INNER JOIN Material m ON pm.idMaterial = m.id;

-- CREATE OR REPLACE VIEW ViewPontosFiltro AS
-- SELECT
--     p.id AS idPonto,
--     p.cep,
--     p.status,
--     pm.idMaterial
-- FROM PontosDeColeta p
-- LEFT JOIN PontoMaterial pm ON p.id = pm.idPonto;

-- CREATE OR REPLACE VIEW ViewRankingUsuarios AS
-- SELECT
--     u.id,
--     u.nome,
--     u.saldoPontos,
--     COUNT(c.id) AS totalColetas,
--     COALESCE(SUM(c.pontuacao), 0.00) AS totalPontos
-- FROM Usuarios u
-- LEFT JOIN Coleta c ON u.id = c.idUsuario
-- GROUP BY u.id, u.nome, u.saldoPontos
-- ORDER BY totalPontos DESC, totalColetas DESC, u.nome ASC;

-- CREATE OR REPLACE VIEW ViewCampanhas AS
-- SELECT
--     camp.id,
--     camp.idUsuario,
--     u.nome AS organizador,
--     camp.idPonto,
--     p.cep AS cepPonto,
--     camp.titulo,
--     camp.descricao,
--     camp.dataInicio,
--     camp.dataFim,
--     camp.criadoEm,
--     COALESCE(
--         GROUP_CONCAT(
--             m.tipo
--             ORDER BY m.tipo
--             SEPARATOR ', '
--         ),
--         'Todos os materiais'
--     ) AS materiaisAlvo
-- FROM Campanha camp
-- INNER JOIN Usuarios u ON camp.idUsuario = u.id
-- LEFT JOIN PontosDeColeta p ON camp.idPonto = p.id
-- LEFT JOIN CampanhaMaterial cm ON camp.id = cm.idCampanha
-- LEFT JOIN Material m ON cm.idMaterial = m.id
-- GROUP BY
--     camp.id,
--     camp.idUsuario,
--     u.nome,
--     camp.idPonto,
--     p.cep,
--     camp.titulo,
--     camp.descricao,
--     camp.dataInicio,
--     camp.dataFim,
--     camp.criadoEm;

CREATE DATABASE IF NOT EXISTS EcoCollect
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE EcoCollect;

CREATE TABLE Usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(14) NOT NULL,
    saldoPontos DECIMAL(10,2) NOT NULL DEFAULT 0.00
);

CREATE TABLE PontosDeColeta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cep VARCHAR(9) NOT NULL,
    status ENUM('disponivel', 'indisponivel') NOT NULL DEFAULT 'disponivel',
    INDEX idx_pontos_status (status),
    INDEX idx_pontos_cep (cep)
);

CREATE TABLE Material (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo ENUM('plasticos', 'metais', 'papeis', 'vidros', 'organico') NOT NULL UNIQUE,
    valorUnidade DECIMAL(10,2) NOT NULL DEFAULT 1.00
);

CREATE TABLE PontoMaterial (
    id INT PRIMARY KEY AUTO_INCREMENT,
    idPonto INT NOT NULL,
    idMaterial INT NOT NULL,
    UNIQUE KEY uk_ponto_material (idPonto, idMaterial),
    INDEX idx_pm_material (idMaterial),
    CONSTRAINT fk_pm_ponto FOREIGN KEY (idPonto) REFERENCES PontosDeColeta(id) ON DELETE CASCADE,
    CONSTRAINT fk_pm_material FOREIGN KEY (idMaterial) REFERENCES Material(id) ON DELETE CASCADE
);

CREATE TABLE Coleta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    idPonto INT NOT NULL,
    idMaterial INT NOT NULL,
    idUsuario INT NOT NULL,
    dataColeta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    quantidadeColetada DECIMAL(10,2) NOT NULL,
    pontuacao DECIMAL(10,2) NOT NULL,
    INDEX idx_coleta_usuario_data (idUsuario, dataColeta),
    INDEX idx_coleta_ponto_data (idPonto, dataColeta),
    INDEX idx_coleta_material (idMaterial),
    CONSTRAINT fk_coleta_ponto FOREIGN KEY (idPonto) REFERENCES PontosDeColeta(id) ON DELETE CASCADE,
    CONSTRAINT fk_coleta_material FOREIGN KEY (idMaterial) REFERENCES Material(id),
    CONSTRAINT fk_coleta_usuario FOREIGN KEY (idUsuario) REFERENCES Usuarios(id) ON DELETE CASCADE
);

-- ==========================================
-- LOJA DE COLLETÁVEIS
-- ==========================================

CREATE TABLE Brinde (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    custoPontos DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL DEFAULT 0,
    imagem VARCHAR(255) DEFAULT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    INDEX idx_brinde_ativo (ativo)
);

CREATE TABLE ResgateBrinde (
    id INT PRIMARY KEY AUTO_INCREMENT,
    idUsuario INT NOT NULL,
    idBrinde INT NOT NULL,
    pontosGastos DECIMAL(10,2) NOT NULL,
    codigoResgate VARCHAR(20) NOT NULL UNIQUE,
    status ENUM('pendente', 'retirado', 'cancelado') NOT NULL DEFAULT 'pendente',
    dataResgate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_resgate_usuario (idUsuario, dataResgate),
    CONSTRAINT fk_resgate_usuario FOREIGN KEY (idUsuario) REFERENCES Usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_resgate_brinde FOREIGN KEY (idBrinde) REFERENCES Brinde(id) ON DELETE RESTRICT
);

-- ==========================================
-- CAMPANHAS DE COLETA
-- ==========================================

CREATE TABLE Campanha (
    id INT PRIMARY KEY AUTO_INCREMENT,
    idUsuario INT NOT NULL,
    idPonto INT DEFAULT NULL,
    titulo VARCHAR(120) NOT NULL,
    descricao TEXT NOT NULL,
    dataInicio DATE NOT NULL,
    dataFim DATE NOT NULL,
    criadoEm DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_campanha_datas (dataInicio, dataFim),
    CONSTRAINT fk_campanha_usuario FOREIGN KEY (idUsuario) REFERENCES Usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_campanha_ponto FOREIGN KEY (idPonto) REFERENCES PontosDeColeta(id) ON DELETE SET NULL
);

CREATE TABLE CampanhaMaterial (
    id INT PRIMARY KEY AUTO_INCREMENT,
    idCampanha INT NOT NULL,
    idMaterial INT NOT NULL,
    UNIQUE KEY uk_campanha_material (idCampanha, idMaterial),
    INDEX idx_cm_material (idMaterial),
    CONSTRAINT fk_cm_campanha FOREIGN KEY (idCampanha) REFERENCES Campanha(id) ON DELETE CASCADE,
    CONSTRAINT fk_cm_material FOREIGN KEY (idMaterial) REFERENCES Material(id) ON DELETE CASCADE
);

-- ==========================================
-- DADOS INICIAIS
-- ==========================================

INSERT INTO Material (tipo, valorUnidade) VALUES
('plasticos', 1.00),
('metais', 5.00),
('papeis', 1.00),
('vidros', 3.00),
('organico', 0.50);

INSERT INTO Brinde (nome, descricao, custoPontos, estoque) VALUES
('Ecobag EcoCollect', 'Sacola retornável de algodão cru reforçado.', 50.00, 30),
('Garrafa Térmica Inox 500ml', 'Garrafa reutilizável para reduzir o consumo de plástico.', 120.00, 15),
('Kit Canudos de Inox + Escova', 'Conjunto sustentável com estojo de transporte.', 35.00, 40),
('Muda de Planta Nativa', 'Muda pronta para plantio com adubo orgânico.', 25.00, 50);

-- ==========================================
-- VIEWS
-- ==========================================

CREATE OR REPLACE VIEW ViewPontosDeColeta AS
SELECT
    p.id,
    p.cep,
    p.status,
    COALESCE(
        GROUP_CONCAT(
            m.tipo
            ORDER BY m.tipo
            SEPARATOR ', '
        ),
        'Nenhum material cadastrado'
    ) AS materiais
FROM PontosDeColeta p
LEFT JOIN PontoMaterial pm ON p.id = pm.idPonto
LEFT JOIN Material m ON pm.idMaterial = m.id
GROUP BY p.id, p.cep, p.status;

CREATE OR REPLACE VIEW ViewMateriaisPorPonto AS
SELECT m.tipo
FROM Material m
INNER JOIN PontoMaterial pm ON m.id = pm.idMaterial
WHERE pm.idPonto = 1;

CREATE OR REPLACE VIEW ViewMateriaisDoPonto AS
SELECT
    pm.idPonto,
    m.id AS idMaterial,
    m.tipo AS material,
    m.valorUnidade
FROM PontoMaterial pm
INNER JOIN Material m ON pm.idMaterial = m.id;

CREATE OR REPLACE VIEW ViewPontosFiltro AS
SELECT
    p.id AS idPonto,
    p.cep,
    p.status,
    pm.idMaterial
FROM PontosDeColeta p
LEFT JOIN PontoMaterial pm ON p.id = pm.idPonto;

CREATE OR REPLACE VIEW ViewRankingUsuarios AS
SELECT
    u.id,
    u.nome,
    u.saldoPontos,
    COUNT(c.id) AS totalColetas,
    COALESCE(SUM(c.pontuacao), 0.00) AS totalPontos
FROM Usuarios u
LEFT JOIN Coleta c ON u.id = c.idUsuario
GROUP BY u.id, u.nome, u.saldoPontos
ORDER BY totalPontos DESC, totalColetas DESC, u.nome ASC;

CREATE OR REPLACE VIEW ViewCampanhas AS
SELECT
    camp.id,
    camp.idUsuario,
    u.nome AS organizador,
    camp.idPonto,
    p.cep AS cepPonto,
    camp.titulo,
    camp.descricao,
    camp.dataInicio,
    camp.dataFim,
    camp.criadoEm,
    COALESCE(
        GROUP_CONCAT(
            m.tipo
            ORDER BY m.tipo
            SEPARATOR ', '
        ),
        'Todos os materiais'
    ) AS materiaisAlvo
FROM Campanha camp
INNER JOIN Usuarios u ON camp.idUsuario = u.id
LEFT JOIN PontosDeColeta p ON camp.idPonto = p.id
LEFT JOIN CampanhaMaterial cm ON camp.id = cm.idCampanha
LEFT JOIN Material m ON cm.idMaterial = m.id
GROUP BY
    camp.id,
    camp.idUsuario,
    u.nome,
    camp.idPonto,
    p.cep,
    camp.titulo,
    camp.descricao,
    camp.dataInicio,
    camp.dataFim,
    camp.criadoEm;