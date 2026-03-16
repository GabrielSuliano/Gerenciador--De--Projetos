# Gerenciador e Criador de Projetos (PHP + MySQL)

Projeto web em **PHP puro + MySQL + JavaScript**, com duas frentes principais:

1. **CRM de Contatos** (cadastro, busca, edição, favoritar e exclusão).
2. **Portfolio OS** (catálogo de projetos, criação de cases e captura de leads).

O front-end principal roda em `index.php` (Portfolio OS), com uma cópia em `meuindex.html`; o backend expõe APIs em `api/`.

---

## Sumário

- [Visão geral](#visão-geral)
- [Tecnologias](#tecnologias)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Pré-requisitos](#pré-requisitos)
- [Como rodar localmente](#como-rodar-localmente)
- [Configuração do banco de dados](#configuração-do-banco-de-dados)
- [Variáveis de ambiente](#variáveis-de-ambiente)
- [APIs disponíveis](#apis-disponíveis)
  - [API de Contatos](#api-de-contatos-apicontactsphp)
  - [API de Portfólio](#api-de-portfólio-apiportfoliophp)
- [Regras de dados e validações](#regras-de-dados-e-validações)
- [Segurança implementada](#segurança-implementada)
- [Fluxo rápido de uso](#fluxo-rápido-de-uso)
- [Problemas comuns](#problemas-comuns)

---

## Visão geral

### 1) Portfolio OS (`index.php`)
Sistema de apresentação de portfólio com integração ao banco:
- Listagem de projetos com filtros e ordenação
- Criação de novos projetos pelo formulário (sem editar código)
- Marcar projeto como destaque
- Excluir projeto
- Captura de leads de contato
- Exportação dos projetos filtrados em JSON
- Tema claro/escuro (salvo no `localStorage`)

### 2) CRM de Contatos (API disponível)
O backend de contatos continua disponível em `api/contacts.php` e na tabela `contacts`, podendo ser consumido por outro frontend.

---

## Tecnologias

- **Backend:** PHP 8+ (PDO)
- **Banco de dados:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Estilo:** CSS customizado (Portfolio)

---

## Estrutura do projeto

```txt
.
├── api/
│   ├── contacts.php      # API REST de contatos
│   └── portfolio.php     # API REST de projetos e leads
├── config/
│   └── database.php      # Conexão PDO com o banco
├── database.sql          # Script SQL de criação de schema/tabelas + seed
├── index.php             # Frontend Portfolio OS (principal)
├── instalar_banco.php    # Instalador automático do banco/tabelas
└── meuindex.html         # Cópia HTML do Portfolio OS
```

---

## Pré-requisitos

- PHP **8.0+** com extensão **PDO MySQL** habilitada
- MySQL ou MariaDB em execução
- Navegador moderno

---

## Como rodar localmente

1. Abra o terminal na pasta do projeto.
2. Suba um servidor PHP local:

```bash
php -S localhost:8000
```

3. Acesse no navegador:
- Portfolio (principal): `http://localhost:8000/index.php`
- Portfolio (cópia HTML): `http://localhost:8000/meuindex.html`
- Instalador do banco: `http://localhost:8000/instalar_banco.php`

---

## Configuração do banco de dados

Você pode configurar de duas formas:

### Opção A (recomendada): instalador automático
Abra:

```txt
http://localhost:8000/instalar_banco.php
```

O instalador:
- Cria o banco (se não existir)
- Cria as tabelas `contacts`, `portfolio_projetos`, `portfolio_leads`
- Insere projetos iniciais em `portfolio_projetos` (apenas se a tabela estiver vazia)

### Opção B: script SQL manual
Execute o arquivo `database.sql` no seu MySQL.

---

## Variáveis de ambiente

A conexão usa as variáveis abaixo (com fallback para padrão):

- `DB_HOST` (padrão: `127.0.0.1`)
- `DB_PORT` (padrão: `3306`)
- `DB_NAME` (padrão: `sistema_contatos`)
- `DB_USER` (padrão: `root`)
- `DB_PASS` (padrão: vazio)

Se não definir variáveis, o projeto tentará conectar com esses valores padrão.

---

## APIs disponíveis

## API de Contatos (`api/contacts.php`)

### `GET /api/contacts.php`
Lista contatos.

Query params:
- `search` (opcional): busca textual
- `sort` (opcional): `recentes` | `nome_asc` | `nome_desc`

Exemplo:
```txt
/api/contacts.php?search=maria&sort=nome_asc
```

Resposta de sucesso:
```json
{
  "ok": true,
  "data": [
    {
      "id": 1,
      "name": "Maria Silva",
      "phone": "11999999999",
      "email": "maria@email.com",
      "company": "Acme",
      "favorite": 1,
      "created_at": "2026-03-16 10:00:00",
      "updated_at": null
    }
  ]
}
```

### `POST /api/contacts.php`
Cria contato.

Body JSON:
```json
{
  "name": "Maria Silva",
  "phone": "(11) 99999-9999",
  "email": "maria@email.com",
  "company": "Acme"
}
```

### `PUT /api/contacts.php`
Atualiza contato existente (parcial ou completo).

Body JSON (exemplo):
```json
{
  "id": 1,
  "favorite": true
}
```

### `DELETE /api/contacts.php?id=1`
Exclui contato por ID.

---

## API de Portfólio (`api/portfolio.php`)

### `GET /api/portfolio.php?recurso=projetos`
Lista projetos com filtros.

Query params:
- `recurso=projetos`
- `busca` (opcional)
- `categoria` (opcional): `all`, `web`, `mobile`, `dados`, `branding`
- `ordem` (opcional): `featured`, `az`, `recent`

Resposta inclui:
- `dados` (lista de projetos)
- `resumo.total`
- `resumo.categorias`
- `resumo.filtrados`

### `GET /api/portfolio.php?recurso=leads`
Retorna resumo de leads:
- total de leads
- último lead (nome e data), quando existir

### `POST /api/portfolio.php`
A API de portfólio diferencia ações por `acao` no JSON.

#### Criar projeto
```json
{
  "acao": "criar_projeto",
  "titulo": "Projeto X",
  "categoria": "web",
  "stack": "PHP, MySQL, JS",
  "descricao": "Descrição do projeto",
  "link": "https://exemplo.com",
  "destaque": true
}
```

#### Criar lead
```json
{
  "acao": "criar_lead",
  "nome": "João",
  "email": "joao@email.com",
  "orcamento": "5k-10k",
  "mensagem": "Quero um site institucional"
}
```

### `PUT /api/portfolio.php`
Atualiza destaque de projeto.

Body JSON:
```json
{
  "id": 1,
  "destaque": false
}
```

### `DELETE /api/portfolio.php?id=1`
Remove projeto por ID.

---

## Regras de dados e validações

### Contatos
- `name`, `phone`, `email` são obrigatórios
- Telefone é normalizado para apenas dígitos
- Telefone deve ter entre **9 e 15** dígitos
- E-mail validado por formato
- `name`, `phone`, `email` têm restrição `UNIQUE`

### Portfólio
- Projeto exige `titulo`, `categoria`, `stack`, `descricao`
- Lead exige `nome`, `email`, `mensagem`
- E-mail de lead validado por formato

---

## Segurança implementada

As APIs enviam cabeçalhos de segurança, incluindo:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Referrer-Policy: no-referrer`
- `Content-Security-Policy` restritiva

Além disso:
- Queries usam `PDO` com prepared statements
- Ordenações permitidas são controladas por whitelist
- JSON inválido e `Content-Type` incorreto retornam erro apropriado

---

## Fluxo rápido de uso

1. Configure o banco (ideal: `instalar_banco.php`).
2. Abra `index.php` para usar o Portfolio OS.
3. (Opcional) Abra `meuindex.html` como cópia equivalente do portfólio.
4. Use as rotas em `api/` para integrar com outros frontends.

---

## Problemas comuns

### Erro de conexão com banco
- Verifique host, porta, usuário e senha
- Confirme se o MySQL está rodando
- Ajuste variáveis `DB_*`

### Erro 415 nas APIs
- Envie `Content-Type: application/json` em `POST`/`PUT`

### Erro 409 ao criar contato
- Já existe contato com mesmo nome, telefone ou e-mail

### Tela sem dados
- Confirme se as tabelas foram criadas
- Rode `instalar_banco.php` ou importe `database.sql`

---

Se quiser, posso também gerar uma versão **README curto (MVP)** para apresentação no GitHub com menos detalhes técnicos.