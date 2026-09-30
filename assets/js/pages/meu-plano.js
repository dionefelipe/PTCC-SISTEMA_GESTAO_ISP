async function paginaMeuPlano() {
    const me = await Conecta.request('me.php');
    document.getElementById('pageRoot').innerHTML = `
        <section class="welcome-card">
            <div>
                <span class="eyebrow">CONSULTAR DADOS DO PLANO</span>
                <h1>${me.plano_nome || 'Nenhum plano contratado'}</h1>
                <p>${me.plano_descricao || 'Adquira um plano para acompanhar velocidade e valor.'}</p>
            </div>
            <i class="fa-solid fa-id-card welcome-icon"></i>
        </section>
        <div class="cards-grid">
            <article class="stat-card"><div><h3>Velocidade</h3><div class="number">${me.velocidade || '—'}</div></div><div class="card-icon icon-blue"><i class="fa-solid fa-gauge-high"></i></div></article>
            <article class="stat-card"><div><h3>Mensalidade</h3><div class="number">${me.valor ? Conecta.money(me.valor) : '—'}</div></div><div class="card-icon icon-green"><i class="fa-solid fa-coins"></i></div></article>
            <article class="stat-card"><div><h3>Área</h3><div class="number" style="font-size:1.1rem">${me.regiao_nome || 'Não definida'}</div></div><div class="card-icon icon-orange"><i class="fa-solid fa-location-dot"></i></div></article>
        </div>
        <a class="btn" href="${Conecta.base}views/planos.php">Alterar ou adquirir plano</a>
    `;
}

document.addEventListener('DOMContentLoaded', () => paginaMeuPlano().catch((e) => Conecta.toast(e.message, 'erro')));
