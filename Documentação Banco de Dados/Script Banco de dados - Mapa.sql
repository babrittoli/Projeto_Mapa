create database mapa; #CRIANDO BANCO DE DADOS

use mapa; #SELECIONANDO O BANCO DE DADOS MAPA

#CRIANDO A TABELA DE USUARIOS
create table usuarios(
	id_usuario integer not null auto_increment primary key,
	nome 	   varchar(50),
	usuario    varchar(15),
	senha      varchar(32),
	email      varchar(80),
	dt_criacao datetime default now(),
	estatus    char(01) default ''
);

#CRIANDO A TABELA DE CADASTRO DAS SALAS
create table salas(
	codigo 		integer primary key,
    descricao	varchar(30) default '',
    andar		integer,
    capacidade	integer,
    dt_criacao  datetime default now(),
    estatus		char(01) default ''
);

#CRIANDO A TABELA DE CADASTRO DE PROFESSORES
create table professores(
	codigo 		integer auto_increment primary key,
	nome		varchar(30) default '',
	cpf			varchar(11) default '',
	tipo		char(1) default 'F',
	dt_criacao	datetime default now(),
	estatus		char(01) default ''
);

#CRIANDO A TABELA DE CADASTRO DAS TURMAS
create table turmas(
	codigo		integer auto_increment primary key,
    descricao	varchar(50) default '',
    capacidade	integer default 0,
    dt_inicio	date,
	dt_criacao	datetime default now(),
    estatus		char(01) default ''
);

#CRIANDO A TABELA DE CADASTRO DOS HORARIOS
create table horarios(
	codigo		integer auto_increment primary key,
    descricao	varchar(50) default '',
    hora_inicio	time,
    hora_fim	time,
    dt_criacao	datetime default now(),
    estatus		char(010) default ''
);

#CRIANDO TABELA DE MAPEAMENTO DE SALAS
create table mapas(
	codigo				integer auto_increment primary key,
    dt_reserva			date,
	codigo_sala         integer default 0,
    codigo_horario		integer default 0,
    codigo_turma		integer default 0,
    codigo_professor	integer default 0,
    estatus				char(01) default '',
    
    foreign key (codigo_sala) references salas(codigo),
    foreign key (codigo_horario) references horarios(codigo),
    foreign key (codigo_turma) references turmas(codigo),
    foreign key (codigo_professor) references professores(codigo)
);

select * from usuarios;