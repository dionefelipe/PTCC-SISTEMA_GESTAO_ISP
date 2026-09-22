 <!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inclusion Network - Gestão de Provedores e Inclusão Digital</title>
    <!-- Ícones FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Arquivo de Estilos Externo -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
 <!-- MÁSCARA DE FUNDO PARA O MENU MOBILE -->
    <div class="overlay" id="overlay"></div>

    <!-- NAVEGAÇÃO ESTILO SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <!-- LOGO DO SISTEMA -->
        <div class="sidebar-header">
            <div class="logo">
                <i class="fa-solid fa-wifi"></i>
                <span>Inclusion Network</span>
            </div>
        </div>

        <!-- LISTA DE JANELAS/FUNCIONALIDADES DO PROJETO -->
        <ul class="nav-list">
            <li class="nav-item">
                <a href="#" class="nav-link active">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard Redes</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-users"></i>
                    <span>Clientes e Contratos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-headset"></i>
                    <span>Chamados Técnicos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="views/dashboard.php" class="nav-link">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>Mapeamento de Cobertura</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-box-open"></i>
                    <span>Planos de Internet</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Relatórios Sociais</span>
                </a>
            </li>
        </ul>

        <!-- PERFIL DO USUÁRIO LOGADO -->
        <div class="user-profile">
            <div class="avatar">G</div>
            <div class="user-info">
                <div class="name">Gestor de Provedor</div>
                <div class="role">Admin / Sistema</div>
            </div>
        </div>
    </aside>
</body>