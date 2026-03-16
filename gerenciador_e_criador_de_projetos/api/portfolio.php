<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'; base-uri 'none'");
header('Content-Type: application/json; charset=utf-8');

/**
 * Envia resposta JSON e encerra a execução.
 */
function responder(int $codigo, array $dados): void
{
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Lê JSON do corpo da requisição.
 */
function lerCorpoJson(): array
{
    $tipoConteudo = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));
    if (!str_contains($tipoConteudo, 'application/json')) {
        responder(415, ['ok' => false, 'mensagem' => 'Content-Type deve ser application/json.']);
    }

    $json = file_get_contents('php://input');
    if ($json === false || trim($json) === '') {
        return [];
    }

    $dados = json_decode($json, true);
    if (!is_array($dados)) {
        responder(400, ['ok' => false, 'mensagem' => 'JSON inválido.']);
    }

    return $dados;
}

/**
 * Normaliza texto para evitar espaços extras e limita tamanho.
 */
function normalizarTexto(string $texto, int $tamanhoMaximo): string
{
    $textoTratado = trim(preg_replace('/\s+/', ' ', $texto) ?? '');
    return mb_substr($textoTratado, 0, $tamanhoMaximo);
}

/**
 * Valida e retorna ordenação permitida.
 */
function obterOrdemProjetos(string $ordem): string
{
    if ($ordem === 'az') {
        return 'titulo ASC';
    }
    if ($ordem === 'recent') {
        return 'criado_em DESC';
    }
    return 'destaque DESC, criado_em DESC';
}

/**
 * Lista projetos com filtros opcionais.
 */
function listarProjetos(PDO $conexao): void
{
    $busca = normalizarTexto((string)($_GET['busca'] ?? ''), 120);
    $categoria = normalizarTexto((string)($_GET['categoria'] ?? 'all'), 30);
    $ordem = obterOrdemProjetos((string)($_GET['ordem'] ?? 'featured'));

    $sql = 'SELECT id, titulo, categoria, stack_tecnologica, descricao, link_projeto, destaque, criado_em
            FROM portfolio_projetos
            WHERE 1 = 1';

    $parametros = [];

    if ($categoria !== '' && $categoria !== 'all') {
        $sql .= ' AND categoria = :categoria';
        $parametros['categoria'] = $categoria;
    }

    if ($busca !== '') {
        $sql .= ' AND (titulo LIKE :busca OR stack_tecnologica LIKE :busca OR descricao LIKE :busca)';
        $parametros['busca'] = "%{$busca}%";
    }

    $sql .= " ORDER BY {$ordem}";

    $consulta = $conexao->prepare($sql);
    $consulta->execute($parametros);
    $projetos = $consulta->fetchAll();

    $total = (int)$conexao->query('SELECT COUNT(*) FROM portfolio_projetos')->fetchColumn();
    $categorias = (int)$conexao->query('SELECT COUNT(DISTINCT categoria) FROM portfolio_projetos')->fetchColumn();

    responder(200, [
        'ok' => true,
        'dados' => $projetos,
        'resumo' => [
            'total' => $total,
            'categorias' => $categorias,
            'filtrados' => count($projetos),
        ],
    ]);
}

/**
 * Cria novo projeto no banco.
 */
