<?php
// /src/Provedor/ServicoPlano.php
require_once __DIR__ . '/../../config/conexao_banco.php';

class ServicoPlano {
    public static function listarPlanos($idProvedor) {
        $pdo = ConexaoBanco::obterConexao();
        $sql = "SELECT id_plano, nome_plano, velocidade_megas, valor_mensal, status_disponivel 
                FROM planos_internet 
                WHERE id_provedor = :id_provedor ORDER BY valor_mensal ASC";
        $comando = $pdo->prepare($sql);
        $comando->bindParam(':id_provedor', $idProvedor);
        $comando->execute();
        return $comando->fetchAll();
    }

    public static function criarPlano($idProvedor, $nome, $velocidade, $valor, $descricao) {
        $pdo = ConexaoBanco::obterConexao();
        $sql = "INSERT INTO planos_internet (id_provedor, nome_plano, velocidade_megas, valor_mensal, descricao_detalhada) 
                VALUES (:id_provedor, :nome, :velocidade, :valor, :descricao)";
        $comando = $pdo->prepare($sql);
        return $comando->execute([
            ':id_provedor' => $idProvedor,
            ':nome' => $nome,
            ':velocidade' => $velocidade,
            ':valor' => $valor,
            ':descricao' => $descricao
        ]);
    }
    // Obs: Métodos de Atualizar e Excluir seguiriam o mesmo padrão (omitidos por brevidade, mas a estrutura já suporta).
}
?>