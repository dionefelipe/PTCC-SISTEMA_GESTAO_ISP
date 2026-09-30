<?php
// /config/conexao_banco.php

class ConexaoBanco {
    private static $instancia = null;

    public static function obterConexao() {
        if (self::$instancia === null) {
            try {
                // Atualizado para o nome do banco: tccbd
                $dsn = "mysql:host=localhost;dbname=tccbd;charset=utf8mb4";
                $usuario_db = "root"; 
                $senha_db = ""; 

                self::$instancia = new PDO($dsn, $usuario_db, $senha_db);
                self::$instancia->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instancia->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $erro) {
                die("Erro de Conexão com o Banco: " . $erro->getMessage());
            }
        }
        return self::$instancia;
    }
}
?>