-- create database EcoCollecteste;
-- use EcoCollecteste;

-- create table Usuarios (
--     id int primary key auto_increment,
--     nome varchar(100) not null,
--     email varchar(100) not null unique,
--     telefone varchar(14) not null,
--     perfil enum('usuario', 'responsavel') not null default 'usuario'
-- );

-- create table PontosDeColeta (
--     id int primary key auto_increment,
--     cep varchar(9) not null,
--     status enum('disponivel', 'indisponivel') not null default 'disponivel'
-- );

-- create table Material (
--     id int primary key auto_increment,
--     tipo enum('plasticos', 'metais', 'papeis', 'vidros', 'organico') not null,
--     valorUnidade decimal(10,2) not null default 1.00
-- );

-- -- Tabela reduzida para associação N:M entre Pontos e Materiais
-- create table PontoMaterial (
-- 	id int primary key auto_increment,
--     idPonto int not null,
--     idMaterial int not null,
--     foreign key (idPonto) references PontosDeColeta(id) on delete cascade,
--     foreign key (idMaterial) references Material(id) on delete cascade
-- );

-- create table Coleta (
--     id int primary key auto_increment,
--     idPonto int not null,
--     idMaterial int not null,
--     idUsuario int not null,
--     dataColeta datetime not null default current_timestamp,
--     quantidadeColetada decimal(10,2) not null,
--     pontuacao decimal(10,2) not null,
--     foreign key (idPonto) references PontosDeColeta(id),
--     foreign key (idMaterial) references Material(id),
--     foreign key (idUsuario) references Usuarios(id)
-- );

-- CREATE VIEW ViewPontosDeColeta AS
-- SELECT
--     p.id,
--     p.cep,
--     p.status,
--     COALESCE(
--         GROUP_CONCAT(
--             DISTINCT m.tipo
--             ORDER BY m.tipo
--             SEPARATOR ', '
--         ),
--         'Nenhum material cadastrado'
--     ) AS materiais
-- FROM PontosDeColeta p
-- LEFT JOIN PontoMaterial pm
--     ON p.id = pm.idPonto
-- LEFT JOIN Material m
--     ON pm.idMaterial = m.id
-- GROUP BY
--     p.id,
--     p.cep,
--     p.status;
    
-- select * from ViewPontosDeColeta;

-- CREATE VIEW ViewMateriaisPorPonto AS SELECT m.tipo 
-- FROM Material m
-- INNER JOIN PontoMaterial pm ON m.id = pm.idMaterial
-- WHERE pm.idPonto = 1; -- Substitua pelo ID do ponto desejado

-- CREATE VIEW ViewMateriaisDoPonto AS
-- SELECT 
--     pm.idPonto,
--     m.id AS idMaterial,
--     m.tipo AS material,
--     m.valorUnidade
-- FROM PontoMaterial pm
-- INNER JOIN Material m ON pm.idMaterial = m.id;

-- select * from ViewMateriaisDoPonto where idPonto = 1;

-- ALTER TABLE Usuarios 
-- ADD COLUMN senha VARCHAR(255) NOT NULL AFTER email;

-- SELECT DISTINCT idPonto, cep, status
-- FROM ViewPontosFiltro
-- WHERE 1=1
--   AND (:status IS NULL OR status = :status)
--   AND (:idMaterial IS NULL OR idMaterial = :idMaterial);

-- INSERT INTO Material (tipo, valorUnidade) VALUES 
-- ('plasticos', 1.00),
-- ('metais', 5.00),
-- ('papeis', 1.00),
-- ('vidros', 3.00),
-- ('organico', 0.50);


CREATE DATABASE IF NOT EXISTS EcoCollect
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE EcoCollect;

-- ==========================================================
-- 1. TABELAS
-- ==========================================================

CREATE TABLE Usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(14) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE PontosDeColeta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cep VARCHAR(9) NOT NULL,
    status ENUM('disponivel', 'indisponivel') NOT NULL DEFAULT 'disponivel',
    INDEX idx_pontos_status (status),
    INDEX idx_pontos_cep (cep)
) ENGINE=InnoDB;

CREATE TABLE Material (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo ENUM('plasticos', 'metais', 'papeis', 'vidros', 'organico') NOT NULL UNIQUE,
    valorUnidade DECIMAL(10,2) NOT NULL DEFAULT 1.00
) ENGINE=InnoDB;

-- Tabela associativa N:M otimizada (sem duplicidade de material por ponto)
CREATE TABLE PontoMaterial (
    id INT PRIMARY KEY AUTO_INCREMENT,
    idPonto INT NOT NULL,
    idMaterial INT NOT NULL,
    UNIQUE KEY uk_ponto_material (idPonto, idMaterial),
    INDEX idx_pm_material (idMaterial),
    CONSTRAINT fk_pm_ponto FOREIGN KEY (idPonto) REFERENCES PontosDeColeta(id) ON DELETE CASCADE,
    CONSTRAINT fk_pm_material FOREIGN KEY (idMaterial) REFERENCES Material(id) ON DELETE CASCADE
) ENGINE=InnoDB;

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
    CONSTRAINT fk_coleta_ponto FOREIGN KEY (idPonto) REFERENCES PontosDeColeta(id),
    CONSTRAINT fk_coleta_material FOREIGN KEY (idMaterial) REFERENCES Material(id),
    CONSTRAINT fk_coleta_usuario FOREIGN KEY (idUsuario) REFERENCES Usuarios(id)
) ENGINE=InnoDB;

-- ==========================================================
-- 2. DADOS INICIAIS
-- ==========================================================

INSERT INTO Material (tipo, valorUnidade) VALUES 
('plasticos', 1.00),
('metais', 5.00),
('papeis', 1.00),
('vidros', 3.00),
('organico', 0.50);

-- ==========================================================
-- 3. VIEWS
-- ==========================================================

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

-- View utilizada pela sua consulta de filtro no back-end
CREATE OR REPLACE VIEW ViewPontosFiltro AS
SELECT
    p.id AS idPonto,
    p.cep,
    p.status,
    pm.idMaterial
FROM PontosDeColeta p
LEFT JOIN PontoMaterial pm ON p.id = pm.idPonto;