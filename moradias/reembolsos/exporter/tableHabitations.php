<?php
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/ativos/sindicos/backend/PhpXlsxGenerator.php'; 

if (!isset($_SESSION)) {
    session_start();
}

date_default_timezone_set('America/Sao_Paulo');
$data = date("d-m H", time()) . 'h' . date("i", time());
$fileName = "Despesas Reembolsáveis - " . $data . ".xlsx"; 

$query_principal = "SELECT CONCAT(avr.mes, '/', avr.ano) AS competencia, a.contrato_totvs, a.digito_financeiro, a.centro_custo, a.status, a.operacao, a.endereco, tt.description AS despesa, IF(tt.refundable=1, 'Sim', 'Não') AS reembolsavel, avr.valor_taxa FROM alojamentos_valores_reembolso avr INNER JOIN alojamentos a ON avr.id_alojamento=a.id INNER JOIN tipos_taxas tt ON avr.id_taxa=tt.id ORDER BY avr.ano, avr.mes, a.endereco ASC;";
$result = mysqli_query($mysqli, $query_principal);

$excelData[] = array('COMPETÊNCIA', 'CONTRATO', 'DIG FINANCEIRO', 'CENTRO DE CUSTO', 'OPERACAO', 'STATUS', 'ENDERECO', 'DESPESA', 'REEMBOLSÁVEL', 'VALOR');

while ($row = $result->fetch_assoc()) {
    $lineData = array($row['competencia'], $row['contrato_totvs'], $row['digito_financeiro'], $row['centro_custo'], $row['operacao'], $row['status'], $row['endereco'], $row['despesa'], $row['reembolsavel'], $row['valor_taxa']);  
    $excelData[] = $lineData; 
}

$xlsx = CodexWorld\PhpXlsxGenerator::fromArray( $excelData ); 
$xlsx->downloadAs($fileName); 

?>
