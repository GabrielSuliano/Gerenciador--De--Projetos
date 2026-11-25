# APIs e banco

Base após iniciar PHP dentro de `gerenciador_e_criador_de_projetos`: `http://localhost:8000`.

| Método | Caminho | Contrato |
| --- | --- | --- |
| GET | `/api/portfolio.php?recurso=projetos` | `busca`, `categoria`, `ordem` (`az`, `recent`, padrão destaque) |
| GET | `/api/portfolio.php?recurso=leads` | Resumo de leads |
| POST | `/api/portfolio.php` | JSON `acao=criar_projeto` ou `acao=criar_lead` |
| PUT | `/api/portfolio.php` | JSON `id`, `destaque`; altera somente destaque |
| DELETE | `/api/portfolio.php?id=1` | Exclui projeto |
| GET | `/api/contacts.php` | `search`, `sort` |
| POST | `/api/contacts.php` | JSON `name`, `phone`, `email`, `company` opcional |
| PUT | `/api/contacts.php` | JSON `id` e campos; inclui `favorite` |
| DELETE | `/api/contacts.php?id=1` | Exclui contato |

Projetos recebem `titulo`, `categoria`, `stack`, `descricao`, `link` opcional e `destaque`. Leads recebem `nome`, `email`, `mensagem`, `orcamento` opcional. POST/PUT exigem `Content-Type: application/json`.

Exemplo somente leitura em banco local já inicializado:

```bash
curl "http://localhost:8000/api/portfolio.php?recurso=projetos"
```

`portfolio.php` responde `ok`, `dados` nas leituras; `contacts.php` usa `ok`, `data`, `message`. As APIs não implementam autenticação.

## Dados

`database.sql` cria `contacts`, `portfolio_projetos` e `portfolio_leads` em `sistema_contatos`. Não há FKs entre essas tabelas. Nome, telefone e e-mail de contatos têm UNIQUE. O seed de catálogo é fictício.

Não há migrations incrementais. A importação é manual, em banco local, e não foi executada nesta revisão.
