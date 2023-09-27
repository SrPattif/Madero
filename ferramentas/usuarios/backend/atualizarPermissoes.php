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

    if(!isset($_POST['idUsuario']) || !isset($_POST['modulos'])) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Campos obrigatórios faltantes."));
        http_response_code(400);
        exit();
    }

    $idUsuario = mysqli_real_escape_string($mysqli, $_POST['idUsuario']);
    $listaModulos = $_POST['modulos'];

    if(sizeof($listaModulos) < 1) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Campos obrigatórios faltantes."));
        http_response_code(400);
        exit();
    }

    $queryModulos = "SELECT * FROM modulos;";
    $resultModulos = mysqli_query($mysqli, $queryModulos);
    $rowsModulos = array();
    while ($row = mysqli_fetch_array($resultModulos)) {
        array_push($rowsModulos, $row);
    }

    $modulos = array();
    $modulosPermitidos = array();
    foreach($rowsModulos as $modulo) {
        $idModulo = $modulo['id'];
        array_push($modulos, $idModulo);
    }

    foreach ($listaModulos as $moduloUsuario) {

        if(!in_array($moduloUsuario, $modulos)) {
            header("Content-Type: application/json");
            echo json_encode(array("sucesso" => false, "mensagem" =>  "Módulo " . $moduloUsuario . " desconhecido."));
            http_response_code(400);
            exit();

        } else {
            array_push($modulosPermitidos, mysqli_real_escape_string($mysqli, $moduloUsuario));
        }
    }

    $queryRemovedor = "DELETE FROM permissoes WHERE id_usuario={$idUsuario};";
    $resultRemovedor = mysqli_query($mysqli, $queryRemovedor);

    if($resultRemovedor) {

        $adicionarValues = array();
        foreach ($modulosPermitidos as $idModulo) {
            array_push($adicionarValues, "('{$idUsuario}', '{$idModulo}')");
        }

        $values = implode(",", $adicionarValues);
        $queryAdicionar = "INSERT INTO permissoes (`id_usuario`, `id_modulo`) VALUES {$values};";
        $resultAdicionar = mysqli_query($mysqli, $queryAdicionar);

        if($resultAdicionar) {
            header("Content-Type: application/json");
            echo json_encode(array("sucesso" => true, "mensagem" =>  "As permissões do usuário foram atualizadas."));
            http_response_code(200);
            exit();
        }
    }
}