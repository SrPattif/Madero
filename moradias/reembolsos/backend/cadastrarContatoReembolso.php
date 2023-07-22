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

    if(!isset($_POST['id_alojamento']) || !isset($_POST['email']) || !isset($_POST['observacoes'])) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Existem valores não especificados."));
        http_response_code(401);
        exit();
    }

    $idAlojamento = mysqli_real_escape_string($mysqli, $_POST['id_alojamento']);
    $data_email = mysqli_real_escape_string($mysqli, $_POST['email']);
    $data_obs = mysqli_real_escape_string($mysqli, $_POST['observacoes']);

    $query = "INSERT INTO contatos_reembolso (`id_alojamento`, `email_reembolso`, `observacoes`, `usuario_inclusao`) VALUES ({$idAlojamento}, '{$data_email}', '{$data_obs}', {$userId});";
    $resultUpdate = mysqli_query($mysqli, $query);
            
    if($resultUpdate) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => true, "mensagem" =>  "Contato de reembolso cadastrado."));
        http_response_code(200);
        exit();

    } else {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Ocorreu um erro ao cadastrar o contato de reembolso."));
        http_response_code(500);
        exit();
    }

} else {
    http_response_code(405);
    exit();
}

?>