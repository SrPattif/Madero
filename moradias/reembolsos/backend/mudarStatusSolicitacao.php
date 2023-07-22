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

    if(!isset($_POST['id_alojamento']) || !isset($_POST['mes']) || !isset($_POST['ano']) || !isset($_POST['status'])) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Existem valores não especificados."));
        http_response_code(401);
        exit();
    }

    $idAlojamento = mysqli_real_escape_string($mysqli, $_POST['id_alojamento']);
    $mes = mysqli_real_escape_string($mysqli, $_POST['mes']);
    $ano = mysqli_real_escape_string($mysqli, $_POST['ano']);
    $status = mysqli_real_escape_string($mysqli, $_POST['status']);

    $statusReal = "NULL";
    $dataQuery = "";
    switch ($status) {
        case 'enviado':
            $statusReal = "'enviado'";
            $dataQuery = ", `data_envio`=CURRENT_TIMESTAMP()";
            break;

        case 'reembolsado':
            $statusReal = "'reembolsado'";
            $dataQuery = ", `data_reembolso`=CURRENT_TIMESTAMP()";
            break;
        
        default:
            $statusReal = "NULL";
            break;
    }

    $query = "UPDATE alojamentos_valores_reembolso SET `status`={$statusReal}{$dataQuery} WHERE  id_alojamento={$idAlojamento} AND mes={$mes} AND ano={$ano};";
    $resultUpdate = mysqli_query($mysqli, $query);
            
    if($resultUpdate) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => true, "mensagem" =>  "Status da solicitação atualizada."));
        http_response_code(200);
        exit();

    } else {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Ocorreu um erro ao executar a query de atualização."));
        http_response_code(500);
        exit();
    }

} else {
    http_response_code(405);
    exit();
}

?>