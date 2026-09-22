document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const senhaInput = document.getElementById('senha');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const forgotPasswordBtn = document.getElementById('forgotPasswordBtn');

    // Alternar visibilidade da senha (Mostrar/Ocultar)
    if (togglePasswordBtn && senhaInput) {
        togglePasswordBtn.addEventListener('click', () => {
            const isPassword = senhaInput.getAttribute('type') === 'password';
            senhaInput.setAttribute('type', isPassword ? 'text' : 'password');
            
            togglePasswordBtn.classList.toggle('fa-eye');
            togglePasswordBtn.classList.toggle('fa-eye-slash');
        });
    }

    // Submissão do Formulário de Login
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const tipoUsuario = document.getElementById('tipoUsuario').value;
            const email = document.getElementById('email').value;
            const senha = senhaInput.value;

            // Simulação de login
            console.log('Dados do Login:', { tipoUsuario, email, senha });
            alert(`Acesso realizado com sucesso como: ${tipoUsuario}`);
            
            // Redirecionamento para a página inicial (index.html)
            window.location.href = 'index.php';
        });
    }

    // Ação do link "Esqueceu a Senha"
    if (forgotPasswordBtn) {
        forgotPasswordBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const email = prompt('Digite seu e-mail cadastrado para redefinição de senha:');
            if (email) {
                alert(`Instruções de recuperação de senha foram enviadas para: ${email}`);
            }
        });
    }
});