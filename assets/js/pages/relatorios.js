async function paginaRelatorios() {
    const regioes = await Conecta.request('regioes.php');
    document.getElementById('pageRoot').innerHTML = `
        <section class="panel">
            <div class="panel-header"><h2 class="panel-title">Exportar relatórios</h2></div>
            <p class="muted" style="margin-bottom:1rem">Extensão do dashboard de desempenho: filtre e baixe CSV.</p>
            <form id="formRel" class="toolbar">
                <div class="field"><label>Tipo</label>
                    <select id="tipo">
                        <option value="chamados">Chamados</option>
                        <option value="clientes">Clientes</option>
                    </select>
                </div>
                <div class="field"><label>Status</label>
                    <select id="status">
                        <option value="">Todos</option>
                        <option>Aberto</option>
                        <option>Em andamento</option>
                        <option>Finalizado</option>
                    </select>
                </div>
                <div class="field"><label>Área</label>
                    <select id="regiao">
                        <option value="">Todas</option>
                        ${regioes.map((r) => `<option value="${r.id_regiao}">${r.nome}</option>`).join('')}
                    </select>
                </div>
                <button class="btn btn-secondary" type="button" id="btnPreview">Visualizar</button>
                <button class="btn" type="submit"><i class="fa-solid fa-download"></i> Exportar CSV</button>
            </form>
            <div class="table-responsive" id="preview"></div>
        </section>`;

    async function buscar(formato = 'json') {
        const qs = new URLSearchParams({
            tipo: document.getElementById('tipo').value,
            formato,
        });
        const status = document.getElementById('status').value;
        const regiao = document.getElementById('regiao').value;
        if (status) qs.set('status', status);
        if (regiao) qs.set('regiao', regiao);
        return qs;
    }

    document.getElementById('btnPreview').addEventListener('click', async () => {
        try {
            const qs = await buscar('json');
            const dados = await Conecta.request('relatorios.php?' + qs.toString());
            const linhas = dados.dados || [];
            if (!linhas.length) {
                document.getElementById('preview').innerHTML = '<p class="empty-state">Sem dados.</p>';
                return;
            }
            const cols = Object.keys(linhas[0]);
            document.getElementById('preview').innerHTML = `
                <p class="muted">${dados.total} registros</p>
                <table>
                    <thead><tr>${cols.map((c) => `<th>${c}</th>`).join('')}</tr></thead>
                    <tbody>${linhas.map((l) => `<tr>${cols.map((c) => `<td>${l[c] ?? ''}</td>`).join('')}</tr>`).join('')}</tbody>
                </table>`;
        } catch (err) {
            Conecta.toast(err.message, 'erro');
        }
    });

    document.getElementById('formRel').addEventListener('submit', async (e) => {
        e.preventDefault();
        const qs = await buscar('csv');
        const res = await fetch(Conecta.api + 'relatorios.php?' + qs.toString(), { headers: Conecta.headers(false) });
        if (!res.ok) {
            Conecta.toast('Falha ao exportar.', 'erro');
            return;
        }
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'relatorio.csv';
        a.click();
        URL.revokeObjectURL(url);
        Conecta.toast('Relatório exportado.');
    });
}

document.addEventListener('DOMContentLoaded', () => paginaRelatorios().catch((e) => Conecta.toast(e.message, 'erro')));
