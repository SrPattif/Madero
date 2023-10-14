<?php

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['USER_ID'])) {
    header("Content-Type: application/json");
    echo json_encode(array("sucesso" => false, "mensagem" =>  "Usuário não autorizado."));
    http_response_code(401);
    exit();
}
$userId = $_SESSION['USER_ID'];
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if(!isset($_POST['id_solicitacao']) || !isset($_POST['aprovado'])) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Existem valores não especificados."));
        http_response_code(400);
        exit();
    }

    $idSolicitacao = mysqli_real_escape_string($mysqli, $_POST['id_solicitacao']);
    $aprovado = filter_var($_POST['aprovado'], FILTER_VALIDATE_BOOLEAN);

    if($aprovado == true) {
        $querySolicitacao = "SELECT ss.*, a.id_sindico AS id_sindico_antigo FROM solicitacoes_sindicos ss LEFT JOIN alojamentos a ON ss.id_alojamento=a.id WHERE ss.id={$idSolicitacao};";
        $rowsSolicitacao = array();
        $resultSolicitacao = mysqli_query($mysqli, $querySolicitacao);
        if(mysqli_num_rows($resultSolicitacao) != 1) {
            header("Content-Type: application/json");
            echo json_encode(array("sucesso" => false, "mensagem" => "Múltiplas solicitações encontradas."));
            http_response_code(400);
            exit();
        }
        $dadosSolicitacao = mysqli_fetch_assoc($resultSolicitacao);
    
        $idSindico = $dadosSolicitacao['id_sindico'];
        $idAlojamento = $dadosSolicitacao['id_alojamento'];
        $idSindicoAntigo = "NULL";
        if(isset($dadosSolicitacao['id_sindico_antigo']) && !empty($dadosSolicitacao['id_sindico_antigo'])) {
            $idSindicoAntigo = $dadosSolicitacao['id_sindico_antigo'];
        }

        $query = "UPDATE alojamentos SET `id_sindico`='{$idSindico}', `data_alteracao_sindico`=CURRENT_TIMESTAMP() WHERE `id`={$idAlojamento};";
        $resultUpdate = mysqli_query($mysqli, $query);
                
        if($resultUpdate) {
            $querySolicitacao = "UPDATE solicitacoes_sindicos SET `situacao`='aprovado', `revisado_em`=CURRENT_TIMESTAMP(), `id_revisor`={$userId} WHERE `id`={$idSolicitacao};";
            $resultSolicitacao = mysqli_query($mysqli, $querySolicitacao);

            if($resultSolicitacao) {
                $insertHistorico = "INSERT INTO historico_sindicos (id_alojamento, id_sindico_antigo, id_sindico) VALUES ({$idAlojamento}, {$idSindicoAntigo}, {$idSindico});";
                $resultHistorico = mysqli_query($mysqli, $insertHistorico);

                if($resultHistorico) {
                    header("Content-Type: application/json");
                    echo json_encode(array("sucesso" => true, "mensagem" =>  "A solicitação para troca de síndico foi aprovada.", "id_solicitacao" => $idSolicitacao));
                    http_response_code(200);
                    exit();

                } else {
                    header("Content-Type: application/json");
                    echo json_encode(array("sucesso" => false, "mensagem" =>  "Ocorreu um erro ao aprovar a solicitação."));
                    http_response_code(500);
                    exit();
                }

            } else {
                header("Content-Type: application/json");
                echo json_encode(array("sucesso" => false, "mensagem" =>  "Ocorreu um erro ao aprovar a solicitação."));
                http_response_code(500);
                exit();
            }

        } else {
            header("Content-Type: application/json");
            echo json_encode(array("sucesso" => false, "mensagem" =>  "Ocorreu um erro ao aprovar a solicitação."));
            http_response_code(500);
            exit();
        }

    } else if($aprovado == false) {
        $querySolicitacao = "UPDATE solicitacoes_sindicos SET `situacao`='rejeitado', `revisado_em`=CURRENT_TIMESTAMP(), `id_revisor`={$userId} WHERE `id`={$idSolicitacao};";
        $resultSolicitacao = mysqli_query($mysqli, $querySolicitacao);

        if($resultSolicitacao) {
            header("Content-Type: application/json");
            echo json_encode(array("sucesso" => true, "mensagem" =>  "A solicitação para troca de síndico foi recusada.", "id_solicitacao" => $idSolicitacao));
            http_response_code(200);
            exit();

        } else {
            header("Content-Type: application/json");
            echo json_encode(array("sucesso" => false, "mensagem" =>  "Ocorreu um erro ao recusar a solicitação."));
            http_response_code(500);
            exit();
        }
    }

} else {
    http_response_code(405);
    exit();
}

?>