<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'; base-uri 'none'");
header('Content-Type: application/json; charset=utf-8');

/**
 * Retorna uma resposta JSON padronizada e encerra a execução.
 */
function responderJson(int $codigoHttp, array $corpo): void
{
    http_response_code($codigoHttp);
    echo json_encode($corpo, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Bloqueia requisições com origem externa quando o cabeçalho Origin estiver presente.
 */
function validarOrigemMesmaAplicacao(): void
{
    $origem = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origem === '') {
        return;
    }

    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $hostAtual = $protocolo . '://' . ($_SERVER['HTTP_HOST'] ?? '');

    if (!hash_equals($hostAtual, $origem)) {
        responderJson(403, ['ok' => false, 'message' => 'Origem não autorizada.']);
    }
}

/**
 * Lê o JSON do corpo da requisição com limite de tamanho e validação de formato.
 */
function lerJsonDaRequisicao(int $tamanhoMaximoBytes = 16384): array
{
    $conteudoTipo = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));
    if (!str_contains($conteudoTipo, 'application/json')) {
        responderJson(415, ['ok' => false, 'message' => 'Content-Type deve ser application/json.']);
    }

    $tamanhoCorpo = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
    if ($tamanhoCorpo > $tamanhoMaximoBytes) {
        responderJson(413, ['ok' => false, 'message' => 'Corpo da requisição excede o limite permitido.']);
    }

    $jsonBruto = file_get_contents('php://input');
    if ($jsonBruto === false || trim($jsonBruto) === '') {
        return [];
    }

    $dados = json_decode($jsonBruto, true);
    if (!is_array($dados)) {
        responderJson(400, ['ok' => false, 'message' => 'JSON inválido.']);
    }

    return $dados;
}

/**
 * Normaliza telefone mantendo apenas dígitos.
 */
function normalizarTelefone(string $telefone): string
{
    return preg_replace('/\D+/', '', $telefone) ?? '';
}

/**
 * Normaliza texto removendo espaços extras e limitando tamanho.
 */
function normalizarTexto(string $texto, int $tamanhoMaximo): string
{
    $textoTratado = trim(preg_replace('/\s+/', ' ', $texto) ?? '');
    return mb_substr($textoTratado, 0, $tamanhoMaximo);
}

/**
 * Verifica se e-mail possui formato válido.
 */
