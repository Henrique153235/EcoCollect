CREATE DATABASE IF NOT EXISTS EcoCollect
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE EcoCollect;

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

INSERT INTO Material (tipo, valorUnidade) VALUES 
('plasticos', 1.00),
('metais', 5.00),
('papeis', 1.00),
('vidros', 3.00),
('organico', 0.50);

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