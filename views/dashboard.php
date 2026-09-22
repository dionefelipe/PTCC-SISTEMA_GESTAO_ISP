<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Integrada - Provedor Inclusão Digital</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body>
<style>
    body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            display: flex;
            background-color: #f4f7f6;
            color: #333;
        }
        #sidebar {
            width: 260px;
            background: #2c3e50;
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
        }
        #main-content {
            margin-left: 300px;
            flex-grow: 1;
            padding: 30px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-item {
            background: #3498db;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        #mapa {
            height: 450px;
            width: 100%;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #eee;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            color: #2c3e50;
        }
        .prioridade-alta {
            color: #e74c3c;
            font-weight: bold;
        }
        button {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 10px;
        }
        button:hover {
            background-color: #219150;
        }
        hr { border: 0; border-top: 1px solid #555; margin: 20px 0; }
    </style>

<!-- Chama a sidebar de forma fixa e centralizada -->
  <?php include __DIR__ . '/../sidebar.php'; ?>

<!-- Inicio da tela do dashboard-->
<div id="main-content">
    <h1>Painel de Controle Integrado</h1>

    <div class="stats-grid">
        <div class="stat-item">
            <h3>Clientes Mapeados</h3>
            <p id="total-clientes">Carregando...</p>
        </div>
        <div class="stat-item" style="background: #e67e22;">
            <h3>Chamados Abertos</h3>
            <p id="total-chamados">Carregando...</p>
        </div>
        <div class="stat-item" style="background: #27ae60;">
            <h3>Planos Disponíveis</h3>
            <p id="total-planos">Carregando...</p>
        </div>
    </div>

    <div class="card">
        <h2>📍 Mapa de Cobertura e Geolocalização</h2>
        <p>Visualização de clientes para priorização de atendimento e expansão de infraestrutura.</p>
        <div id="mapa"></div>
    </div>

    <div class="card">
        <h2>🛠️ Fila de Chamados Técnicos (Priorização Geográfica)</h2>
        <button onclick="carregarChamados()">Atualizar Fila</button>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Descrição do Problema</th>
                    <th>Prioridade</th>
                </tr>
            </thead>
            <tbody id="tabela-chamados">
                </tbody>
        </table>
    </div>

    <div class="card">
        <h2>📡 Planos de Internet Disponíveis</h2>
        <button onclick="carregarPlanos()">Listar Planos</button>
        <ul id="lista-planos">
            </ul>
    </div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // URL base do seu backend local
    const API_URL = "api/";
    let map;

    document.addEventListener("DOMContentLoaded", () => {
        inicializarMapa();
        carregarMapaClientes();
        carregarPlanos();
        carregarChamados();
    });

    function inicializarMapa() {
        map = L.map('mapa').setView([-23.5505, -46.6333], 12); 
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
    }

    async function carregarMapaClientes() {
        try {
            const response = await fetch(`${API_URL}/mapa_clientes.php`);
            const clientes = await response.json();
            
            if (clientes.error) {
                console.error("Erro da API:", clientes.error);
                return;
            }

            // Atualiza estatística do topo
            document.getElementById('total-clientes').innerText = clientes.length;

            clientes.forEach(cliente => {
                if (cliente.latitude && cliente.longitude) {
                    L.marker([cliente.latitude, cliente.longitude])
                        .addTo(map)
                        .bindPopup(`<b>${cliente.nome}</b><br>Status: Ativo`);
                }
            });
        } catch (error) {
            console.error('Erro ao carregar mapa:', error);
            document.getElementById('total-clientes').innerText = '0';
        }
    }

    async function carregarPlanos() {
        try {
            const response = await fetch(`${API_URL}/planos.php`);
            const result = await response.json();
            
            const lista = document.getElementById('lista-planos');
            if (!lista) return;

            lista.innerHTML = ''; 
            const planos = result.dados ? result.dados : result;

            // Atualiza estatística do topo
            document.getElementById('total-planos').innerText = planos.length;

            planos.forEach(plano => {
                const li = document.createElement('li');
                li.textContent = `${plano.nome} - ${plano.velocidade} (R$ ${plano.valor})`;
                lista.appendChild(li);
            });
        } catch (error) {
            console.error("Erro ao carregar planos:", error);
            document.getElementById('total-planos').innerText = '0';
        }
    }

    async function carregarChamados() {
        try {
            const response = await fetch(`${API_URL}/chamados.php`);
            const result = await response.json();
            
            const tabela = document.getElementById('tabela-chamados');
            if (!tabela) return;

            tabela.innerHTML = ''; 
            const chamados = result.dados ? result.dados : result;

            // Atualiza estatística do topo
            document.getElementById('total-chamados').innerText = chamados.length;

            chamados.forEach(chamado => {
                const tr = document.createElement('tr');
                
                const isAlta = chamado.area_vulnerabilidade == 1 || chamado.prioridade_social_alta == 1;
                const classePrioridade = isAlta ? 'prioridade-alta' : '';
                const textoPrioridade = isAlta ? 'ALTA (Inclusão)' : chamado.prioridade || 'Normal';
                
                tr.innerHTML = `
                    <td>${chamado.id}</td>
                    <td>${chamado.cliente_nome || chamado.cliente || 'Desconhecido'}</td>
                    <td>${chamado.descricao}</td>
                    <td class="${classePrioridade}">${textoPrioridade}</td>
                `;
                tabela.appendChild(tr);
            });
        } catch (error) {
            console.error("Erro ao carregar chamados:", error);
            document.getElementById('total-chamados').innerText = '0';
        }
    }
</script>

</body>
</html>