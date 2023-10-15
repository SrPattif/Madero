<?php
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/ativos/sindicos/backend/PhpXlsxGenerator.php'; 

if (!isset($_SESSION)) {
    session_start();
}

date_default_timezone_set('America/Sao_Paulo');
$data = date("d-m H", time()) . 'h' . date("i", time());
$fileName = "Síndicos - atualizado em " . $data . ".xlsx"; 

$query_principal = "SELECT a.contrato_totvs, a.digito_financeiro, a.centro_custo, a.operacao, a.status, a.endereco, atv.chapa AS chapa_sindico, atv.nome AS nome_sindico FROM alojamentos a LEFT JOIN ativos atv ON a.id_sindico=atv.id;";
$result = mysqli_query($mysqli, $query_principal);

$excelData[] = array('CONTRATO', 'DIG FINANCEIRO', 'CENTRO DE CUSTO', 'OPERACAO', 'STATUS', 'ENDERECO', 'SINDICO - CHAPA', 'SINDICO - NOME');

while ($row = $result->fetch_assoc()) {
    $lineData = array($row['contrato_totvs'], $row['digito_financeiro'], $row['centro_custo'], $row['operacao'], $row['status'], $row['endereco'], $row['chapa_sindico'], $row['nome_sindico']);  
    $excelData[] = $lineData; 
}

$xlsx = CodexWorld\PhpXlsxGenerator::fromArray( $excelData ); 
$xlsx->downloadAs($fileName); 

?>
