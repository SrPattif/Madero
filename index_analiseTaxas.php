<?php

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['USER_ID'])) {
    header('location: /login/');
    exit();
}

include('./libs/databaseConnection.php');

$meses = range(1, 12); // Cria um array com os números de 1 a 12

$anos = array(
    2023 => array_fill_keys($meses, array()),
    2024 => array_fill_keys($meses, array())
);


$query = "SELECT tt.description AS nome_taxa, m.ano, m.mes, COALESCE(SUM(avr.valor_taxa), 0) AS valor_total FROM ( SELECT DISTINCT description, id FROM tipos_taxas ) tt CROSS JOIN ( SELECT DISTINCT ano, mes FROM alojamentos_valores_reembolso ) m LEFT JOIN alojamentos_valores_reembolso avr ON tt.id = avr.id_taxa AND m.ano = avr.ano AND m.mes = avr.mes GROUP BY tt.description, m.ano, m.mes ORDER BY ano,mes ASC;";
$result = mysqli_query($mysqli, $query);
$row = mysqli_num_rows($result);
$rows = array();
while($row = mysqli_fetch_array($result)){
    array_push($rows, $row);
}

foreach($rows as $row) {
    $ano = $row['ano'];
    $mes = $row['mes'];
    $description = $row['nome_taxa'];
    $valorTotal = $row['valor_total'];

    if(isset($anos[$ano][$mes])) {
        array_push($anos[$ano][$mes], array('name' => $description, 'totalAmount' => $valorTotal));

    } else {
        $anos[$ano][$mes] = array(array('name' => $description, 'totalAmount' => $valorTotal));
    }
}

header("Content-Type: application/json");
echo json_encode($anos);
http_response_code(200);
exit();

?>