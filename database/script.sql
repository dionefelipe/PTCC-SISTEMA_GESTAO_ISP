/*CREATE SCHEMA IF NOT EXISTS `tccbd` DEFAULT CHARACTER SET utf8mb4 ;
USE `tccbd` ;

-- -----------------------------------------------------
-- Tabela `perfis_acesso` (RBAC)
-- Define os tipos de usuários: 1-Provedor, 2-Técnico, 3-Cliente
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `perfis_acesso` (
  `id_perfil` INT NOT NULL AUTO_INCREMENT,
  `nome_perfil` VARCHAR(45) NOT NULL, -- Ex: 'Provedor', 'Técnico', 'Cliente'
  PRIMARY KEY (`id_perfil`)
) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Tabela `provedores` (Os Tenants do sistema)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `provedores` (
  `id_provedor` INT NOT NULL AUTO_INCREMENT,
  `razao_social` VARCHAR(100) NOT NULL,
  `cnpj` VARCHAR(20) NOT NULL UNIQUE,
  `email_contato` VARCHAR(100) NOT NULL,
  `telefone_contato` VARCHAR(20),
  `data_cadastro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_provedor`)
) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Tabela `usuarios_sistema`
-- Unifica o acesso. Todos que fazem login estão aqui.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios_sistema` (
  `id_usuario` INT NOT NULL AUTO_INCREMENT,
  `id_perfil` INT NOT NULL,
  `id_provedor` INT NULL, -- Pode ser NULL se o usuário for um cliente que ainda não tem provedor, ou not null dependendo da regra de negócio exata na contratação.
  `email_login` VARCHAR(100) NOT NULL UNIQUE,
  `senha_hash` VARCHAR(255) NOT NULL,
  `status_ativo` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `fk_usuario_perfil`
    FOREIGN KEY (`id_perfil`)
    REFERENCES `perfis_acesso` (`id_perfil`),
  CONSTRAINT `fk_usuario_provedor`
    FOREIGN KEY (`id_provedor`)
    REFERENCES `provedores` (`id_provedor`)
) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Tabela `planos_internet`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `planos_internet` (
  `id_plano` INT NOT NULL AUTO_INCREMENT,
  `id_provedor` INT NOT NULL, -- Isolamento SaaS
  `nome_plano` VARCHAR(100) NOT NULL,
  `velocidade_megas` INT NOT NULL,
  `valor_mensal` DECIMAL(10,2) NOT NULL,
  `descricao_detalhada` TEXT NULL,
  `status_disponivel` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_plano`),
  CONSTRAINT `fk_plano_provedor`
    FOREIGN KEY (`id_provedor`)
    REFERENCES `provedores` (`id_provedor`)
) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Tabela `clientes`
-- Detalhamento dos usuários do perfil 'Cliente'
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `clientes` (
  `id_cliente` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL, -- Vínculo com o login
  `id_plano_contratado` INT NULL, -- O plano que ele escolheu
  `nome_completo` VARCHAR(100) NOT NULL,
  `cpf` VARCHAR(15) NOT NULL UNIQUE,
  `telefone_celular` VARCHAR(20) NOT NULL,
  `bairro` VARCHAR(100) NOT NULL, -- Importante para o roteamento
  `endereco_completo` VARCHAR(255) NOT NULL,
  `latitude_geografica` DECIMAL(10,8) NULL,
  `longitude_geografica` DECIMAL(11,8) NULL,
  PRIMARY KEY (`id_cliente`),
  CONSTRAINT `fk_cliente_usuario`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `usuarios_sistema` (`id_usuario`),
  CONSTRAINT `fk_cliente_plano`
    FOREIGN KEY (`id_plano_contratado`)
    REFERENCES `planos_internet` (`id_plano`)
) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Tabela `tecnicos`
-- Detalhamento dos usuários do perfil 'Técnico'
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tecnicos` (
  `id_tecnico` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL, -- Vínculo com o login
  `nome_completo` VARCHAR(100) NOT NULL,
  `matricula_trabalhista` VARCHAR(45) NOT NULL,
  `telefone_celular` VARCHAR(20) NOT NULL,
  `status_disponibilidade` VARCHAR(45) NOT NULL DEFAULT 'Disponível', -- Ex: Disponível, Em Atendimento, Folga
  PRIMARY KEY (`id_tecnico`),
  CONSTRAINT `fk_tecnico_usuario`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `usuarios_sistema` (`id_usuario`)
) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Tabela `chamados_suporte`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `chamados_suporte` (
  `id_chamado` INT NOT NULL AUTO_INCREMENT,
  `id_provedor` INT NOT NULL, -- Isolamento SaaS
  `id_cliente` INT NOT NULL, -- Quem abriu
  `id_tecnico_atribuido` INT NULL, -- Quem vai atender (definido pelo algoritmo)
  `assunto_chamado` VARCHAR(100) NOT NULL,
  `descricao_problema` TEXT NOT NULL,
  `status_chamado` VARCHAR(45) NOT NULL DEFAULT 'Aberto', -- Ex: Aberto, A Caminho, Em Atendimento, Concluído
  `data_abertura` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_conclusao` DATETIME NULL,
  PRIMARY KEY (`id_chamado`),
  CONSTRAINT `fk_chamado_provedor`
    FOREIGN KEY (`id_provedor`)
    REFERENCES `provedores` (`id_provedor`),
  CONSTRAINT `fk_chamado_cliente`
    FOREIGN KEY (`id_cliente`)
    REFERENCES `clientes` (`id_cliente`),
  CONSTRAINT `fk_chamado_tecnico`
    FOREIGN KEY (`id_tecnico_atribuido`)
    REFERENCES `tecnicos` (`id_tecnico`)
) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Tabela `matriz_pesos_bairros`
-- Base para o Algoritmo de Roteamento Inteligente
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `matriz_pesos_bairros` (
  `id_matriz` INT NOT NULL AUTO_INCREMENT,
  `id_provedor` INT NOT NULL, -- Isolamento SaaS (cada provedor define sua área/pesos)
  `bairro_origem` VARCHAR(100) NOT NULL,
  `bairro_destino` VARCHAR(100) NOT NULL,
  `peso_deslocamento` INT NOT NULL, -- Representa o custo (tempo/distância). Quanto menor, melhor.
  PRIMARY KEY (`id_matriz`),
  CONSTRAINT `fk_matriz_provedor`
    FOREIGN KEY (`id_provedor`)
    REFERENCES `provedores` (`id_provedor`)
) ENGINE = InnoDB;

select *  from matriz_pesos_bairros



-- Adiciona a coluna de renda mensal bruta na tabela de clientes
ALTER TABLE clientes ADD COLUMN renda_mensal_bruta DECIMAL(10,2) NULL AFTER cpf;

UPDATE clientes 
SET latitude_geografica = -23.550520, 
    longitude_geografica = -46.633309 
WHERE id_cliente =1;

ALTER TABLE chamados_suporte ADD COLUMN prioridade VARCHAR(20) DEFAULT 'Normal' AFTER descricao_problema;
ALTER TABLE chamados_suporte ADD COLUMN solucao TEXT NULL AFTER data_conclusao;

-- Adiciona a coluna CEP na tabela de clientes
ALTER TABLE clientes ADD COLUMN cep VARCHAR(10) NULL AFTER cpf;

-- 1. Tabela para gerir Hotspots e Antenas de Acesso Comunitário (Wi-Fi Social)
CREATE TABLE IF NOT EXISTS `pontos_acesso_comunitario` (
  `id_ponto` INT NOT NULL AUTO_INCREMENT,
  `id_provedor` INT NOT NULL,
  `nome_local` VARCHAR(100) NOT NULL, -- Ex: Associação de Moradores do Bairro
  `bairro` VARCHAR(100) NOT NULL,
  `endereco` VARCHAR(255) NOT NULL,
  `status_ativo` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_ponto`),
  CONSTRAINT `fk_ponto_provedor`
    FOREIGN KEY (`id_provedor`)
    REFERENCES `provedores` (`id_provedor`)
) ENGINE = InnoDB;

-- 2. Adiciona uma flag nos planos para indicar se é um Plano Social acessível
ALTER TABLE planos_internet ADD COLUMN indicador_social TINYINT(1) NOT NULL DEFAULT 0 AFTER status_disponivel;

-- Adiciona a coluna CEP na tabela de pontos de acesso comunitário
ALTER TABLE pontos_acesso_comunitario ADD COLUMN cep VARCHAR(10) NULL AFTER nome_local;


-- Adiciona geolocalização na tabela de pontos de acesso comunitário
ALTER TABLE pontos_acesso_comunitario ADD COLUMN latitude_geografica DECIMAL(10,8) NULL AFTER endereco;
ALTER TABLE pontos_acesso_comunitario ADD COLUMN longitude_geografica DECIMAL(11,8) NULL AFTER latitude_geografica;

UPDATE clientes
SET renda_mensal_bruta = 4200.00
WHERE id_cliente = 1

-- 1. Insere o segundo provedor para gerar concorrência
INSERT INTO provedores (razao_social, cnpj, email_contato, telefone_contato) 
VALUES ('InovaNet Conectividade S.A.', '98.765.432/0001-99','inova@provedor.com', '(11) 98888-7777');

-- 2. Insere planos acessíveis para o segundo provedor (ID 2)
INSERT INTO planos_internet (id_provedor, nome_plano, velocidade_megas, valor_mensal, descricao_detalhada, status_disponivel) VALUES
(4, 'Inova Popular 60MB', 60, 49.90, 'Fibra ótica de entrada com alta estabilidade', 1),
(4, 'Inova Conecta 150MB', 150, 79.90, 'Ideal para estudos e home office', 1),
(4, 'Inova Turbo 300MB', 300, 119.90, 'Velocidade superior para toda a família', 1);

-- 3. Mapeia os bairros para o segundo provedor na matriz de pesos (permitindo concorrência na mesma área)
-- Substitua 'Jardim Branca Flor' pelo bairro de teste que utiliza no seu sistema
INSERT INTO matriz_pesos_bairros (id_provedor, bairro_origem,bairro_destino, peso_deslocamento) VALUES
(4, 'vila andrade','Jardim Branca Flor', 10),
(4, 'butantã','Centro', 8);

SELECT c.id_chamado, c.assunto_chamado, cl.bairro, m.peso_deslocamento
FROM chamados_suporte c
INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
INNER JOIN matriz_pesos_bairros m ON c.id_provedor = m.id_provedor AND cl.bairro = m.bairro_destino
WHERE c.id_provedor = 1
  AND c.status_chamado = 'Aberto'
ORDER BY m.peso_deslocamento DESC;

delete from usuarios_sistema where id_usuario = 9

select * from clientes

select *from usuarios_sistema

-- Adiciona a coluna CEP na tabela de provedores, caso ainda não exista
ALTER TABLE provedores ADD COLUMN cep VARCHAR(10) NULL AFTER cnpj;

INSERT INTO usuarios_sistema (id_perfil, id_provedor, email_login, senha_hash) 
VALUES( 1, 4, 'inova@provedor.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')                        

UPDATE usuarios_sistema 
SET senha_hash = '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm' 
WHERE email_login = 'braian@provedor.com';*/