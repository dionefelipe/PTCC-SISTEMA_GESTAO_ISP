<?php
// /src/Cliente/ServicoCliente.php
require_once __DIR__ . '/../../config/conexao_banco.php';

class ServicoCliente {
    
    public static function buscarPlanosPorBairro($bairroDestino) {
        $pdo = ConexaoBanco::obterConexao();
        $sql = "SELECT p.id_plano, p.nome_plano, p.velocidade_megas, p.valor_mensal, pr.razao_social, pr.id_provedor
                FROM planos_internet p
                INNER JOIN provedores pr ON p.id_provedor = pr.id_provedor
                INNER JOIN matriz_pesos_bairros m ON pr.id_provedor = m.id_provedor
                WHERE m.bairro_destino = :bairro AND p.status_disponivel = 1
                GROUP BY p.id_plano";
        
        $comando = $pdo->prepare($sql);
        $comando->bindParam(':bairro', $bairroDestino);
        $comando->execute();
        return $comando->fetchAll();
    }

    public static function buscarPlanosElegiveisPorRenda($bairroDestino, $rendaMensalBruta) {
        $pdo = ConexaoBanco::obterConexao();
        $limiteMaximoPlano = $rendaMensalBruta * 0.09;

        $sql = "SELECT p.id_plano, p.nome_plano, p.velocidade_megas, p.valor_mensal, pr.razao_social, pr.id_provedor
                FROM planos_internet p
                INNER JOIN provedores pr ON p.id_provedor = pr.id_provedor
                INNER JOIN matriz_pesos_bairros m ON pr.id_provedor = m.id_provedor
                WHERE m.bairro_destino = :bairro 
                  AND p.status_disponivel = 1
                  AND p.valor_mensal <= :limite_renda
                GROUP BY p.id_plano";
        
        $comando = $pdo->prepare($sql);
        $comando->execute([
            ':bairro' => $bairroDestino,
            ':limite_renda' => $limiteMaximoPlano
        ]);
        return $comando->fetchAll();
    }

    public static function buscarPlanosConcorrentesElegiveis($bairroDestino, $rendaMensalBruta) {
        $pdo = ConexaoBanco::obterConexao();
        $limiteMaximoPlano = $rendaMensalBruta * 0.09;

        $sql = "SELECT p.id_plano, p.nome_plano, p.velocidade_megas, p.valor_mensal, 
                       pr.razao_social, pr.id_provedor
                FROM planos_internet p
                INNER JOIN provedores pr ON p.id_provedor = pr.id_provedor
                INNER JOIN matriz_pesos_bairros m ON pr.id_provedor = m.id_provedor
                WHERE m.bairro_destino = :bairro 
                  AND p.status_disponivel = 1
                  AND p.valor_mensal <= :limite_renda
                GROUP BY p.id_plano
                ORDER BY p.valor_mensal ASC";
        
        $comando = $pdo->prepare($sql);
        $comando->execute([
            ':bairro' => $bairroDestino,
            ':limite_renda' => $limiteMaximoPlano
        ]);
        return $comando->fetchAll();
    }

    public static function cadastrarClienteVinculado($idProvedor, $idPlano, $nome, $cpf, $rendaMensalBruta, $cep, $email, $senha, $telefone, $bairro, $endereco, $latitude, $longitude) {
        $pdo = ConexaoBanco::obterConexao();
        
        try {
            $pdo->beginTransaction();

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sqlLogin = "INSERT INTO usuarios_sistema (id_perfil, id_provedor, email_login, senha_hash) 
                         VALUES (3, :id_provedor, :email, :senha)";
            $comandoLogin = $pdo->prepare($sqlLogin);
            $comandoLogin->execute([
                ':id_provedor' => $idProvedor, 
                ':email' => $email, 
                ':senha' => $senhaHash
            ]);
            $idUsuarioNovo = $pdo->lastInsertId();

            $sqlCliente = "INSERT INTO clientes (id_usuario, id_plano_contratado, nome_completo, cpf, renda_mensal_bruta, cep, telefone_celular, bairro, endereco_completo, latitude_geografica, longitude_geografica) 
                           VALUES (:id_usuario, :id_plano, :nome, :cpf, :renda, :cep, :telefone, :bairro, :endereco, :lat, :lng)";
            $comandoCliente = $pdo->prepare($sqlCliente);
            $comandoCliente->execute([
                ':id_usuario' => $idUsuarioNovo, 
                ':id_plano' => $idPlano, 
                ':nome' => $nome, 
                ':cpf' => $cpf, 
                ':renda' => $rendaMensalBruta,
                ':cep' => $cep, 
                ':telefone' => $telefone, 
                ':bairro' => $bairro, 
                ':endereco' => $endereco, 
                ':lat' => $latitude, 
                ':lng' => $longitude
            ]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }
}
?>