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
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if(!isset($_POST['id_formulario']) || !isset($_POST['titulo']) || !isset($_POST['status'])) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Existem valores não especificados."));
        http_response_code(401);
        exit();
    }

    $idFormulario = mysqli_real_escape_string($mysqli, $_POST['id_formulario']);
    $data_titulo = mysqli_real_escape_string($mysqli, $_POST['titulo']);
    $data_status = mysqli_real_escape_string($mysqli, $_POST['status']);

    if($data_status != "ativo" && $data_status != "desativado") {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Valor invalido especificado para o campo status."));
        http_response_code(500);
        exit();
    }

    $query = "UPDATE formularios SET `titulo`='{$data_titulo}', `status`='{$data_status}' WHERE  `id`={$idFormulario};";
    $resultUpdate = mysqli_query($mysqli, $query);
            
    if($resultUpdate) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => true, "mensagem" =>  "Os dados do formulário foram atualizados."));
        http_response_code(200);
        exit();

    } else {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Ocorreu um erro ao atualizar os dados do formulário."));
        http_response_code(500);
        exit();
    }

} else {
    http_response_code(405);
    exit();
}

?>