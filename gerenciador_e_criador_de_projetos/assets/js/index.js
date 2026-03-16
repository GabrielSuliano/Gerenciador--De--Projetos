const API_PORTFOLIO = "./api/portfolio.php";
const THEME_KEY = "portfolio-os-theme";

const projectGrid = document.getElementById("projectGrid");
const searchInput = document.getElementById("searchInput");
const categoryFilter = document.getElementById("categoryFilter");
const sortBy = document.getElementById("sortBy");
const statProjects = document.getElementById("statProjects");
const statSegments = document.getElementById("statSegments");
const projectForm = document.getElementById("projectForm");
const clearCustomBtn = document.getElementById("clearCustom");
const exportBtn = document.getElementById("exportBtn");

const leadForm = document.getElementById("leadForm");
const leadCount = document.getElementById("leadCount");
const lastLead = document.getElementById("lastLead");
const themeToggle = document.getElementById("themeToggle");
const toast = document.getElementById("toast");

let projetos = [];
let resumoProjetos = { total: 0, categorias: 0, filtrados: 0 };

function notify(message, type = "ok") {
    toast.textContent = message;
    toast.style.borderLeftColor = type === "error" ? "var(--danger)" : "var(--accent)";
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 2600);
}

async function requisicaoApi(url, opcoes = {}) {
    const resposta = await fetch(url, opcoes);
    const payload = await resposta.json();

    if (!resposta.ok || !payload.ok) {
        throw new Error(payload.mensagem || "Falha na comunicação com o servidor.");
    }

    return payload;
}

async function carregarProjetosDoBanco() {
    const query = new URLSearchParams({
        recurso: "projetos",
        busca: searchInput.value.trim(),
        categoria: categoryFilter.value,
        ordem: sortBy.value,
    });

    const payload = await requisicaoApi(`${API_PORTFOLIO}?${query.toString()}`);
    projetos = payload.dados;
    resumoProjetos = payload.resumo;
}

function renderStats() {
    statProjects.textContent = resumoProjetos.total;
    statSegments.textContent = resumoProjetos.categorias;
}

function createCard(project) {
    const article = document.createElement("article");
    article.className = "project";

    const top = document.createElement("div");
    top.className = "project-top";
    top.innerHTML = `
        <div>
            <h3>${project.titulo}</h3>
            <small>${project.stack_tecnologica}</small>
        </div>
        <span class="tag">${project.categoria}</span>
    `;

    const desc = document.createElement("p");
    desc.textContent = project.descricao;

    const actions = document.createElement("div");
    actions.className = "project-actions";

    const link = document.createElement("a");
    link.href = project.link_projeto || "#";
    link.target = "_blank";
    link.rel = "noopener noreferrer";
    link.textContent = "Abrir";

    const feature = document.createElement("button");
    feature.textContent = Number(project.destaque) === 1 ? "Destaque ativo" : "Marcar destaque";
    feature.addEventListener("click", async function () {
        try {
            await requisicaoApi(API_PORTFOLIO, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id: Number(project.id), destaque: Number(project.destaque) !== 1 })
            });
            await render();
            notify("Destaque atualizado.");
        } catch (erro) {
            notify(erro.message, "error");
        }
    });

    const remove = document.createElement("button");
    remove.className = "danger";
    remove.textContent = "Excluir";
    remove.addEventListener("click", async function () {
        try {
            await requisicaoApi(`${API_PORTFOLIO}?recurso=projetos&id=${Number(project.id)}`, { method: "DELETE" });
            await render();
            notify("Projeto removido.");
        } catch (erro) {
            notify(erro.message, "error");
        }
    });

    actions.appendChild(link);
    actions.appendChild(feature);
    actions.appendChild(remove);

    article.appendChild(top);
    article.appendChild(desc);
    article.appendChild(actions);
    return article;
}

