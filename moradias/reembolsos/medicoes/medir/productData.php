<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['USER_ID'])) {
    http_response_code(401);
    echo "Não Autorizado";
    exit();
}

include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');
require($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

if(!isset($_GET['idProduto'])) {
    header("Content-Type: application/json");
    echo json_encode(array("sucesso" => false, "descricao_erro" => "Taxa não especificada"));
    http_response_code(400);
    exit();
}

$idTaxa = mysqli_real_escape_string($mysqli, $_GET['idProduto']);

$queryTaxas = "SELECT * FROM tipos_taxas WHERE id = {$idTaxa};";
$resultTaxas = mysqli_query($mysqli, $queryTaxas);
if(mysqli_num_rows($resultTaxas) != 1) {
    header("Content-Type: application/json");
    echo json_encode(array("sucesso" => false, "descricao_erro" => "Múltiplas ou nenhuma taxa encontradas."));
    http_response_code(400);
    exit();
}
$dadosTaxa = mysqli_fetch_assoc($resultTaxas);

header("Content-Type: application/json");
echo json_encode(array("sucesso" => true, "dadosProduto" => array("id" => $dadosTaxa['id'], "codigoProtheus" => $dadosTaxa['codigo_protheus'], "nomeTaxa" => $dadosTaxa['description'], "reembolsavel" => boolval($dadosTaxa['refundable']))));
http_response_code(200);
exit();

?>