<?php
// /public/exportar_csv.php
require_once __DIR__ . '/../src/Autenticacao/ServicoLogin.php';
require_once __DIR__ . '/../src/Provedor/ServicoRelatorio.php';

ServicoLogin::verificarAcessoRestrito(1);
$idProvedor = $_SESSION['id_provedor'];

// Chamada estática corrigida
$dados = ServicoRelatorio::exportarRelatorioChamados($idProvedor);

// Cabeçalhos para download do arquivo CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=relatorio_chamados.csv');

$saida = fopen('php://output', 'w');
// Delimitador padrão em pt-BR com BOM UTF-8
fprintf($saida, chr(0xEF).chr(0xBB).chr(0xBF)); 
fputcsv($saida, ['ID Chamado', 'Cliente', 'Técnico', 'Assunto', 'Status', 'Data Abertura', 'Data Conclusão'], ';');

// Loop corrigido com a variável $linha definida corretamente
foreach ($dados as $linha) {
    fputcsv($saida, [
        $linha['id_chamado'],
        $linha['cliente'],
        $linha['tecnico'] ?? 'Não atribuído',
        $linha['assunto_chamado'],
        $linha['status_chamado'],
        $linha['data_abertura'],
        $linha['data_conclusao'] ?? '-'
    ], ';');
}

fclose($saida);
exit;
?>