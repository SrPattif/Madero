<?php
include('../../libs/databaseConnection.php');

// Obtém a lista de colunas da tabela 'razao'
$sql = "SELECT column_name FROM information_schema.columns WHERE table_name = 'razao' AND column_name != 'id' AND column_name != 'data_atualizacao';";
$result = $mysqli->query($sql);

$columns = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['column_name'];
    }
}

// Retorna as colunas como resposta JSON
header('Content-Type: application/json');
echo json_encode($columns);
?>
