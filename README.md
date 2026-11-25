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

Implementação demonstrativa full stack para portfolio, com CRUD, API PHP e persistencia em MySQL.

## Arquitetura e escopo real

`index.php` usa `assets/js/index.js` e `api/portfolio.php`; `api/contacts.php` é uma API adicional de contatos, sem interface dedicada neste repositório. Não há autenticação/autorização nessas APIs. Validação de origem e cabeçalhos não equivalem a controle de acesso.

O sistema organiza catálogo de projetos e captura interessados; não implementa tarefas, sprints ou gestão de equipes. Os registros de exemplo no SQL são dados demonstrativos, não evidências de outros sistemas construídos pelo autor.

Consulte [API e dados](docs/API.md). O README interno detalhado foi preservado, com correções das referências a arquivos ausentes.

## Preparação local

Requer PHP 8+, PDO MySQL/mbstring e MySQL/MariaDB. Configure `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` no ambiente do processo PHP, conforme [.env.example](.env.example). Não existe loader `.env`. Banco padrão: `sistema_contatos`.

Revise e importe `gerenciador_e_criador_de_projetos/database.sql` em banco local novo antes de abrir a aplicação. O instalador PHP também escreve no banco; não o exponha como ferramenta pública. Nenhuma dessas operações foi executada nesta auditoria.

## Testes e próximos passos

Implementação demonstrativa sem suíte automatizada ou deploy confirmado. Prioridades: autenticação, autorização para escrita, testes de contratos e proteção do instalador em futura alteração de código. PDO desativa emulação de prepares; consultas que repetem o mesmo placeholder de busca precisam de teste no MySQL utilizado. Não há licença versionada.
