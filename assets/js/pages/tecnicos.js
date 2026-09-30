async function paginaTecnicos() {
    const [tecnicos, regioes] = await Promise.all([
        Conecta.request('tecnicos.php'),
        Conecta.request('regioes.php'),
    ]);

    document.getElementById('pageRoot').innerHTML = `
        <section class="panel">
            <div class="panel-header">
                <h2 class="panel-title">Equipe Técnica</h2>
                <button class="btn btn-success" id="btnNovoTecnico">
                    <i class="fa-solid fa-plus"></i> Adicionar Técnico
                </button>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead><tr><th>Nome</th><th>E-mail</th><th>Telefone</th><th>Área</th><th>Status</th></tr></thead>
                    <tbody>
                        ${tecnicos.length ? tecnicos.map((t) => `
                            <tr>
                                <td><strong>${t.nome}</strong></td>
                                <td>${t.email}</td>
                                <td>${t.telefone || 'N/A'}</td>
                                <td>${t.regiao || 'Geral'}</td>
                                <td>${Number(t.ativo) === 1 ? '<span class="badge badge-ok">Ativo</span>' : '<span class="badge badge-high">Inativo</span>'}</td>
                            </tr>`).join('') : '<tr><td colspan="5" class="empty-state">Nenhum técnico cadastrado.</td></tr>'}
                    </tbody>
                </table>
            </div>
        </section>

        <dialog id="dlgTecnico">
            <h3>Cadastrar Novo Técnico</h3>
            <form id="formTecnico" style="display: flex; flex-direction: column; gap: 15px;">
                <div class="field"><label>Nome Completo</label><input id="nome" required></div>
                <div class="field"><label>E-mail de Acesso</label><input id="email" type="email" required></div>
                <div class="field"><label>Senha Inicial</label><input id="senha" type="password" required></div>
                <div class="field"><label>Telefone</label><input id="telefone"></div>
                <div class="field"><label>Área de Atuação</label>
                    <select id="id_regiao">
                        <option value="">Todas as Áreas</option>
                        ${regioes.map((r) => `<option value="${r.id_regiao}">${r.nome}</option>`).join('')}
                    </select>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button class="btn" type="submit">Salvar Técnico</button>
                    <button class="btn btn-secondary" type="button" id="fecharDlgTecnico">Cancelar</button>
                </div>
            </form>
        </dialog>
    `;

    const dialog = document.getElementById('dlgTecnico');
    document.getElementById('btnNovoTecnico').addEventListener('click', () => dialog.showModal());
    document.getElementById('fecharDlgTecnico').addEventListener('click', () => dialog.close());

    document.getElementById('formTecnico').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            await Conecta.request('tecnicos.php', {
                method: 'POST',
                body: JSON.stringify({
                    nome: document.getElementById('nome').value,
                    email: document.getElementById('email').value,
                    senha: document.getElementById('senha').value,
                    telefone: document.getElementById('telefone').value,
                    id_regiao: document.getElementById('id_regiao').value || null,
                }),
            });
            dialog.close();
            Conecta.toast('Técnico adicionado com sucesso!');
            paginaTecnicos();
        } catch (err) {
            Conecta.toast(err.message, 'erro');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => paginaTecnicos().catch((e) => Conecta.toast(e.message, 'erro')));