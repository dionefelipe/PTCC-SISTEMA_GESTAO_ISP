document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const feedback = document.getElementById('loginFeedback');

    document.getElementById('forgotPasswordBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        alert('Para redefinir a senha, entre em contato com o provedor responsável pela sua conta.');
    });

    const togglePassword = document.getElementById('togglePassword');
    const inputSenha = document.getElementById('senha');
    togglePassword?.addEventListener('click', () => {
        const tipo = inputSenha.getAttribute('type') === 'password' ? 'text' : 'password';
        inputSenha.setAttribute('type', tipo);
        togglePassword.classList.toggle('fa-eye-slash');
    });

    loginForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const botao = loginForm.querySelector('.btn-submit');
        botao.disabled = true;
        if (feedback) feedback.textContent = '';

        const payload = {
            tipoUsuario: document.getElementById('tipoUsuario').value,
            email: document.getElementById('email').value,
            senha: document.getElementById('senha').value,
        };

        try {
            const resposta = await fetch('./api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const dados = await resposta.json();
            if (!resposta.ok) throw new Error(dados.erro || 'Erro ao realizar login.');

            localStorage.setItem('token_acesso', dados.token);
            localStorage.setItem('perfil_usuario', dados.perfil);
            localStorage.setItem('nome_usuario', dados.nome || '');
            window.location.href = 'index.php';
        } catch (erro) {
            if (feedback) {
                feedback.textContent = erro.message;
            } else {
                alert(erro.message);
            }
        } finally {
            botao.disabled = false;
        }
    });
});
