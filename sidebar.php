<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/paths.php';

$activeNav = $activeNav ?? '';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a class="logo" href="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>index.php">
            <i class="fa-solid fa-wifi"></i>
            <span>ConectaSocial</span>
        </a>
    </div>

    <nav>
        <ul class="nav-list">
            <li class="nav-item" data-roles="GESTOR,TECNICO,CLIENTE">
                <a href="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>index.php"
                   class="nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>"
                   data-nav="dashboard">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item" data-roles="CLIENTE">
                <a href="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>views/meu-plano.php"
                   class="nav-link <?= $activeNav === 'meu-plano' ? 'active' : '' ?>"
                   data-nav="meu-plano">
                    <i class="fa-solid fa-id-card"></i>
                    <span>Meu plano</span>
                </a>
            </li>
            <li class="nav-item" data-roles="GESTOR,CLIENTE">
                <a href="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>views/planos.php"
                   class="nav-link <?= $activeNav === 'planos' ? 'active' : '' ?>"
                   data-nav="planos">
                    <i class="fa-solid fa-box-open"></i>
                    <span>Planos de internet</span>
                </a>
            </li>
            <li class="nav-item" data-roles="GESTOR,TECNICO,CLIENTE">
                <a href="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>views/chamados.php"
                   class="nav-link <?= $activeNav === 'chamados' ? 'active' : '' ?>"
                   data-nav="chamados">
                    <i class="fa-solid fa-headset"></i>
                    <span>Chamados</span>
                </a>
            </li>
            <li class="nav-item" data-roles="TECNICO">
                <a href="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>views/rota.php"
                   class="nav-link <?= $activeNav === 'rota' ? 'active' : '' ?>"
                   data-nav="rota">
                    <i class="fa-solid fa-route"></i>
                    <span>Rota de trabalho</span>
                </a>
            </li>
            <li class="nav-item" data-roles="GESTOR">
                <a href="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>views/tecnicos.php"
                   class="nav-link <?= $activeNav === 'tecnicos' ? 'active' : '' ?>"
                   data-nav="tecnicos">
                    <i class="fa-solid fa-user-gear"></i>
                    <span>Gerenciar técnicos</span>
                </a>
            </li>
            <li class="nav-item" data-roles="GESTOR">
                <a href="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>views/areas.php"
                   class="nav-link <?= $activeNav === 'areas' ? 'active' : '' ?>"
                   data-nav="areas">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>Áreas atendidas</span>
                </a>
            </li>
            <li class="nav-item" data-roles="GESTOR">
                <a href="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>views/clientes.php"
                   class="nav-link <?= $activeNav === 'clientes' ? 'active' : '' ?>"
                   data-nav="clientes">
                    <i class="fa-solid fa-users"></i>
                    <span>Clientes</span>
                </a>
            </li>
            <li class="nav-item" data-roles="GESTOR">
                <a href="<?= htmlspecialchars($BASE, ENT_QUOTES, 'UTF-8') ?>views/relatorios.php"
                   class="nav-link <?= $activeNav === 'relatorios' ? 'active' : '' ?>"
                   data-nav="relatorios">
                    <i class="fa-solid fa-file-export"></i>
                    <span>Relatórios</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="user-profile">
        <div class="avatar" id="userAvatar">?</div>
        <div class="user-info">
            <div class="name" id="userName">Carregando...</div>
            <div class="role" id="userRole">—</div>
        </div>
    </div>
</aside>
