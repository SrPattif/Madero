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

if(isset($_SESSION['year'])) {
    if($_SESSION['year'] == 2023 || $_SESSION['year'] == 2024) {
        $year = mysqli_real_escape_string($mysqli, $_SESSION['year']);
    }
}

if(isset($_SESSION['month'])) {
    if($_SESSION['month'] > 0 && $_SESSION['month'] <= 12) {
        $month = mysqli_real_escape_string($mysqli, $_SESSION['month']);
    }
}

$alojamentosCadastrados = 0;
$medicoesRealizadas = 0;
$boletosEnviados = 0;
$boletosBaixados = 0;
$totalReembolsavel = 0;
$totalNaoReembolsavel = 0;
$totalReembolsado = 0;
$totalPago = 0;


// Alojamentos Cadastrados
$query = "SELECT COUNT(id) AS qtde_alojamentos FROM alojamentos;";
$result = mysqli_query($mysqli, $query);
$row = mysqli_num_rows($result);

$qtdeAlojamentos = 0;
if ($row == 1) {
    $resultData = mysqli_fetch_assoc($result);
    $alojamentosCadastrados = $resultData['qtde_alojamentos'];
}

// Medicoes Realizadas
$query = "SELECT COUNT(DISTINCT id_alojamento) AS alojamentos_com_medicao FROM alojamentos_valores_reembolso WHERE mes={$month} AND ano={$year};";
$result = mysqli_query($mysqli, $query);
$row = mysqli_num_rows($result);

if ($row == 1) {
    $resultData = mysqli_fetch_assoc($result);
    $medicoesRealizadas = $resultData['alojamentos_com_medicao'];
}


// Boletos Enviados
$query = "SELECT COUNT(id) AS qtde_boletos FROM boletos WHERE MONTH(data_vencimento) = {$month} AND YEAR(data_vencimento) = {$year};";
$result = mysqli_query($mysqli, $query);
$row = mysqli_num_rows($result);

if ($row == 1) {
    $resultData = mysqli_fetch_assoc($result);
    $boletosEnviados = $resultData['qtde_boletos'];
}

// Boletos Baixados
$query = "SELECT COUNT(b.id) AS qtde_boletos_baixados FROM boletos b LEFT JOIN razao r ON b.lancamento=r.documento WHERE r.data_baixa IS NOT NULL AND MONTH(b.data_vencimento)={$month} AND YEAR(b.data_vencimento)={$year};";
$result = mysqli_query($mysqli, $query);
$row = mysqli_num_rows($result);

if ($row == 1) {
    $resultData = mysqli_fetch_assoc($result);
    $boletosBaixados = $resultData['qtde_boletos_baixados'];
}

// Total Reembolsavel
$query = "SELECT SUM(avr.valor_taxa) AS soma_valores FROM tipos_taxas tt JOIN alojamentos_valores_reembolso avr ON tt.id = avr.id_taxa WHERE tt.refundable = 1 AND avr.mes={$month} AND avr.ano={$year};";
$result = mysqli_query($mysqli, $query);
$row = mysqli_num_rows($result);

$totalReembolsavel = 0.0;
if ($row == 1) {
    $refundableObject = mysqli_fetch_assoc($result);
    $totalReembolsavel = $refundableObject['soma_valores'];
}

// Total Reembolsado
$query = "SELECT SUM(avr.valor_taxa) AS soma_valores FROM tipos_taxas tt JOIN alojamentos_valores_reembolso avr ON tt.id = avr.id_taxa WHERE tt.refundable = 1 AND avr.mes={$month} AND avr.ano={$year} AND avr.status='reembolsado';";
$result = mysqli_query($mysqli, $query);
$row = mysqli_num_rows($result);

if ($row == 1) {
    $refundedObject = mysqli_fetch_assoc($result);
    $totalReembolsado = $refundedObject['soma_valores'];
}

// Valor Total Pago (razão)
$query = "SELECT SUM(r.valor_liquido) AS soma_valores FROM boletos b LEFT JOIN razao r ON r.documento=b.lancamento AND MONTH(b.data_vencimento)={$month} AND YEAR(b.data_vencimento)={$year};";
$result = mysqli_query($mysqli, $query);
$row = mysqli_num_rows($result);

if ($row == 1) {
    $totalAmountObject = mysqli_fetch_assoc($result);
    $totalPago = $totalAmountObject['soma_valores'];
}

// Total não Reembolsável
$query = "SELECT SUM(avr.valor_taxa) AS soma_valores FROM tipos_taxas tt JOIN alojamentos_valores_reembolso avr ON tt.id = avr.id_taxa WHERE tt.refundable = 0 AND avr.mes={$month} AND avr.ano={$year};";
$result = mysqli_query($mysqli, $query);
$row = mysqli_num_rows($result);

if ($row == 1) {
    $refundableObject = mysqli_fetch_assoc($result);
    $totalNaoReembolsavel = $refundableObject['soma_valores'];
}


$retorno = array(
"alojamentos" => $alojamentosCadastrados,
"medicoes" => $medicoesRealizadas,
"boletos" => array(
    "enviados" => $boletosEnviados,
    "baixados" => $boletosBaixados),
"totais" => array(
    "reembolsavel" => floatval($totalReembolsavel),
    "nao_reembolsavel" => floatval($totalNaoReembolsavel),
    "reembolsado" => floatval($totalReembolsado),
    "pago_razao" => floatval($totalPago))
);

header("Content-Type: application/json");
echo json_encode($retorno);
http_response_code(200);
exit();

?>