function emailValido(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Define a cláusula de ordenação permitida para evitar injeção SQL.
 */
function obterOrdenacaoPermitida(string $ordenacao): string
{
    if ($ordenacao === 'nome_asc') {
        return 'name ASC';
    }

    if ($ordenacao === 'nome_desc') {
        return 'name DESC';
    }

    return 'created_at DESC';
}

/**
 * Valida campos principais do contato.
 */
function validarDadosContato(string $nome, string $telefone, string $email): void
{
    if ($nome === '' || $telefone === '' || $email === '') {
        responderJson(422, ['ok' => false, 'message' => 'Preencha nome, telefone e e-mail.']);
    }

    if (strlen($telefone) < 9 || strlen($telefone) > 15) {
        responderJson(422, ['ok' => false, 'message' => 'Telefone deve ter entre 9 e 15 dígitos.']);
    }

    if (!emailValido($email)) {
        responderJson(422, ['ok' => false, 'message' => 'E-mail inválido.']);
    }
}

try {
    validarOrigemMesmaAplicacao();

    $conexao = obterConexaoBanco();
    $metodoHttp = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($metodoHttp === 'GET') {
        $busca = normalizarTexto((string)($_GET['search'] ?? ''), 120);
        $ordenacao = (string)($_GET['sort'] ?? 'recentes');
        $clausulaOrdem = obterOrdenacaoPermitida($ordenacao);

        if ($busca !== '') {
            $consulta = $conexao->prepare("SELECT id, name, phone, email, company, favorite, created_at, updated_at
                FROM contacts
                WHERE name LIKE :search OR phone LIKE :search OR email LIKE :search OR company LIKE :search
                ORDER BY {$clausulaOrdem}");
            $consulta->execute(['search' => "%{$busca}%"]);
        } else {
            $consulta = $conexao->query("SELECT id, name, phone, email, company, favorite, created_at, updated_at
                FROM contacts
                ORDER BY {$clausulaOrdem}");
        }

        $contatos = $consulta->fetchAll();
        responderJson(200, ['ok' => true, 'data' => $contatos]);
    }

    if ($metodoHttp === 'POST') {
        $corpo = lerJsonDaRequisicao();

        $nome = normalizarTexto((string)($corpo['name'] ?? ''), 120);
        $telefone = normalizarTelefone((string)($corpo['phone'] ?? ''));
        $email = mb_strtolower(normalizarTexto((string)($corpo['email'] ?? ''), 140));
        $empresa = normalizarTexto((string)($corpo['company'] ?? ''), 140);

        validarDadosContato($nome, $telefone, $email);

        $instrucao = $conexao->prepare('INSERT INTO contacts (name, phone, email, company, favorite) VALUES (:name, :phone, :email, :company, 0)');
        $instrucao->execute([
            'name' => $nome,
            'phone' => $telefone,
            'email' => $email,
            'company' => $empresa,
        ]);

        responderJson(201, ['ok' => true, 'message' => 'Contato criado com sucesso.']);
    }

    if ($metodoHttp === 'PUT') {
        $corpo = lerJsonDaRequisicao();
        $idContato = (int)($corpo['id'] ?? 0);

        if ($idContato <= 0) {
            responderJson(422, ['ok' => false, 'message' => 'ID inválido.']);
        }

        $buscaContato = $conexao->prepare('SELECT * FROM contacts WHERE id = :id');
        $buscaContato->execute(['id' => $idContato]);
        $contatoExistente = $buscaContato->fetch();
        if (!$contatoExistente) {
            responderJson(404, ['ok' => false, 'message' => 'Contato não encontrado.']);
        }

        $nome = array_key_exists('name', $corpo)
            ? normalizarTexto((string)$corpo['name'], 120)
            : (string)$contatoExistente['name'];
        $telefone = array_key_exists('phone', $corpo)
            ? normalizarTelefone((string)$corpo['phone'])
            : (string)$contatoExistente['phone'];
        $email = array_key_exists('email', $corpo)
            ? mb_strtolower(normalizarTexto((string)$corpo['email'], 140))
            : (string)$contatoExistente['email'];
        $empresa = array_key_exists('company', $corpo)
            ? normalizarTexto((string)$corpo['company'], 140)
            : (string)$contatoExistente['company'];
        $favorito = array_key_exists('favorite', $corpo)
            ? (int)((bool)$corpo['favorite'])
            : (int)$contatoExistente['favorite'];

        validarDadosContato($nome, $telefone, $email);

        $instrucao = $conexao->prepare('UPDATE contacts
            SET name = :name, phone = :phone, email = :email, company = :company, favorite = :favorite
            WHERE id = :id');
        $instrucao->execute([
            'name' => $nome,
            'phone' => $telefone,
            'email' => $email,
            'company' => $empresa,
            'favorite' => $favorito,
            'id' => $idContato,
        ]);

        responderJson(200, ['ok' => true, 'message' => 'Contato atualizado com sucesso.']);
    }

    if ($metodoHttp === 'DELETE') {
        $idContato = (int)($_GET['id'] ?? 0);
        if ($idContato <= 0) {
            responderJson(422, ['ok' => false, 'message' => 'ID inválido.']);
        }

        $instrucao = $conexao->prepare('DELETE FROM contacts WHERE id = :id');
        $instrucao->execute(['id' => $idContato]);

        if ($instrucao->rowCount() === 0) {
            responderJson(404, ['ok' => false, 'message' => 'Contato não encontrado.']);
        }

        responderJson(200, ['ok' => true, 'message' => 'Contato excluído com sucesso.']);
    }

    responderJson(405, ['ok' => false, 'message' => 'Método não suportado.']);
} catch (PDOException $erroBanco) {
    if ((int)$erroBanco->getCode() === 23000) {
        responderJson(409, ['ok' => false, 'message' => 'Nome, telefone ou e-mail já cadastrado.']);
    }
    responderJson(500, ['ok' => false, 'message' => 'Erro no banco de dados.']);
} catch (Throwable $erroGeral) {
    responderJson(500, ['ok' => false, 'message' => 'Erro interno do servidor.']);
}
