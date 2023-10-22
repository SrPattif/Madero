<?php

/*
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['USER_ID'])) {
    http_response_code(403);
    exit();
}

*/

include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

if(!isset($_POST['telefone']) || !isset($_POST['operacao']) || !isset($_POST['alojamento']) || !isset($_POST['sindico'])) {
    http_response_code(403);
    exit();
}

$query = "SELECT * FROM formularios WHERE nome='sindicos';";
$result = mysqli_query($mysqli, $query);
if(mysqli_num_rows($result) != 1) {
    header('location: /');
    exit();
}
$formData = mysqli_fetch_assoc($result);

if($formData['status'] != 'ativo') {
    header("Content-Type: application/json");
    echo json_encode(array("gerado" => false, "msg" => "Este formulário está desativado."));
    http_response_code(400);
    exit();
}
    

$telefone = mysqli_real_escape_string($mysqli, $_POST['telefone']);
$operacao = mysqli_real_escape_string($mysqli, $_POST['operacao']);
$idAlojamento = mysqli_real_escape_string($mysqli, $_POST['alojamento']);
$idSindico = mysqli_real_escape_string($mysqli, $_POST['sindico']);

$codigoSolicitacao = uniqid();

$userIp = $_SERVER['REMOTE_ADDR'];

$query = "INSERT INTO solicitacoes_sindicos (`codigo_externo`, `telefone`, `operacao`, `id_alojamento`, `id_sindico`, `endereco_ip`) VALUES ('{$codigoSolicitacao}', '{$telefone}', '{$operacao}', {$idAlojamento}, {$idSindico}, '{$userIp}')";
$result = mysqli_query($mysqli, $query);

if ($result) {
    header("Content-Type: application/json");
    echo json_encode(array("gerado" => true, "codigo" => $codigoSolicitacao));
    http_response_code(200);
    exit();

} else {
    header("Content-Type: application/json");
    echo json_encode(array("gerado" => false, "msg" => "Erro ao executar a inserção."));
    http_response_code(400);
    exit();
}

?>