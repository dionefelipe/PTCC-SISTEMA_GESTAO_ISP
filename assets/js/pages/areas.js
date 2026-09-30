async function paginaAreas() {
    const [areas, clientes] = await Promise.all([
        Conecta.request('regioes.php'),
        Conecta.request('mapa_clientes.php'),
    ]);

    document.getElementById('pageRoot').innerHTML = `
        <section class="split">
            <div class="panel">
                <div class="panel-header"><h2 class="panel-title">Mapa de cobertura</h2></div>
                <div id="mapa" class="map-box"></div>
            </div>
            <div class="panel">
                <div class="panel-header"><h2 class="panel-title">Nova área</h2></div>
                <form id="formArea" class="toolbar" style="flex-direction:column;align-items:stretch">
                    <div class="field"><label>Nome</label><input id="nome" required></div>
                    <div class="field"><label>Prioridade (1 = mais urgente)</label><input id="prioridade" type="number" min="1" value="3"></div>
                    <div class="field"><label>Descrição</label><input id="descricao"></div>
                    <button class="btn" type="submit">Salvar área</button>
                </form>
            </div>
        </section>
        <section class="panel">
            <div class="table-responsive">
                <table>
                    <thead><tr><th>Área</th><th>Prioridade</th><th>Clientes</th><th>Chamados</th><th>Abertos</th></tr></thead>
                    <tbody>
                        ${areas.map((a) => `
                            <tr>
                                <td>${a.nome}<div class="muted">${a.descricao || ''}</div></td>
                                <td><span class="badge badge-medium">P${a.prioridade}</span></td>
                                <td>${a.total_clientes}</td>
                                <td>${a.total_chamados}</td>
                                <td>${a.chamados_abertos}</td>
                            </tr>`).join('')}
                    </tbody>
                </table>
            </div>
        </section>`;

    if (window.L && document.getElementById('mapa')) {
        const map = L.map('mapa').setView([-23.5505, -46.6333], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
        }).addTo(map);
        clientes.forEach((c) => {
            if (c.latitude && c.longitude) {
                L.marker([c.latitude, c.longitude]).addTo(map)
                    .bindPopup(`<b>${c.nome}</b><br>${c.regiao || ''}<br>CEP ${c.cep || '—'}`);
            }
        });
        setTimeout(() => map.invalidateSize(), 200);
    }

    document.getElementById('formArea').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            await Conecta.request('regioes.php', {
                method: 'POST',
                body: JSON.stringify({
                    nome: document.getElementById('nome').value,
                    prioridade: document.getElementById('prioridade').value,
                    descricao: document.getElementById('descricao').value,
                }),
            });
            Conecta.toast('Área cadastrada.');
            paginaAreas();
        } catch (err) {
            Conecta.toast(err.message, 'erro');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => paginaAreas().catch((e) => Conecta.toast(e.message, 'erro')));
