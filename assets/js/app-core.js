(() => {
    const APP_BASE = window.APP_BASE || './';

    window.Conecta = {
        base: APP_BASE,
        api: `${APP_BASE}api/`,
        perfil() {
            return (localStorage.getItem('perfil_usuario') || '').toUpperCase();
        },
        nome() {
            return localStorage.getItem('nome_usuario') || 'Usuário';
        },
        token() {
            return localStorage.getItem('token_acesso');
        },
        headers(json = true) {
            const headers = {
                Accept: 'application/json',
                Authorization: `Bearer ${this.token() || ''}`,
            };
            if (json) headers['Content-Type'] = 'application/json';
            return headers;
        },
        async request(path, options = {}) {
            const resposta = await fetch(this.api + path.replace(/^\/+/, ''), {
                ...options,
                headers: { ...this.headers(!options.body || typeof options.body === 'string'), ...(options.headers || {}) },
            });
            const texto = await resposta.text();
            let dados = {};
            try {
                dados = texto ? JSON.parse(texto) : {};
            } catch {
                dados = { erro: texto || 'Resposta inválida do servidor.' };
            }
            if (resposta.status === 401) {
                this.logout();
                throw new Error('Sessão expirada.');
            }
            if (!resposta.ok) {
                throw new Error(dados.erro || dados.mensagem || 'Falha na requisição.');
            }
            return dados;
        },
        logout() {
            localStorage.removeItem('token_acesso');
            localStorage.removeItem('perfil_usuario');
            localStorage.removeItem('nome_usuario');
            window.location.replace(`${APP_BASE}login.html`);
        },
        rotuloPerfil(perfil) {
            const mapa = {
                GESTOR: 'Provedor',
                TECNICO: 'Técnico',
                CLIENTE: 'Cliente',
            };
            return mapa[perfil] || perfil || 'Usuário';
        },
        money(valor) {
            return Number(valor || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        },
        data(valor) {
            if (!valor) return '—';
            return new Date(valor.replace(' ', 'T')).toLocaleString('pt-BR');
        },
        badgeStatus(status) {
            const chave = String(status || '').toLowerCase();
            let cls = 'badge-low';
            if (chave.includes('aberto')) cls = 'badge-high';
            else if (chave.includes('andamento')) cls = 'badge-medium';
            else if (chave.includes('final') || chave.includes('resolv')) cls = 'badge-ok';
            return `<span class="badge ${cls}">${status || '—'}</span>`;
        },
        toast(msg, tipo = 'ok') {
            let box = document.getElementById('toastBox');
            if (!box) {
                box = document.createElement('div');
                box.id = 'toastBox';
                box.className = 'toast-box';
                document.body.appendChild(box);
            }
            const item = document.createElement('div');
            item.className = `toast toast-${tipo}`;
            item.textContent = msg;
            box.appendChild(item);
            setTimeout(() => item.remove(), 3500);
        },
    };

    document.addEventListener('DOMContentLoaded', () => {
        const perfil = window.Conecta.perfil();
        const nome = window.Conecta.nome();

        document.querySelectorAll('.nav-item[data-roles]').forEach((item) => {
            const roles = item.getAttribute('data-roles').split(',').map((r) => r.trim());
            item.style.display = roles.includes(perfil) ? '' : 'none';
        });

        const avatar = document.getElementById('userAvatar');
        const userName = document.getElementById('userName');
        const userRole = document.getElementById('userRole');
        const perfilEl = document.getElementById('perfilUsuario');

        if (avatar) avatar.textContent = (nome || '?').charAt(0).toUpperCase();
        if (userName) userName.textContent = nome;
        if (userRole) userRole.textContent = window.Conecta.rotuloPerfil(perfil);
        if (perfilEl) perfilEl.textContent = window.Conecta.rotuloPerfil(perfil);

        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const mobileBtn = document.getElementById('mobileToggleBtn');

        const fechar = () => {
            sidebar?.classList.remove('active');
            overlay?.classList.remove('active');
            mobileBtn?.setAttribute('aria-expanded', 'false');
        };

        mobileBtn?.addEventListener('click', () => {
            const aberta = sidebar?.classList.toggle('active');
            overlay?.classList.toggle('active', Boolean(aberta));
            mobileBtn.setAttribute('aria-expanded', String(Boolean(aberta)));
        });
        overlay?.addEventListener('click', fechar);
        document.getElementById('logoutBtn')?.addEventListener('click', () => window.Conecta.logout());
    });
})();
