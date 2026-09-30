<?php
// Adição ao /src/Tecnico/ServicoRoteamento.php para suporte a Zonas de Prioridade Social

class ServicoRoteamentoSocial {

    /**
     * Identifica chamados pendentes em zonas remotas/vulneráveis (peso > 20)
     * para permitir o despacho em lote (Batching Técnico), otimizando custos e tempo.
     */
    public static function listarChamadosZonaPrioritaria($idProvedor) {
        $pdo = ConexaoBanco::obterConexao();
        
        $sql = "SELECT c.id_chamado, c.assunto_chamado, c.descricao_problema, cl.bairro, cl.endereco_completo, m.peso_deslocamento
                FROM chamados_suporte c
                INNER JOIN clientes cl ON c.id_cliente = cl.id_cliente
                INNER JOIN matriz_pesos_bairros m ON c.id_provedor = m.id_provedor AND cl.bairro = m.bairro_destino
                WHERE c.id_provedor = :provedor 
                  AND c.status_chamado = 'Aberto'
                  AND m.peso_deslocamento > 20
                ORDER BY m.peso_deslocamento DESC";
                
        $cmd = $pdo->prepare($sql);
        $cmd->execute([':provedor' => $idProvedor]);
        return $cmd->fetchAll();
    }
}
?>