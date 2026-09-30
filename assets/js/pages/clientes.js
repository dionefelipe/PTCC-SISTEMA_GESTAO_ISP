async function paginaClientes() {
    const [clientes, regioes] = await Promise.all([
        Conecta.request('clientes.php'),
        Conecta.request('regioes.php'),
    ]);

    document.getElementById('pageRoot').innerHTML = `
        <section class="panel">
            <div class="panel-header"><h2 class="panel-title">Cadastrar cliente</h2></div>
            <form id="formCliente" class="toolbar">
                <div class="field"><label>Nome</label><input id="nome" required></div>
                <div class="field"><label>E-mail</label><input id="email" type="email" required></div>
                <div class="field"><label>Senha</label><input id="senha" type="password" required></div>
                <div class="field"><label>CEP</label><input id="cep" maxlength="8" required></div>
                <div class="field"><label>Área</label>
                    <select id="id_regiao">
                        <option value="">—</option>
                        ${regioes.map((r) => `<option value="${r.id_regiao}">${r.nome}</option>`).join('')}
                    </select>
                </div>
                <input type="hidden" id="latitude">
                <input type="hidden" id="longitude">
                <button class="btn" type="submit">Salvar</button>
            </form>
            <p id="mensagem-cep" class="hint"></p>
        </section>
        <section class="panel">
            <div class="table-responsive">
                <table>
                    <thead><tr><th>Nome</th><th>E-mail</th><th>Plano</th><th>Área</th><th>CEP</th></tr></thead>
                    <tbody>
                        ${clientes.length ? clientes.map((c) => `
                            <tr>
                                <td>${c.nome}</td><td>${c.email}</td><td>${c.plano || '—'}</td><td>${c.regiao || '—'}</td><td>${c.cep || '—'}</td>
                            </tr>`).join('') : '<tr><td colspan="5" class="empty-state">Nenhum cliente.</td></tr>'}
                    </tbody>
                </table>
            </div>
        </section>`;

    document.getElementById('cep').addEventListener('blur', async () => {
        const cep = document.getElementById('cep').value.replace(/\D/g, '');
        const msg = document.getElementById('mensagem-cep');
        if (cep.length !== 8) return;
        try {
            const via = await fetch(`https://viacep.com.br/ws/${cep}/json/`).then((r) => r.json());
            if (via.erro) return;
            const q = encodeURIComponent(`${via.logradouro}, ${via.localidade}, ${via.uf}`);
            const geo = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${q}`).then((r) => r.json());
            if (geo[0]) {
                document.getElementById('latitude').value = geo[0].lat;
                document.getElementById('longitude').value = geo[0].lon;
                msg.textContent = 'GPS capturado.';
                msg.className = 'hint ok';
            }
        } catch {
            msg.textContent = 'Falha ao geocodificar CEP.';
            msg.className = 'hint error';
        }
    });

    document.getElementById('formCliente').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            await Conecta.request('cadastrar_usuario.php', {
                method: 'POST',
                body: JSON.stringify({
                    nome: document.getElementById('nome').value,
                    email: document.getElementById('email').value,
                    senha: document.getElementById('senha').value,
                    cep: document.getElementById('cep').value,
                    perfil: 'CLIENTE',
                    id_regiao: document.getElementById('id_regiao').value || null,
                    latitude: document.getElementById('latitude').value,
                    longitude: document.getElementById('longitude').value,
                }),
            });
            Conecta.toast('Cliente cadastrado.');
            paginaClientes();
        } catch (err) {
            Conecta.toast(err.message, 'erro');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => paginaClientes().catch((e) => Conecta.toast(e.message, 'erro')));
