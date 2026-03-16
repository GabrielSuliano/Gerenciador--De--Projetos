<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Portfolio OS</title>
    <link rel="stylesheet" href="assets/css/index.css" />
</head>
<body>
    <header class="container glass">
        <nav class="nav">
            <div class="brand"><span class="brand-dot"></span> Portfolio OS</div>
            <div class="nav-links">
                <a href="#inicio">Início</a>
                <a href="#servicos">Serviços</a>
                <a href="#projetos">Projetos</a>
                <a href="#contato">Contato</a>
            </div>
            <button id="themeToggle" class="soft">Tema</button>
        </nav>
    </header>

    <main class="container">
        <section id="inicio" class="hero glass">
            <div>
                <h1>Sistema de Portfólio completo para apresentar, filtrar e gerenciar projetos reais.</h1>
                <p>Um modelo profissional com foco em resultado: catálogo de projetos, construção dinâmica de cases, identidade visual forte e captura de oportunidades de clientes em um só lugar.</p>
                <div class="hero-actions">
                    <a class="button primary" href="#projetos">Ver projetos</a>
                    <a class="button soft" href="#builder">Criar novo case</a>
                </div>
            </div>
            <div class="hero-stats">
                <article class="stat"><strong id="statProjects">0</strong><span>Projetos ativos</span></article>
                <article class="stat"><strong id="statClients">120+</strong><span>Clientes impactados</span></article>
                <article class="stat"><strong id="statSegments">0</strong><span>Categorias</span></article>
                <article class="stat"><strong>98%</strong><span>Taxa de aprovação</span></article>
            </div>
        </section>

        <section id="servicos" class="section glass">
            <div class="section-head">
                <div>
                    <h2>Serviços de alto nível</h2>
                    <p class="sub">Estrutura pronta para mostrar seu posicionamento comercial.</p>
                </div>
            </div>
            <div class="services">
                <article class="service">
                    <h3>Design de Produto</h3>
                    <p>Construção de interfaces escaláveis com foco em usabilidade, clareza e impacto de negócio.</p>
                </article>
                <article class="service">
                    <h3>Desenvolvimento Web</h3>
                    <p>Soluções web rápidas e seguras com arquitetura limpa, integrações e manutenção contínua.</p>
                </article>
                <article class="service">
                    <h3>Growth & Métricas</h3>
                    <p>Experimentação orientada a dados para melhorar aquisição, retenção e conversão.</p>
                </article>
            </div>
        </section>

        <section id="projetos" class="section glass">
            <div class="section-head">
                <div>
                    <h2>Catálogo de Projetos</h2>
                    <p class="sub">Filtro por categoria, busca por texto e ordenação por destaque.</p>
                </div>
                <button id="exportBtn" class="soft">Exportar JSON</button>
            </div>
            <div class="portfolio-controls">
                <input id="searchInput" type="text" placeholder="Buscar por nome, stack ou descrição" />
                <select id="categoryFilter">
                    <option value="all">Todas categorias</option>
                    <option value="web">Web</option>
                    <option value="mobile">Mobile</option>
                    <option value="dados">Dados</option>
                    <option value="branding">Branding</option>
                </select>
                <select id="sortBy">
                    <option value="featured">Destaque</option>
                    <option value="az">Nome A-Z</option>
                    <option value="recent">Mais recentes</option>
                </select>
            </div>
            <div id="projectGrid" class="portfolio-grid"></div>
        </section>

        <section id="builder" class="section glass">
            <div class="section-head">
                <div>
                    <h2>Builder de Cases</h2>
                    <p class="sub">Adicione novos projetos no portfólio sem mexer no código.</p>
                </div>
            </div>
            <form id="projectForm" class="builder">
                <input id="title" type="text" placeholder="Nome do projeto" required />
                <select id="category" required>
                    <option value="">Categoria</option>
                    <option value="web">Web</option>
                    <option value="mobile">Mobile</option>
                    <option value="dados">Dados</option>
                    <option value="branding">Branding</option>
                </select>
                <input id="stack" type="text" placeholder="Stack (ex: HTML, CSS, JS)" required />
                <input id="link" type="url" placeholder="Link do projeto (opcional)" />
                <textarea id="description" class="full" placeholder="Resumo de impacto do projeto" required></textarea>
                <label class="full" style="display:flex;gap:8px;align-items:center;color:var(--muted);font-size:.9rem;">
                    <input id="featured" type="checkbox" style="width:16px;height:16px;"> Marcar como destaque
                </label>
                <div class="builder-actions">
                    <button class="primary" type="submit">Salvar projeto</button>
                    <button id="clearCustom" class="soft" type="button">Limpar projetos personalizados</button>
                </div>
            </form>
        </section>

        <section class="section glass">
            <div class="section-head">
                <div>
                    <h2>Processo de entrega</h2>
                    <p class="sub">Fluxo usado para transformar briefing em resultado.</p>
                </div>
            </div>
            <div class="timeline">
                <article class="step">
                    <h4>1. Diagnóstico estratégico</h4>
                    <p>Levantamento de objetivo, público e metas para definir prioridade real de negócio.</p>
                </article>
                <article class="step">
                    <h4>2. Execução orientada a valor</h4>
                    <p>Construção incremental com validações rápidas e foco em redução de risco.</p>
                </article>
                <article class="step">
                    <h4>3. Mensuração e escala</h4>
                    <p>Leitura de resultados, ajustes finos e plano de continuidade para crescimento.</p>
                </article>
            </div>
        </section>

        <section id="contato" class="section glass">
            <div class="section-head">
                <div>
                    <h2>Contato comercial</h2>
                    <p class="sub">Receba leads direto no navegador com armazenamento local.</p>
                </div>
            </div>
            <div class="contact">
                <form id="leadForm" class="contact-form">
                    <input id="leadName" type="text" placeholder="Seu nome" required>
                    <input id="leadEmail" type="email" placeholder="Seu e-mail" required>
                    <input id="leadBudget" type="text" placeholder="Faixa de orçamento (ex: 5k - 10k)">
                    <textarea id="leadMessage" placeholder="Descreva o projeto" required></textarea>
                    <button class="primary" type="submit">Enviar proposta</button>
                </form>
                <aside class="contact-card">
                    <h3>Indicadores rápidos</h3>
                    <p>Leads recebidos: <strong id="leadCount">0</strong></p>
                    <p>Último contato: <strong id="lastLead">Nenhum</strong></p>
                    <p style="color:var(--muted);font-size:.9rem;">Dica: conecte este formulário à sua API quando estiver pronto para produção.</p>
                </aside>
            </div>
        </section>
    </main>

    <footer class="container">Portfolio OS · Sistema de portfólio pronto para apresentar valor de verdade</footer>
    <div id="toast" class="toast"></div>

    <script src="assets/js/index.js"></script>
</body>
</html>
