<?php
// /src/Provedor/ServicoTecnico.php
require_once __DIR__ . '/../../config/conexao_banco.php';

class ServicoTecnico {
    public static function listarTecnicos($idProvedor) {
        $pdo = ConexaoBanco::obterConexao();
        // JOIN limpo para trazer os dados de login e do técnico
        $sql = "SELECT t.id_tecnico, t.nome_completo, t.matricula_trabalhista, t.status_disponibilidade, u.email_login 
                FROM tecnicos t
                INNER JOIN usuarios_sistema u ON t.id_usuario = u.id_usuario
                WHERE u.id_provedor = :id_provedor";
        $comando = $pdo->prepare($sql);
        $comando->bindParam(':id_provedor', $idProvedor);
        $comando->execute();
        return $comando->fetchAll();
    }

    public static function criarTecnico($idProvedor, $nome, $email, $senha, $matricula, $telefone) {
        $pdo = ConexaoBanco::obterConexao();
        
        try {
            $pdo->beginTransaction();

            // 1. Cria o login (Perfil 2 = Técnico)
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sqlLogin = "INSERT INTO usuarios_sistema (id_perfil, id_provedor, email_login, senha_hash) 
                         VALUES (2, :id_provedor, :email, :senha)";
            $comandoLogin = $pdo->prepare($sqlLogin);
            $comandoLogin->execute([
                ':id_provedor' => $idProvedor, ':email' => $email, ':senha' => $senhaHash
            ]);
            
            $idUsuarioNovo = $pdo->lastInsertId();

            // 2. Cria o perfil do técnico
            $sqlTecnico = "INSERT INTO tecnicos (id_usuario, nome_completo, matricula_trabalhista, telefone_celular) 
                           VALUES (:id_usuario, :nome, :matricula, :telefone)";
            $comandoTecnico = $pdo->prepare($sqlTecnico);
            $comandoTecnico->execute([
                ':id_usuario' => $idUsuarioNovo, ':nome' => $nome, 
                ':matricula' => $matricula, ':telefone' => $telefone
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