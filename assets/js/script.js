document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formCadastroPublico');
    if (!form) return;

    const campoCep = document.getElementById('cep');
    const msgCep = document.getElementById('mensagem-cep');

    campoCep?.addEventListener('blur', async () => {
        const cep = (campoCep.value || '').replace(/\D/g, '');
        if (cep.length !== 8) return;
        msgCep.textContent = 'Buscando coordenadas...';
        msgCep.className = 'hint warn';
        try {
            const viaCep = await fetch(`https://viacep.com.br/ws/${cep}/json/`).then((r) => r.json());
            if (viaCep.erro) {
                msgCep.textContent = 'CEP não encontrado.';
                msgCep.className = 'hint error';
                return;
            }
            const q = encodeURIComponent(`${viaCep.logradouro}, ${viaCep.localidade}, ${viaCep.uf}`);
            const mapa = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${q}`).then((r) => r.json());
            if (mapa[0]) {
                document.getElementById('latitude').value = mapa[0].lat;
                document.getElementById('longitude').value = mapa[0].lon;
                msgCep.textContent = 'Localização capturada.';
                msgCep.className = 'hint ok';
            } else {
                msgCep.textContent = 'CEP válido, mas sem coordenada precisa.';
                msgCep.className = 'hint warn';
            }
        } catch {
            msgCep.textContent = 'Não foi possível consultar o CEP.';
            msgCep.className = 'hint error';
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            nome: document.getElementById('nome').value,
            email: document.getElementById('email').value,
            senha: document.getElementById('senha').value,
            perfil: 'CLIENTE',
            cep: document.getElementById('cep').value,
            telefone: document.getElementById('telefone')?.value || '',
            latitude: document.getElementById('latitude').value,
            longitude: document.getElementById('longitude').value,
        };
        try {
            const res = await fetch(`${window.APP_BASE || '../'}api/cadastrar_usuario.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const dados = await res.json();
            if (!res.ok) throw new Error(dados.erro || 'Falha no cadastro.');
            alert('Cadastro concluído. Faça login como Cliente.');
            window.location.href = `${window.APP_BASE || '../'}login.html`;
        } catch (err) {
            alert(err.message);
        }
    });
});
