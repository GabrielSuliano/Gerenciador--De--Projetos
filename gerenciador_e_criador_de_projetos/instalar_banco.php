<?php

declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

$hostBanco = getenv('DB_HOST') ?: '127.0.0.1';
$portaBanco = getenv('DB_PORT') ?: '3306';
$nomeBanco = getenv('DB_NAME') ?: 'sistema_contatos';
$usuarioBanco = getenv('DB_USER') ?: 'root';
$senhaBanco = getenv('DB_PASS') ?: '';

try {
    $dsnSemBanco = "mysql:host={$hostBanco};port={$portaBanco};charset=utf8mb4";

    $conexao = new PDO($dsnSemBanco, $usuarioBanco, $senhaBanco, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $conexao->exec("CREATE DATABASE IF NOT EXISTS {$nomeBanco} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conexao->exec("USE {$nomeBanco}");

    $conexao->exec('CREATE TABLE IF NOT EXISTS contacts (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL UNIQUE,
        phone VARCHAR(25) NOT NULL UNIQUE,
        email VARCHAR(140) NOT NULL UNIQUE,
        company VARCHAR(140) NULL,
        favorite TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
    )');

    $conexao->exec('CREATE TABLE IF NOT EXISTS portfolio_projetos (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(140) NOT NULL,
        categoria VARCHAR(30) NOT NULL,
        stack_tecnologica VARCHAR(180) NOT NULL,
        descricao TEXT NOT NULL,
        link_projeto VARCHAR(255) NULL,
        destaque TINYINT(1) NOT NULL DEFAULT 0,
        criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
    )');

    $conexao->exec('CREATE TABLE IF NOT EXISTS portfolio_leads (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(120) NOT NULL,
        email VARCHAR(140) NOT NULL,
        orcamento VARCHAR(60) NULL,
        mensagem TEXT NOT NULL,
        criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $projetoExiste = (int)$conexao->query('SELECT COUNT(*) FROM portfolio_projetos')->fetchColumn();
    if ($projetoExiste === 0) {
        $conexao->exec("INSERT INTO portfolio_projetos (titulo, categoria, stack_tecnologica, descricao, link_projeto, destaque) VALUES
            ('Dashboard Financeiro B2B', 'web', 'Vue, Node, PostgreSQL', 'Sistema de gestão financeira com alertas inteligentes e visão por centro de custo.', './index.php', 1),
            ('App de Vendas Externas', 'mobile', 'Flutter, Firebase', 'Aplicativo para equipes de campo com operação offline e sincronização automática.', './meuindex.html', 1),
            ('Pipeline de BI Comercial', 'dados', 'Python, Airflow, Power BI', 'Integração de dados e painéis executivos para decisões semanais orientadas por KPI.', './database.sql', 0),
            ('Reposicionamento de Marca', 'branding', 'Strategy, Visual System', 'Nova arquitetura de marca e biblioteca visual para expansão digital.', './instalar_banco.php', 0)");
    }

    echo '<h2>Banco instalado com sucesso ✅</h2>';
    echo '<p>Banco: <strong>' . htmlspecialchars($nomeBanco, ENT_QUOTES, 'UTF-8') . '</strong></p>';
    echo '<p>Tabela criada/verificada: <strong>contacts</strong></p>';
    echo '<p>Tabelas criadas/verificadas: <strong>portfolio_projetos</strong> e <strong>portfolio_leads</strong></p>';
    echo '<p>Agora abra o sistema em <a href="index.php">index.php</a>.</p>';
} catch (Throwable $erro) {
    http_response_code(500);
    echo '<h2>Falha ao instalar banco ❌</h2>';
    echo '<p>Mensagem: ' . htmlspecialchars($erro->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
}
