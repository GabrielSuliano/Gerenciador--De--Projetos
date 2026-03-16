CREATE DATABASE IF NOT EXISTS sistema_contatos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_contatos;

CREATE TABLE IF NOT EXISTS contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(25) NOT NULL UNIQUE,
    email VARCHAR(140) NOT NULL UNIQUE,
    company VARCHAR(140) NULL,
    favorite TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS portfolio_projetos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(140) NOT NULL,
    categoria VARCHAR(30) NOT NULL,
    stack_tecnologica VARCHAR(180) NOT NULL,
    descricao TEXT NOT NULL,
    link_projeto VARCHAR(255) NULL,
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS portfolio_leads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(140) NOT NULL,
    orcamento VARCHAR(60) NULL,
    mensagem TEXT NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO portfolio_projetos (titulo, categoria, stack_tecnologica, descricao, link_projeto, destaque)
SELECT * FROM (
    SELECT 'Dashboard Financeiro B2B', 'web', 'Vue, Node, PostgreSQL', 'Sistema de gestão financeira com alertas inteligentes e visão por centro de custo.', './index.php', 1
    UNION ALL
    SELECT 'App de Vendas Externas', 'mobile', 'Flutter, Firebase', 'Aplicativo para equipes de campo com operação offline e sincronização automática.', './meuindex.html', 1
    UNION ALL
    SELECT 'Pipeline de BI Comercial', 'dados', 'Python, Airflow, Power BI', 'Integração de dados e painéis executivos para decisões semanais orientadas por KPI.', './database.sql', 0
    UNION ALL
    SELECT 'Reposicionamento de Marca', 'branding', 'Strategy, Visual System', 'Nova arquitetura de marca e biblioteca visual para expansão digital.', './instalar_banco.php', 0
) AS dados_iniciais
WHERE NOT EXISTS (SELECT 1 FROM portfolio_projetos LIMIT 1);
