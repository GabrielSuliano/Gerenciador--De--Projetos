<?php

declare(strict_types=1);

/**
 * Cria e retorna uma conexão PDO segura com o banco MySQL.
 */
function obterConexaoBanco(): PDO
{
    $hostBanco = getenv('DB_HOST') ?: '127.0.0.1';
    $portaBanco = getenv('DB_PORT') ?: '3306';
    $nomeBanco = getenv('DB_NAME') ?: 'sistema_contatos';
    $usuarioBanco = getenv('DB_USER') ?: 'root';
    $senhaBanco = getenv('DB_PASS') ?: '';

    $dsn = "mysql:host={$hostBanco};port={$portaBanco};dbname={$nomeBanco};charset=utf8mb4";

    return new PDO($dsn, $usuarioBanco, $senhaBanco, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
