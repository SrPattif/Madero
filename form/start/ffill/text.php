<?php
include('../../libs/databaseConnection.php');

$query = "SELECT DISTINCT a.* FROM alojamentos a LEFT JOIN alojamentos_valores_reembolso ar ON a.id = ar.id_alojamento AND ar.ano = 2023 AND ar.mes = 5 WHERE ar.id_alojamento IS NULL;";
$result = mysqli_query($mysqli, $query);
$rows = array();
while($row = mysqli_fetch_array($result)){
    array_push($rows, $row);
}

if (!empty($rows)) {
    $primeiroResultado = $rows[0];
    $id = $primeiroResultado['id']; // Substitua 'ID' pelo nome correto da coluna do ID

    echo $id;
}