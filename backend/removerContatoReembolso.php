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

    if(!isset($_POST['id_contato'])) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Existem valores não especificados."));
        http_response_code(401);
        exit();
    }

    $idContato = mysqli_real_escape_string($mysqli, $_POST['id_contato']);

    $query = "DELETE FROM `contatos_reembolso` WHERE  `id`={$idContato};";
    $resultUpdate = mysqli_query($mysqli, $query);
            
    if($resultUpdate) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => true, "mensagem" =>  "Contato removido com sucesso."));
        http_response_code(200);
        exit();

    } else {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Ocorreu um erro ao remover o contato de reembolso."));
        http_response_code(500);
        exit();
    }

} else {
    http_response_code(405);
    exit();
}

?>