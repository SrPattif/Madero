<?php

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['USER_ID'])) {
    header('location: /login/');
    exit();
}

include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

$meses = array(
    '1',
    '2',
    '3',
    '4',
    '5',
    '6',
    '7',
    '8',
    '9',
    '10',
    '11',
    '12'
);

$anos = array(
    2023 => array_fill_keys($meses, 0.0),
    2024 => array_fill_keys($meses, 0.0)
);

$query = "SELECT YEAR(ss.criado_em) AS ano, MONTH(ss.criado_em) AS mes, COUNT(ss.id) AS qtde_solicitacoes FROM solicitacoes_sindicos ss GROUP BY YEAR(ss.criado_em), MONTH(ss.criado_em);";
$result = mysqli_query($mysqli, $query);
$row = mysqli_num_rows($result);
$rows = array();
while($row = mysqli_fetch_array($result)){
    array_push($rows, $row);
}

foreach($rows as $row) {
    $ano = $row['ano'];
    $mes = $row['mes'];
    $anos[$ano][$mes] .= $row['qtde_solicitacoes'];
}

header("Content-Type: application/json");
echo json_encode($anos);
http_response_code(200);
exit();

?>