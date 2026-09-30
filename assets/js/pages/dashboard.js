async function carregarDashboard() {
    const stats = await Conecta.request('dashboard_stats.php');
    const perfil = Conecta.perfil();
    const root = document.getElementById('dashboardRoot');
    const nome = Conecta.nome();

    const acoes = {
        GESTOR: [
            ['views/planos.php', 'fa-box-open', 'Gerenciar planos', 'Criar e editar ofertas de internet'],
            ['views/tecnicos.php', 'fa-user-gear', 'Gerenciar técnicos', 'Equipe de campo e permissões'],
            ['views/areas.php', 'fa-map-location-dot', 'Áreas atendidas', 'Acompanhar cobertura e demanda'],
            ['views/relatorios.php', 'fa-file-export', 'Exportar relatórios', 'CSV de chamados e clientes'],
        ],
        TECNICO: [
            ['views/chamados.php', 'fa-headset', 'Fila de chamados', 'Consultar, alterar e finalizar'],
            ['views/rota.php', 'fa-route', 'Rota de trabalho', 'Prioridade geográfica no mapa'],
        ],
        CLIENTE: [
            ['views/meu-plano.php', 'fa-id-card', 'Consultar meu plano', 'Velocidade, valor e benefícios'],
            ['views/planos.php', 'fa-arrows-rotate', 'Adquirir ou alterar plano', 'Escolha um novo pacote'],
            ['views/chamados.php', 'fa-plus', 'Abrir chamado', 'Relatar falha de conexão'],
        ],
    };

    const cardsGestor = `
        <div class="cards-grid">
            <article class="stat-card"><div><h3>Clientes</h3><div class="number">${stats.clientes}</div></div><div class="card-icon icon-blue"><i class="fa-solid fa-users"></i></div></article>
            <article class="stat-card"><div><h3>Técnicos</h3><div class="number">${stats.tecnicos}</div></div><div class="card-icon icon-purple"><i class="fa-solid fa-user-gear"></i></div></article>
            <article class="stat-card"><div><h3>Chamados abertos</h3><div class="number">${stats.chamados_abertos}</div></div><div class="card-icon icon-orange"><i class="fa-solid fa-headset"></i></div></article>
            <article class="stat-card"><div><h3>Áreas</h3><div class="number">${stats.areas}</div></div><div class="card-icon icon-green"><i class="fa-solid fa-map"></i></div></article>
        </div>`;

    const cardsTecnico = `
        <div class="cards-grid">
            <article class="stat-card"><div><h3>Abertos</h3><div class="number">${stats.chamados_abertos}</div></div><div class="card-icon icon-orange"><i class="fa-solid fa-inbox"></i></div></article>
            <article class="stat-card"><div><h3>Em andamento</h3><div class="number">${stats.chamados_andamento}</div></div><div class="card-icon icon-blue"><i class="fa-solid fa-spinner"></i></div></article>
            <article class="stat-card"><div><h3>Finalizados por você</h3><div class="number">${stats.chamados_finalizados}</div></div><div class="card-icon icon-green"><i class="fa-solid fa-check"></i></div></article>
        </div>`;

    const plano = stats.meu_plano || {};
    const cardsCliente = `
        <div class="cards-grid">
            <article class="stat-card"><div><h3>Seu plano</h3><div class="number">${plano.nome || 'Nenhum'}</div><p class="muted">${plano.velocidade || 'Contrate um plano para começar'}</p></div><div class="card-icon icon-blue"><i class="fa-solid fa-wifi"></i></div></article>
            <article class="stat-card"><div><h3>Chamados abertos</h3><div class="number">${stats.meus_chamados}</div></div><div class="card-icon icon-orange"><i class="fa-solid fa-headset"></i></div></article>
        </div>`;

    const cards = perfil === 'GESTOR' ? cardsGestor : perfil === 'TECNICO' ? cardsTecnico : cardsCliente;
    const atalhos = (acoes[perfil] || []).map(([href, icon, title, desc]) => `
        <a class="panel action-card" href="${Conecta.base}${href}">
            <i class="fa-solid ${icon}"></i>
            <strong>${title}</strong>
            <span>${desc}</span>
        </a>`).join('');

    const linhas = (stats.recentes || []).map((c) => `
        <tr>
            <td>#${c.id_chamados}</td>
            <td>${c.cliente_nome || 'Você'}</td>
            <td>${c.descricao || ''}</td>
            <td>${c.regiao_nome || '—'}</td>
            <td>${Conecta.badgeStatus(c.status)}</td>
        </tr>`).join('') || '<tr><td colspan="5" class="empty-state">Nenhum registro recente.</td></tr>';

    const filtro = perfil === 'GESTOR' ? `
        <form class="toolbar" id="filtroDash">
            <div class="field">
                <label for="filtroStatus">Status</label>
                <select id="filtroStatus">
                    <option value="">Todos</option>
                    <option value="Aberto">Aberto</option>
                    <option value="Em andamento">Em andamento</option>
                    <option value="Finalizado">Finalizado</option>
                </select>
            </div>
            <div class="field">
                <label for="filtroRegiao">Área</label>
                <select id="filtroRegiao"><option value="">Todas</option></select>
            </div>
            <button class="btn" type="submit"><i class="fa-solid fa-filter"></i> Aplicar filtros</button>
            <a class="btn btn-secondary" href="${Conecta.base}views/relatorios.php"><i class="fa-solid fa-file-export"></i> Exportar</a>
        </form>` : '';

    root.innerHTML = `
        <div class="welcome-card">
            <div>
                <span class="eyebrow">PAINEL ${Conecta.rotuloPerfil(perfil).toUpperCase()}</span>
                <h1>Olá, ${nome}</h1>
                <p>Fluxos alinhados ao diagrama de uso: login com validação de permissões e ações por perfil.</p>
            </div>
            <i class="fa-solid fa-chart-line welcome-icon"></i>
        </div>
        ${cards}
        <div class="actions-grid">${atalhos}</div>
        <section class="panel">
            <div class="panel-header"><h2 class="panel-title">Atividade recente</h2></div>
            ${filtro}
            <div class="table-responsive">
                <table>
                    <thead><tr><th>ID</th><th>Cliente</th><th>Descrição</th><th>Área</th><th>Status</th></tr></thead>
                    <tbody>${linhas}</tbody>
                </table>
            </div>
        </section>`;

    if (perfil === 'GESTOR') {
        const regioes = await Conecta.request('regioes.php');
        const sel = document.getElementById('filtroRegiao');
        regioes.forEach((r) => {
            sel.insertAdjacentHTML('beforeend', `<option value="${r.id_regiao}">${r.nome}</option>`);
        });
        document.getElementById('filtroDash').addEventListener('submit', async (e) => {
            e.preventDefault();
            const status = document.getElementById('filtroStatus').value;
            const regiao = document.getElementById('filtroRegiao').value;
            const qs = new URLSearchParams();
            if (status) qs.set('status', status);
            if (regiao) qs.set('regiao', regiao);
            const filtrado = await Conecta.request('dashboard_stats.php?' + qs.toString());
            const tbody = root.querySelector('tbody');
            tbody.innerHTML = (filtrado.recentes || []).map((c) => `
                <tr>
                    <td>#${c.id_chamados}</td>
                    <td>${c.cliente_nome || '—'}</td>
                    <td>${c.descricao || ''}</td>
                    <td>${c.regiao_nome || '—'}</td>
                    <td>${Conecta.badgeStatus(c.status)}</td>
                </tr>`).join('') || '<tr><td colspan="5" class="empty-state">Nenhum resultado para o filtro.</td></tr>';
            Conecta.toast('Filtros aplicados.');
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    carregarDashboard().catch((e) => Conecta.toast(e.message, 'erro'));
});