function renderProjects() {
    projectGrid.innerHTML = "";

    if (projetos.length === 0) {
        projectGrid.innerHTML = `<div class="empty">Nenhum projeto encontrado com os filtros atuais.</div>`;
        return;
    }

    projetos.forEach(project => {
        projectGrid.appendChild(createCard(project));
    });
}

async function renderLeads() {
    try {
        const payload = await requisicaoApi(`${API_PORTFOLIO}?recurso=leads`);
        const dados = payload.dados;
        leadCount.textContent = dados.total;

        if (!dados.ultimo) {
            lastLead.textContent = "Nenhum";
            return;
        }

        lastLead.textContent = `${dados.ultimo.nome} (${new Date(dados.ultimo.criado_em).toLocaleDateString("pt-BR")})`;
    } catch {
        leadCount.textContent = "0";
        lastLead.textContent = "Nenhum";
    }
}

async function render() {
    await carregarProjetosDoBanco();
    renderStats();
    renderProjects();
    await renderLeads();
}

function applyTheme(theme) {
    document.body.classList.toggle("light", theme === "light");
    localStorage.setItem(THEME_KEY, theme);
}

projectForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const title = document.getElementById("title").value.trim();
    const category = document.getElementById("category").value;
    const stack = document.getElementById("stack").value.trim();
    const link = document.getElementById("link").value.trim();
    const description = document.getElementById("description").value.trim();
    const featured = document.getElementById("featured").checked;

    if (!title || !category || !stack || !description) {
        notify("Preencha todos os campos obrigatórios do projeto.", "error");
        return;
    }

    requisicaoApi(API_PORTFOLIO, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            acao: "criar_projeto",
            titulo: title,
            categoria: category,
            stack,
            link,
            descricao: description,
            destaque: featured
        })
    })
        .then(async () => {
            projectForm.reset();
            await render();
            notify("Projeto adicionado ao portfólio.");
        })
        .catch((erro) => {
            notify(erro.message, "error");
        });
});

clearCustomBtn.addEventListener("click", function () {
    notify("Para limpar tudo, exclua os projetos pela lista (agora os dados estão no banco).", "error");
});

exportBtn.addEventListener("click", function () {
    const data = JSON.stringify(projetos, null, 2);
    const blob = new Blob([data], { type: "application/json" });
    const url = URL.createObjectURL(blob);
    const anchor = document.createElement("a");
    anchor.href = url;
    anchor.download = "portfolio-projects.json";
    anchor.click();
    URL.revokeObjectURL(url);
    notify("Arquivo JSON exportado.");
});

searchInput.addEventListener("input", () => render().catch((erro) => notify(erro.message, "error")));
categoryFilter.addEventListener("change", () => render().catch((erro) => notify(erro.message, "error")));
sortBy.addEventListener("change", () => render().catch((erro) => notify(erro.message, "error")));

leadForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const name = document.getElementById("leadName").value.trim();
    const email = document.getElementById("leadEmail").value.trim().toLowerCase();
    const budget = document.getElementById("leadBudget").value.trim();
    const message = document.getElementById("leadMessage").value.trim();

    if (!name || !email || !message) {
        notify("Preencha nome, e-mail e mensagem para enviar.", "error");
        return;
    }

    const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    if (!validEmail) {
        notify("Informe um e-mail válido.", "error");
        return;
    }

    requisicaoApi(API_PORTFOLIO, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            acao: "criar_lead",
            nome: name,
            email,
            orcamento: budget,
            mensagem: message,
        })
    })
        .then(async () => {
            leadForm.reset();
            await renderLeads();
            notify("Lead registrado com sucesso.");
        })
        .catch((erro) => {
            notify(erro.message, "error");
        });
});

themeToggle.addEventListener("click", function () {
    const isLight = document.body.classList.contains("light");
    applyTheme(isLight ? "dark" : "light");
});

const storedTheme = localStorage.getItem(THEME_KEY) || "dark";
applyTheme(storedTheme);
render().catch((erro) => notify(erro.message, "error"));
