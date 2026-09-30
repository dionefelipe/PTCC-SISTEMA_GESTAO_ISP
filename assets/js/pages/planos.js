async function paginaPlanos() {
    const perfil = Conecta.perfil();
    const [planos, me] = await Promise.all([
        Conecta.request('planos.php'),
        Conecta.request('me.php'),
    ]);

    const adminForm = perfil === 'GESTOR' ? `
        <section class="panel">
            <div class="panel-header"><h2 class="panel-title">Gerenciar Planos do Provedor</h2></div>
            <form id="formPlano" class="toolbar">
                <div class="field"><label>Nome do Plano</label><input id="nome" placeholder="Ex: Fibra Start" required></div>
                <div class="field"><label>Velocidade</label><input id="velocidade" placeholder="Ex: 200 Mbps" required></div>
                <div class="field"><label>Valor Mensal (R$)</label><input id="valor" type="number" step="0.01" placeholder="89.90" required></div>
                <div class="field" style="flex:2"><label>Descrição / Benefícios</label><input id="descricao" placeholder="Roteador Wi-Fi 6 incluso..."></div>
                <button class="btn btn-success" type="submit"><i class="fa-solid fa-plus"></i> Criar Plano</button>
            </form>
        </section>` : '';

    const cards = planos.map((p) => {
        const atual = Number(me.id_plano) === Number(p.id_plano);
        
        const acaoCliente = perfil === 'CLIENTE' ? `
            <button class="btn ${atual ? 'btn-secondary' : 'btn-success'}" style="width: 100%; margin-top: 10px;" data-contratar="${p.id_plano}" ${atual ? 'disabled' : ''}>
                ${atual ? '<i class="fa-solid fa-check"></i> Seu Plano Atual' : 'Contratar Opção'}
            </button>` : '';
            
        const acaoGestor = perfil === 'GESTOR' ? `
            <button class="btn btn-logout" style="width: 100%; margin-top: 10px; justify-content: center;" data-desativar="${p.id_plano}">
                <i class="fa-solid fa-trash"></i> Remover
            </button>` : '';

        return `
            <article class="plan-card ${atual ? 'active-plan' : ''}">
                ${atual ? '<div style="color: var(--accent); font-weight: bold; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 5px;">Plano Atual</div>' : ''}
                <strong>${p.nome}</strong>
                <div class="speed">${p.velocidade}</div>
                <div class="price">${Conecta.money(p.valor)}<span style="font-size: 0.9rem; color: #94a3b8;">/mês</span></div>
                <p class="muted">${p.descricao || 'Sem descrição detalhada'}</p>
                ${acaoCliente}${acaoGestor}
            </article>`;
    }).join('');

    document.getElementById('pageRoot').innerHTML = `
        ${adminForm}
        ${perfil === 'CLIENTE' ? '<h2 style="margin-bottom: 1rem; color: var(--primary);">Opções de Planos Disponíveis</h2>' : ''}
        <div class="plan-grid">${cards || '<p class="empty-state">Nenhum plano disponível no momento.</p>'}</div>
    `;

    document.getElementById('formPlano')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            await Conecta.request('planos.php', {
                method: 'POST',
                body: JSON.stringify({
                    nome: document.getElementById('nome').value,
                    velocidade: document.getElementById('velocidade').value,
                    valor: document.getElementById('valor').value,
                    descricao: document.getElementById('descricao').value,
                }),
            });
            Conecta.toast('Plano disponibilizado!');
            paginaPlanos();
        } catch (err) {
            Conecta.toast(err.message, 'erro');
        }
    });

    document.querySelectorAll('[data-contratar]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            if (!confirm('Deseja confirmar a alteração para este plano?')) return;
            try {
                await Conecta.request('planos.php', {
                    method: 'POST',
                    body: JSON.stringify({ acao: 'contratar', id_plano: Number(btn.dataset.contratar) }),
                });
                Conecta.toast('Plano atualizado com sucesso!');
                paginaPlanos();
            } catch (err) {
                Conecta.toast(err.message, 'erro');
            }
        });
    });

    document.querySelectorAll('[data-desativar]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            if (!confirm('Desativar este plano para novas contratações?')) return;
            try {
                await Conecta.request('planos.php?id=' + btn.dataset.desativar, { method: 'DELETE' });
                Conecta.toast('Plano removido.');
                paginaPlanos();
            } catch (err) {
                Conecta.toast(err.message, 'erro');
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => paginaPlanos().catch((e) => Conecta.toast(e.message, 'erro')));
