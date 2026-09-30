<?php
// /src/Autenticacao/ServicoLogin.php
require_once __DIR__ . '/../../config/conexao_banco.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ServicoLogin {

    public static function autenticar($email, $senha) {
        $pdo = ConexaoBanco::obterConexao();
        
        $sql = "SELECT * FROM usuarios_sistema WHERE email_login = :email";
        $comando = $pdo->prepare($sql);
        $comando->execute([':email' => $email]);
        $usuario = $comando->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['id_perfil'] = $usuario['id_perfil'];
            $_SESSION['id_provedor'] = $usuario['id_provedor'];
            $_SESSION['email_login'] = $usuario['email_login'];
            return true;
        }

        return false;
    }

    public static function verificarAcessoRestrito($perfilEsperado) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_perfil'] != $perfilEsperado) {
            header('Location: index.php');
            exit;
        }
    }
}
?>