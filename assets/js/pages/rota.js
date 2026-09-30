async function paginaRota() {
    const pontos = await Conecta.request('rota_trabalho.php');
    document.getElementById('pageRoot').innerHTML = `
        <section class="panel">
            <div class="panel-header"><h2 class="panel-title">Rota de trabalho</h2></div>
            <p class="muted" style="margin-bottom:1rem">Chamados abertos ordenados pela prioridade da área.</p>
            <div id="mapa" class="map-box"></div>
        </section>
        <section class="panel">
            <div class="table-responsive">
                <table>
                    <thead><tr><th>Ordem</th><th>Chamado</th><th>Cliente</th><th>Área</th><th>Status</th></tr></thead>
                    <tbody>
                        ${pontos.length ? pontos.map((p, i) => `
                            <tr>
                                <td>${i + 1}</td>
                                <td>#${p.id_chamados} — ${p.descricao}</td>
                                <td>${p.cliente_nome || '—'}</td>
                                <td>${p.regiao_nome || '—'} <span class="badge badge-medium">P${p.prioridade || '-'}</span></td>
                                <td>${Conecta.badgeStatus(p.status)}</td>
                            </tr>`).join('') : '<tr><td colspan="5" class="empty-state">Nenhum chamado em rota.</td></tr>'}
                    </tbody>
                </table>
            </div>
        </section>`;

    if (!window.L) return;
    const map = L.map('mapa').setView([-23.5505, -46.6333], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
    const latlngs = [];
    pontos.forEach((p, i) => {
        if (p.latitude && p.longitude) {
            const latlng = [Number(p.latitude), Number(p.longitude)];
            latlngs.push(latlng);
            L.marker(latlng).addTo(map).bindPopup(`<b>${i + 1}. ${p.cliente_nome || 'Cliente'}</b><br>${p.descricao}`);
        }
    });
    if (latlngs.length > 1) L.polyline(latlngs, { color: '#0284c7' }).addTo(map);
    if (latlngs.length) map.fitBounds(latlngs, { padding: [30, 30] });
    setTimeout(() => map.invalidateSize(), 200);
}

document.addEventListener('DOMContentLoaded', () => paginaRota().catch((e) => Conecta.toast(e.message, 'erro')));
