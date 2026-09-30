<?php
// /src/Provedor/ServicoRelatorio.php
require_once __DIR__ . '/../../config/conexao_banco.php';

class ServicoRelatorio {
    
    // Alterado para public static para manter o padrão KISS/DRY dos outros serviços
    public static function exportarRelatorioChamados($idProvedor) {
        $pdo = ConexaoBanco::obterConexao();
        $sql = "SELECT c.id_chamado, cl.nome_completo as cliente, t.nome_completo as tecnico, 
                       c.assunto_chamado, c.status_chamado, c.data_abertura, c.data_conclusao
                FROM chamados_suporte c
                INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
                LEFT JOIN tecnicos t ON c.id_tecnico_atribuido = t.id_tecnico
                WHERE c.id_provedor = :id_provedor
                ORDER BY c.data_abertura DESC";
        $comando = $pdo->prepare($sql);
        $comando->execute([':id_provedor' => $idProvedor]);
        return $comando->fetchAll();
    }

    public static function obterCoordenadasClientes($idProvedor) {
        $pdo = ConexaoBanco::obterConexao();
        $sql = "SELECT cl.nome_completo, cl.endereco_completo, cl.bairro, cl.latitude_geografica, cl.longitude_geografica, p.nome_plano
                FROM clientes cl
                INNER JOIN planos_internet p ON cl.id_plano_contratado = p.id_plano
                WHERE p.id_provedor = :id_provedor AND cl.latitude_geografica IS NOT NULL";
        $comando = $pdo->prepare($sql);
        $comando->execute([':id_provedor' => $idProvedor]);
        return $comando->fetchAll();
    }
}
?>