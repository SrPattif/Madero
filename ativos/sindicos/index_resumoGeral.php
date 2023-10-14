<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['USER_ID'])) {
    exit();
}

include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

$month = date('n');
$year = date('Y');

// Consulta para obter todas as métricas em uma única consulta
$query = "
    SELECT
        (SELECT COUNT(id) FROM alojamentos WHERE id_sindico IS NOT NULL) AS sindicos_cadastrados,
        (SELECT COUNT(id) FROM solicitacoes_sindicos) AS qtde_solicitacoes,
        (SELECT COUNT(id) FROM solicitacoes_sindicos WHERE MONTH(criado_em)={$month}) AS qtde_solicitacoes_mes";

$result = mysqli_query($mysqli, $query);
$row = mysqli_fetch_assoc($result);

$retorno = array(
    "sindicos_cadastrados" => intval($row['sindicos_cadastrados']),
    "qtde_solicitacoes" => intval($row['qtde_solicitacoes']),
    "qtde_solicitacoes_mes" => intval($row['qtde_solicitacoes_mes'])
);

header("Content-Type: application/json");
echo json_encode($retorno);
http_response_code(200);
exit();
?>