function criarProjeto(PDO $conexao, array $corpo): void
{
    $titulo = normalizarTexto((string)($corpo['titulo'] ?? ''), 140);
    $categoria = normalizarTexto((string)($corpo['categoria'] ?? ''), 30);
    $stack = normalizarTexto((string)($corpo['stack'] ?? ''), 180);
    $descricao = normalizarTexto((string)($corpo['descricao'] ?? ''), 1000);
    $link = normalizarTexto((string)($corpo['link'] ?? ''), 255);
    $destaque = !empty($corpo['destaque']) ? 1 : 0;

    if ($titulo === '' || $categoria === '' || $stack === '' || $descricao === '') {
        responder(422, ['ok' => false, 'mensagem' => 'Preencha os campos obrigatórios do projeto.']);
    }

    $instrucao = $conexao->prepare('INSERT INTO portfolio_projetos
        (titulo, categoria, stack_tecnologica, descricao, link_projeto, destaque)
        VALUES (:titulo, :categoria, :stack, :descricao, :link, :destaque)');

    $instrucao->execute([
        'titulo' => $titulo,
        'categoria' => $categoria,
        'stack' => $stack,
        'descricao' => $descricao,
        'link' => $link,
        'destaque' => $destaque,
    ]);

    responder(201, ['ok' => true, 'mensagem' => 'Projeto criado com sucesso.']);
}

/**
 * Atualiza campo de destaque do projeto.
 */
function atualizarProjeto(PDO $conexao, array $corpo): void
{
    $idProjeto = (int)($corpo['id'] ?? 0);
    if ($idProjeto <= 0) {
        responder(422, ['ok' => false, 'mensagem' => 'ID do projeto inválido.']);
    }

    $destaque = !empty($corpo['destaque']) ? 1 : 0;

    $instrucao = $conexao->prepare('UPDATE portfolio_projetos SET destaque = :destaque WHERE id = :id');
    $instrucao->execute(['destaque' => $destaque, 'id' => $idProjeto]);

    if ($instrucao->rowCount() === 0) {
        responder(404, ['ok' => false, 'mensagem' => 'Projeto não encontrado.']);
    }

    responder(200, ['ok' => true, 'mensagem' => 'Projeto atualizado com sucesso.']);
}

/**
 * Exclui projeto pelo ID.
 */
function excluirProjeto(PDO $conexao): void
{
    $idProjeto = (int)($_GET['id'] ?? 0);
    if ($idProjeto <= 0) {
        responder(422, ['ok' => false, 'mensagem' => 'ID do projeto inválido.']);
    }

    $instrucao = $conexao->prepare('DELETE FROM portfolio_projetos WHERE id = :id');
    $instrucao->execute(['id' => $idProjeto]);

    if ($instrucao->rowCount() === 0) {
        responder(404, ['ok' => false, 'mensagem' => 'Projeto não encontrado.']);
    }

    responder(200, ['ok' => true, 'mensagem' => 'Projeto removido com sucesso.']);
}

/**
 * Registra um lead vindo do formulário de contato.
 */
function criarLead(PDO $conexao, array $corpo): void
{
    $nome = normalizarTexto((string)($corpo['nome'] ?? ''), 120);
    $email = mb_strtolower(normalizarTexto((string)($corpo['email'] ?? ''), 140));
    $orcamento = normalizarTexto((string)($corpo['orcamento'] ?? ''), 60);
    $mensagemLead = normalizarTexto((string)($corpo['mensagem'] ?? ''), 1400);

    if ($nome === '' || $email === '' || $mensagemLead === '') {
        responder(422, ['ok' => false, 'mensagem' => 'Preencha nome, e-mail e mensagem.']);
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        responder(422, ['ok' => false, 'mensagem' => 'E-mail inválido.']);
    }

    $instrucao = $conexao->prepare('INSERT INTO portfolio_leads (nome, email, orcamento, mensagem)
        VALUES (:nome, :email, :orcamento, :mensagem)');

    $instrucao->execute([
        'nome' => $nome,
        'email' => $email,
        'orcamento' => $orcamento,
        'mensagem' => $mensagemLead,
    ]);

    responder(201, ['ok' => true, 'mensagem' => 'Lead registrado com sucesso.']);
}

/**
 * Retorna resumo de leads.
 */
function resumoLeads(PDO $conexao): void
{
    $totalLeads = (int)$conexao->query('SELECT COUNT(*) FROM portfolio_leads')->fetchColumn();

    $ultimoLead = $conexao->query('SELECT nome, criado_em FROM portfolio_leads ORDER BY criado_em DESC LIMIT 1')->fetch();

    responder(200, [
        'ok' => true,
        'dados' => [
            'total' => $totalLeads,
            'ultimo' => $ultimoLead ?: null,
        ],
    ]);
}

try {
    $conexao = obterConexaoBanco();
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $recurso = (string)($_GET['recurso'] ?? 'projetos');

    if ($metodo === 'GET' && $recurso === 'projetos') {
        listarProjetos($conexao);
    }

    if ($metodo === 'GET' && $recurso === 'leads') {
        resumoLeads($conexao);
    }

    if ($metodo === 'POST') {
        $corpo = lerCorpoJson();
        $acao = (string)($corpo['acao'] ?? '');

        if ($acao === 'criar_projeto') {
            criarProjeto($conexao, $corpo);
        }

        if ($acao === 'criar_lead') {
            criarLead($conexao, $corpo);
        }

        responder(422, ['ok' => false, 'mensagem' => 'Ação inválida no POST.']);
    }

    if ($metodo === 'PUT') {
        $corpo = lerCorpoJson();
        atualizarProjeto($conexao, $corpo);
    }

    if ($metodo === 'DELETE') {
        excluirProjeto($conexao);
    }

    responder(405, ['ok' => false, 'mensagem' => 'Método não suportado.']);
} catch (PDOException $erroBanco) {
    responder(500, ['ok' => false, 'mensagem' => 'Erro de banco de dados.', 'detalhe' => $erroBanco->getCode()]);
} catch (Throwable $erroGeral) {
    responder(500, ['ok' => false, 'mensagem' => 'Erro interno do servidor.']);
}
