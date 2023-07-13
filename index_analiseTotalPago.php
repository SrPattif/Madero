<?php

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['USER_ID'])) {
    header('location: /login/');
    exit();
}

include('./libs/databaseConnection.php');

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

$query = "SELECT YEAR(b.data_vencimento) AS ano, MONTH(b.data_vencimento) AS mes, SUM(r.valor_liquido) AS soma_valor_total FROM boletos b LEFT JOIN razao r ON b.lancamento = r.documento GROUP BY YEAR(b.data_vencimento), MONTH(b.data_vencimento);";
$result = mysqli_query($mysqli, $query);
$row = mysqli_num_rows($result);
$rows = array();
while($row = mysqli_fetch_array($result)){
    array_push($rows, $row);
}

foreach($rows as $row) {
    $ano = $row['ano'];
    $mes = $row['mes'];
    $anos[$ano][$mes] .= $row['soma_valor_total'];
}

header("Content-Type: application/json");
echo json_encode($anos);
http_response_code(200);
exit();

?>