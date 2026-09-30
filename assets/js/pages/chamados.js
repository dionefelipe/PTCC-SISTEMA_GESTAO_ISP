async function paginaChamados() {
    const perfil = Conecta.perfil();
    const [chamados, regioes] = await Promise.all([
        Conecta.request('chamados_controller.php'),
        Conecta.request('regioes.php'),
    ]);

    const formCliente = perfil === 'CLIENTE' || perfil === 'GESTOR' ? `
        <section class="panel">
            <div class="panel-header"><h2 class="panel-title">Abrir chamado</h2></div>
            <form id="formChamado" class="toolbar" style="align-items:stretch">
                <div class="field" style="flex:2">
                    <label>Descrição do problema</label>
                    <textarea id="descricao" required placeholder="Ex.: Sem conexão desde as 8h"></textarea>
                </div>
                <div class="field">
                    <label>Área / região</label>
                    <select id="id_local" required>
                        ${regioes.map((r) => `<option value="${r.id_regiao}">${r.nome}</option>`).join('')}
                    </select>
                    <button class="btn" type="submit" style="margin-top:10px"><i class="fa-solid fa-plus"></i> Registrar</button>
                </div>
            </form>
        </section>` : '';

    const linhas = chamados.length ? chamados.map((c) => `
        <tr>
            <td>#${c.id_chamados}</td>
            <td>${c.cliente_nome || '—'}</td>
            <td>${c.descricao}</td>
            <td>${c.regiao_nome || '—'} ${c.prioridade ? `<span class="badge badge-medium">P${c.prioridade}</span>` : ''}</td>
            <td>${Conecta.badgeStatus(c.status)}</td>
            <td>${c.tecnico_nome || c.responsavel || '—'}</td>
            <td>${['TECNICO', 'GESTOR'].includes(perfil) ? `<button class="btn btn-ghost" data-edit="${c.id_chamados}">Atualizar</button>` : ''}</td>
        </tr>`).join('') : '<tr><td colspan="7" class="empty-state">Nenhum chamado.</td></tr>';

    document.getElementById('pageRoot').innerHTML = `
        ${formCliente}
        <section class="panel">
            <div class="panel-header"><h2 class="panel-title">Lista de chamados</h2></div>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>ID</th><th>Cliente</th><th>Descrição</th><th>Área</th><th>Status</th><th>Técnico</th><th></th></tr></thead>
                    <tbody>${linhas}</tbody>
                </table>
            </div>
        </section>
        <dialog id="dlgChamado" class="panel" style="border:0;width:min(520px,92vw)">
            <h3>Atualizar chamado</h3>
            <form id="formAtualiza" class="toolbar" style="flex-direction:column;align-items:stretch">
                <input type="hidden" id="id_chamado">
                <div class="field"><label>Status</label>
                    <select id="novo_status">
                        <option>Aberto</option>
                        <option>Em andamento</option>
                        <option>Finalizado</option>
                    </select>
                </div>
                <div class="field"><label>Solução</label><textarea id="solucao" placeholder="Obrigatório para finalizar"></textarea></div>
                <div style="display:flex;gap:8px">
                    <button class="btn" type="submit">Salvar</button>
                    <button class="btn btn-secondary" type="button" id="fecharDlg">Cancelar</button>
                </div>
            </form>
        </dialog>`;

    document.getElementById('formChamado')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            await Conecta.request('chamados_controller.php', {
                method: 'POST',
                body: JSON.stringify({
                    descricao: document.getElementById('descricao').value,
                    id_local: document.getElementById('id_local').value,
                }),
            });
            Conecta.toast('Chamado aberto.');
            paginaChamados();
        } catch (err) {
            Conecta.toast(err.message, 'erro');
        }
    });

    document.querySelectorAll('[data-edit]').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById('id_chamado').value = btn.dataset.edit;
            document.getElementById('dlgChamado').showModal();
        });
    });
    document.getElementById('fecharDlg')?.addEventListener('click', () => document.getElementById('dlgChamado').close());
    document.getElementById('formAtualiza')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            await Conecta.request('chamados_controller.php', {
                method: 'PUT',
                body: JSON.stringify({
                    id_chamado: Number(document.getElementById('id_chamado').value),
                    novo_status: document.getElementById('novo_status').value,
                    solucao: document.getElementById('solucao').value,
                    responsavel: Conecta.nome(),
                }),
            });
            document.getElementById('dlgChamado').close();
            Conecta.toast('Chamado atualizado.');
            paginaChamados();
        } catch (err) {
            Conecta.toast(err.message, 'erro');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => paginaChamados().catch((e) => Conecta.toast(e.message, 'erro')));
