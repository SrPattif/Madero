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

    if(!isset($_POST['usuario']) || !isset($_POST['email']) || !isset($_POST['nome']) || !isset($_POST['setor']) || !isset($_POST['cargo'])) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Existem valores não especificados."));
        http_response_code(401);
        exit();
    }

    $usuario = mysqli_real_escape_string($mysqli, $_POST['usuario']);
    $nome = mysqli_real_escape_string($mysqli, $_POST['nome']);
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);
    $setor = mysqli_real_escape_string($mysqli, $_POST['setor']);
    $cargo = mysqli_real_escape_string($mysqli, $_POST['cargo']);

    $query = "INSERT INTO usuarios (`username`, `nome`, `password`, `email`, `setor`, `cargo`, `bloqueado`, `troca_senha`) VALUES ('{$usuario}', '{$nome}', MD5('madero123'), '{$email}', '{$setor}', '{$cargo}', 0, 1);";
    $resultUpdate = mysqli_query($mysqli, $query);
            
    if($resultUpdate) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => true, "mensagem" =>  "Usuário cadastrado."));
        http_response_code(200);
        exit();

    } else {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Ocorreu um erro ao cadastrar o usuário."));
        http_response_code(500);
        exit();
    }

} else {
    http_response_code(405);
    exit();
}

?>