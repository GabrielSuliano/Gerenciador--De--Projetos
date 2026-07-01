# Gerenciador de Projetos

Projeto web em PHP, MySQL e JavaScript com duas frentes: um Portfolio OS para catalogar projetos e capturar leads, e uma API de CRM de contatos para cadastro, busca, edicao, favoritos e exclusao.

O codigo principal esta em:

```text
gerenciador_e_criador_de_projetos/
```

## Recursos

- Listagem de projetos com filtros e ordenacao.
- Criacao de projetos pelo formulario.
- Marcacao de projetos em destaque.
- Exclusao de projetos.
- Captura de leads de contato.
- Exportacao de projetos filtrados em JSON.
- Tema claro/escuro salvo no navegador.
- API REST para contatos e portfolio.
- Instalador de banco via PHP.

## Stack

- PHP 8+
- MySQL ou MariaDB
- PDO
- HTML5
- CSS3
- JavaScript puro

## Estrutura

```text
gerenciador_e_criador_de_projetos/
├── api/
│   ├── contacts.php
│   └── portfolio.php
├── config/
│   └── database.php
├── assets/
├── database.sql
├── instalar_banco.php
└── index.php
```

## Como rodar

```bash
cd gerenciador_e_criador_de_projetos
php -S localhost:8000
```

Depois acesse:

```text
http://localhost:8000/index.php
```

## Banco de dados

Voce pode criar as tabelas de duas formas:

1. Abrindo `http://localhost:8000/instalar_banco.php`.
2. Importando manualmente o arquivo `database.sql` no MySQL.

Variaveis aceitas pela conexao:

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

## Documentacao detalhada

Ha um README tecnico mais completo dentro da pasta do projeto:

```text
gerenciador_e_criador_de_projetos/README.md
```

## Status

Projeto full stack funcional para portfolio, com CRUD, API PHP e persistencia em MySQL.
