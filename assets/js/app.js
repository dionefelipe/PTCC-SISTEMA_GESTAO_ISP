// URL base do seu backend local
const API_URL = "api/";

// Variável global do mapa
let map;

// Aguarda o HTML carregar completamente antes de rodar os scripts
document.addEventListener("DOMContentLoaded", () => {
    inicializarMapa();
    
    // Dispara as requisições para preencher o Dashboard
    carregarMapaClientes();
    carregarPlanos();
    carregarChamados();
});

function inicializarMapa() {
    // 1. Inicializa o mapa (centralizado em SP)
    map = L.map('mapa').setView([-23.5505, -46.6333], 12); 

    // 2. Adiciona a camada visual (OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
}

// 3. Busca os dados dos clientes na sua API PHP e plota no mapa
async function carregarMapaClientes() {
    try {
        const response = await fetch(`${API_URL}/mapa_clientes.php`);
        const clientes = await response.json();
        
        if (clientes.error) {
            console.error("Erro da API:", clientes.error);
            return;
        }

        clientes.forEach(cliente => {
            // Só adiciona se o cliente tiver coordenadas salvas no banco
            if (cliente.latitude && cliente.longitude) {
                L.marker([cliente.latitude, cliente.longitude])
                    .addTo(map)
                    .bindPopup(`<b>Cliente:</b> ${cliente.nome}<br><b>Status:</b> Ativo`);
            }
        });
    } catch (error) {
        console.error('Erro ao carregar mapa:', error);
    }
}

// Consome o endpoint planos.php
async function carregarPlanos() {
    try {
        const response = await fetch(`${API_URL}/planos.php`);
        const result = await response.json();
        
        const lista = document.getElementById('lista-planos');
        if (!lista) return;

        lista.innerHTML = ''; // Limpa a lista
        
        // Verifica se a sua API retorna um array direto ou um objeto { dados: [...] }
        const planos = result.dados ? result.dados : result;

        planos.forEach(plano => {
            const li = document.createElement('li');
            li.textContent = `${plano.nome} - ${plano.velocidade} (R$ ${plano.valor})`;
            lista.appendChild(li);
        });
    } catch (error) {
        console.error("Erro ao carregar planos:", error);
    }
}

// Consome o endpoint chamados.php mostrando a prioridade geográfica
async function carregarChamados() {
    try {
        const response = await fetch(`${API_URL}/chamados.php`);
        const result = await response.json();
        
        const tabela = document.getElementById('tabela-chamados');
        if (!tabela) return;

        tabela.innerHTML = ''; 
        
        const chamados = result.dados ? result.dados : result;

        chamados.forEach(chamado => {
            const tr = document.createElement('tr');
            
            // Destaca visualmente se a prioridade for alta (inclusão digital)
            // Compatível com 'area_vulnerabilidade' ou 'prioridade_social_alta'
            const isAlta = chamado.area_vulnerabilidade == 1 || chamado.prioridade_social_alta == 1;
            const classePrioridade = isAlta ? 'prioridade-alta' : '';
            const textoPrioridade = isAlta ? 'ALTA (Inclusão)' : chamado.prioridade || 'Normal';
            
            tr.innerHTML = `
                <td>${chamado.id}</td>
                <td>${chamado.cliente_nome || chamado.cliente}</td>
                <td>${chamado.descricao}</td>
                <td class="${classePrioridade}">${textoPrioridade}</td>
            `;
            tabela.appendChild(tr);
        });
    } catch (error) {
        console.error("Erro ao carregar chamados:", error);
    }
}
// Lógica de Cadastro
const cadastroForm = document.getElementById('cadastroForm');
if(cadastroForm) {
cadastroForm.addEventListener('submit', async (e) => {
e.preventDefault();
const dados = {
nome: document.getElementById('nome').value,
email: document.getElementById('email').value,
senha: document.getElementById('senha').value,
latitude: document.getElementById('lat').value,
longitude: document.getElementById('lng').value,
plano_id: 1 // Definindo plano padrão para o MVP
};
try {
const res = await fetch(API_URL + 'cadastrar_cliente.php', {
method: 'POST',
headers: { 'Content-Type': 'application/json' },
body: JSON.stringify(dados)
});
const info = await res.json();
const msg = document.getElementById('msgFeedback');
if(res.ok) {
msg.style.color = "green";
msg.innerText = info.mensagem;
setTimeout(() => window.location.href = 'index.html', 2000);
} else {
msg.style.color = "red";
msg.innerText = info.mensagem;
}
} catch(error) {
alert("Erro na conexão com o servidor.");
}
});
}