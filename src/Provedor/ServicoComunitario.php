<?php
// /src/Provedor/ServicoComunitario.php
require_once __DIR__ . '/../../config/conexao_banco.php';

class ServicoComunitario {
    
    public static function listarPontos($idProvedor) {
        $pdo = ConexaoBanco::obterConexao();
        $sql = "SELECT * FROM pontos_acesso_comunitario WHERE id_provedor = :provedor";
        $cmd = $pdo->prepare($sql);
        $cmd->execute([':provedor' => $idProvedor]);
        return $cmd->fetchAll();
    }

    public static function cadastrarPonto($idProvedor, $nomeLocal, $cep, $bairro, $endereco, $lat, $lng) {
        $pdo = ConexaoBanco::obterConexao();
        $sql = "INSERT INTO pontos_acesso_comunitario (id_provedor, nome_local, cep, bairro, endereco, latitude_geografica, longitude_geografica) 
                VALUES (:provedor, :nome, :cep, :bairro, :endereco, :lat, :lng)";
        $cmd = $pdo->prepare($sql);
        return $cmd->execute([
            ':provedor' => $idProvedor,
            ':nome' => $nomeLocal,
            ':cep' => $cep,
            ':bairro' => $bairro,
            ':endereco' => $endereco,
            ':lat' => $lat,
            ':lng' => $lng
        ]);
    }

    public static function excluirPonto($idPonto, $idProvedor) {
        $pdo = ConexaoBanco::obterConexao();
        $sql = "DELETE FROM pontos_acesso_comunitario WHERE id_ponto = :id AND id_provedor = :provedor";
        $cmd = $pdo->prepare($sql);
        return $cmd->execute([
            ':id' => $idPonto, 
            ':provedor' => $idProvedor
        ]);
    }
}
?>