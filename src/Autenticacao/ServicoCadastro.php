<?php
// /src/Autenticacao/ServicoCadastro.php
require_once __DIR__ . '/../../config/conexao_banco.php';

class ServicoCadastro {

    public static function listarProvedores() {
        $pdo = ConexaoBanco::obterConexao();
        return $pdo->query("SELECT id_provedor, razao_social FROM provedores ORDER BY razao_social ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    // Registo de Provedor
    public static function registarProvedor($nomeSocial, $cnpj, $cep, $locaisAtendimento, $email, $senha) {
        $pdo = ConexaoBanco::obterConexao();
        try {
            $pdo->beginTransaction();

            $sqlProv = "INSERT INTO provedores (razao_social, cnpj_provedor, cep) VALUES (:nome, :cnpj, :cep)";
            $cmdProv = $pdo->prepare($sqlProv);
            $cmdProv->execute([':nome' => $nomeSocial, ':cnpj' => $cnpj, ':cep' => $cep]);
            $idProvedorNovo = $pdo->lastInsertId();

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sqlUser = "INSERT INTO usuarios_sistema (id_perfil, id_provedor, email_login, senha_hash) VALUES (1, :provedor, :email, :senha)";
            $cmdUser = $pdo->prepare($sqlUser);
            $cmdUser->execute([':provedor' => $idProvedorNovo, ':email' => $email, ':senha' => $senhaHash]);

            if (!empty($locaisAtendimento)) {
                $bairros = explode(',', $locaisAtendimento);
                $sqlMatriz = "INSERT INTO matriz_pesos_bairros (id_provedor, bairro_destino, peso_deslocamento) VALUES (:provedor, :bairro, 10)";
                $cmdMatriz = $pdo->prepare($sqlMatriz);
                foreach ($bairros as $bairro) {
                    $bairroLimpo = trim($bairro);
                    if (!empty($bairroLimpo)) {
                        $cmdMatriz->execute([':provedor' => $idProvedorNovo, ':bairro' => $bairroLimpo]);
                    }
                }
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    // Registo de Técnico
    public static function registarTecnico($idProvedor, $emailInstitucional, $senha) {
        $pdo = ConexaoBanco::obterConexao();
        try {
            $pdo->beginTransaction();

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sqlUser = "INSERT INTO usuarios_sistema (id_perfil, id_provedor, email_login, senha_hash) VALUES (2, :provedor, :email, :senha)";
            $cmdUser = $pdo->prepare($sqlUser);
            $cmdUser->execute([':provedor' => $idProvedor, ':email' => $emailInstitucional, ':senha' => $senhaHash]);
            $idUsuarioNovo = $pdo->lastInsertId();

            $sqlTec = "INSERT INTO tecnicos (id_usuario, id_provedor, nome_tecnico, telefone) VALUES (:usuario, :provedor, 'Técnico Operacional', '(11) 90000-0000')";
            $cmdTec = $pdo->prepare($sqlTec);
            $cmdTec->execute([':usuario' => $idUsuarioNovo, ':provedor' => $idProvedor]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    // Registo de Cliente (Corrigido o erro de sintaxe em $cmdP)
    public static function registarCliente($nome, $cpf, $cep, $rendaBruta, $email, $senha, $bairro, $endereco, $lat, $lng) {
        $pdo = ConexaoBanco::obterConexao();
        try {
            $pdo->beginTransaction();

            $sqlPlano = "SELECT p.id_plano, pr.id_provedor FROM planos_internet p 
                         INNER JOIN provedores pr ON p.id_provedor = pr.id_provedor
                         INNER JOIN matriz_pesos_bairros m ON pr.id_provedor = m.id_provedor
                         WHERE m.bairro_destino = :bairro AND p.valor_mensal <= :limite LIMIT 1";
            $cmdP = $pdo->prepare($sqlPlano);
            $cmdP->execute([':bairro' => $bairro, ':limite' => ($rendaBruta * 0.09)]);
            $planoElegivel = $cmdP->fetch(PDO::FETCH_ASSOC) ?? null;

            if (!$planoElegivel) {
                $sqlFallback = "SELECT p.id_plano, pr.id_provedor FROM planos_internet p 
                                INNER JOIN provedores pr ON p.id_provedor = pr.id_provedor
                                INNER JOIN matriz_pesos_bairros m ON pr.id_provedor = m.id_provedor
                                WHERE m.bairro_destino = :bairro LIMIT 1";
                $cmdF = $pdo->prepare($sqlFallback);
                $cmdF->execute([':bairro' => $bairro]);
                $planoElegivel = $cmdF->fetch(PDO::FETCH_ASSOC);
            }

            if (!$planoElegivel) {
                $planoElegivel = $pdo->query("SELECT id_plano, id_provedor FROM planos_internet WHERE status_disponivel = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            }

            $idPlano = $planoElegivel['id_plano'] ?? 1;
            $idProvedor = $planoElegivel['id_provedor'] ?? 1;

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sqlUser = "INSERT INTO usuarios_sistema (id_perfil, id_provedor, email_login, senha_hash) VALUES (3, :provedor, :email, :senha)";
            $cmdUser = $pdo->prepare($sqlUser);
            $cmdUser->execute([':provedor' => $idProvedor, ':email' => $email, ':senha' => $senhaHash]);
            $idUsuarioNovo = $pdo->lastInsertId();

            $sqlCli = "INSERT INTO clientes (id_usuario, id_plano_contratado, nome_completo, cpf, renda_mensal_bruta, cep, bairro, endereco_completo, latitude_geografica, longitude_geografica) 
                       VALUES (:usuario, :plano, :nome, :cpf, :renda, :cep, :bairro, :end, :lat, :lng)";
            $cmdCli = $pdo->prepare($sqlCli);
            $cmdCli->execute([
                ':usuario' => $idUsuarioNovo, ':plano' => $idPlano, ':nome' => $nome, ':cpf' => $cpf,
                ':renda' => $rendaBruta, ':cep' => $cep, ':bairro' => $bairro, ':end' => $endereco, 
                ':lat' => (!empty($lat) ? $lat : -23.550520), 
                ':lng' => (!empty($lng) ? $lng : -46.633309)
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