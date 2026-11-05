CREATE TABLE IF NOT EXISTS exemplos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(120) NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

create database EcoCollecteste;
use EcoCollecteste;

create table Usuarios (
    id int primary key auto_increment,
    nome varchar(100) not null,
    email varchar(100) not null unique,
    telefone varchar(14) not null,
    perfil enum('usuario', 'responsavel') not null default 'usuario'
);

create table PontosDeColeta (
    id int primary key auto_increment,
    cep varchar(9) not null,
    status enum('disponivel', 'indisponivel') not null default 'disponivel'
);

create table Material (
    id int primary key auto_increment,
    tipo enum('plasticos', 'metais', 'papeis', 'vidros', 'organico') not null,
    valorUnidade decimal(10,2) not null default 1.00
);

-- Tabela reduzida para associação N:M entre Pontos e Materiais
create table PontoMaterial (
	id int primary key auto_increment,
    idPonto int not null,
    idMaterial int not null,
    foreign key (idPonto) references PontosDeColeta(id) on delete cascade,
    foreign key (idMaterial) references Material(id) on delete cascade
);

create table Coleta (
    id int primary key auto_increment,
    idPonto int not null,
    idMaterial int not null,
    idUsuario int not null,
    dataColeta datetime not null default current_timestamp,
    quantidadeColetada decimal(10,2) not null,
    pontuacao decimal(10,2) not null,
    foreign key (idPonto) references PontosDeColeta(id),
    foreign key (idMaterial) references Material(id),
    foreign key (idUsuario) references Usuarios(id)
);

CREATE VIEW ViewPontosDeColeta AS
SELECT
    p.id,
    p.cep,
    p.status,
    COALESCE(
        GROUP_CONCAT(
            DISTINCT m.tipo
            ORDER BY m.tipo
            SEPARATOR ', '
        ),
        'Nenhum material cadastrado'
    ) AS materiais
FROM PontosDeColeta p
LEFT JOIN PontoMaterial pm
    ON p.id = pm.idPonto
LEFT JOIN Material m
    ON pm.idMaterial = m.id
GROUP BY
    p.id,
    p.cep,
    p.status;
    
select * from ViewPontosDeColeta;

CREATE VIEW ViewMateriaisPorPonto AS SELECT m.tipo 
FROM Material m
INNER JOIN PontoMaterial pm ON m.id = pm.idMaterial
WHERE pm.idPonto = 1; -- Substitua pelo ID do ponto desejado

CREATE VIEW ViewMateriaisDoPonto AS
SELECT 
    pm.idPonto,
    m.id AS idMaterial,
    m.tipo AS material,
    m.valorUnidade
FROM PontoMaterial pm
INNER JOIN Material m ON pm.idMaterial = m.id;

select * from ViewMateriaisDoPonto where idPonto = 1;
