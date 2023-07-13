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

include('../libs/databaseConnection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if(!isset($_POST['id_alojamento']) || !isset($_POST['endereco']) || !isset($_POST['contrato']) || !isset($_POST['digito_financeiro']) || !isset($_POST['status']) || !isset($_POST['centro_custo']) || !isset($_POST['operacao'])) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Existem valores não especificados."));
        http_response_code(401);
        exit();
    }

    $idAlojamento = mysqli_real_escape_string($mysqli, $_POST['id_alojamento']);
    $data_endereco = mysqli_real_escape_string($mysqli, $_POST['endereco']);
    $data_contrato = mysqli_real_escape_string($mysqli, $_POST['contrato']);
    $data_digitoFinanceiro = mysqli_real_escape_string($mysqli, $_POST['digito_financeiro']);
    $data_status = mysqli_real_escape_string($mysqli, $_POST['status']);
    $data_centroCusto = mysqli_real_escape_string($mysqli, $_POST['centro_custo']);
    $data_operacao = mysqli_real_escape_string($mysqli, $_POST['operacao']);

    $query = "UPDATE alojamentos SET `endereco`='{$data_endereco}', `contrato_totvs`='{$data_contrato}', `digito_financeiro`='{$data_digitoFinanceiro}', `status`='{$data_status}', `centro_custo`='{$data_centroCusto}', `operacao`='{$data_operacao}' WHERE  `id`={$idAlojamento};";
    $resultUpdate = mysqli_query($mysqli, $query);
            
    if($resultUpdate) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => true, "mensagem" =>  "Os dados da moradia foram atualizados."));
        http_response_code(200);
        exit();

    } else {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Ocorreu um erro ao atualizar os dados da moradia."));
        http_response_code(500);
        exit();
    }

} else {
    http_response_code(405);
    exit();
}

?>