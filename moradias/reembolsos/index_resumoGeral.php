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

if (isset($_SESSION['year']) && ($_SESSION['year'] == 2023 || $_SESSION['year'] == 2024)) {
    $year = mysqli_real_escape_string($mysqli, $_SESSION['year']);
}

if (isset($_SESSION['month']) && ($_SESSION['month'] > 0 && $_SESSION['month'] <= 12)) {
    $month = mysqli_real_escape_string($mysqli, $_SESSION['month']);
}

// Consulta para obter todas as métricas em uma única consulta
$query = "
    SELECT
        (SELECT COUNT(id) FROM alojamentos) AS qtde_alojamentos,
        (SELECT COUNT(DISTINCT id_alojamento) FROM alojamentos_valores_reembolso WHERE mes = {$month} AND ano = {$year}) AS alojamentos_com_medicao,
        (SELECT COUNT(id) FROM boletos WHERE MONTH(data_vencimento) = {$month} AND YEAR(data_vencimento) = {$year}) AS qtde_boletos,
        (SELECT COUNT(b.id) FROM boletos b LEFT JOIN razao r ON b.lancamento = r.documento WHERE r.data_baixa IS NOT NULL AND MONTH(b.data_vencimento) = {$month} AND YEAR(b.data_vencimento) = {$year}) AS qtde_boletos_baixados,
        (SELECT SUM(avr.valor_taxa) FROM tipos_taxas tt JOIN alojamentos_valores_reembolso avr ON tt.id = avr.id_taxa WHERE tt.refundable = 1 AND avr.mes = {$month} AND avr.ano = {$year}) AS totalReembolsavel,
        (SELECT SUM(avr.valor_taxa) FROM tipos_taxas tt JOIN alojamentos_valores_reembolso avr ON tt.id = avr.id_taxa WHERE tt.refundable = 1 AND avr.mes = {$month} AND avr.ano = {$year} AND avr.status = 'reembolsado') AS totalReembolsado,
        (SELECT SUM(r.valor_liquido) FROM boletos b LEFT JOIN razao r ON r.documento = b.lancamento WHERE MONTH(b.data_vencimento) = {$month} AND YEAR(b.data_vencimento) = {$year}) AS totalPago,
        (SELECT SUM(avr.valor_taxa) FROM tipos_taxas tt JOIN alojamentos_valores_reembolso avr ON tt.id = avr.id_taxa WHERE tt.refundable = 0 AND avr.mes = {$month} AND avr.ano = {$year}) AS totalNaoReembolsavel";

$result = mysqli_query($mysqli, $query);
$row = mysqli_fetch_assoc($result);

$retorno = array(
    "alojamentos" => intval($row['qtde_alojamentos']),
    "medicoes" => intval($row['alojamentos_com_medicao']),
    "boletos" => array(
        "enviados" => intval($row['qtde_boletos']),
        "baixados" => intval($row['qtde_boletos_baixados']),
    ),
    "totais" => array(
        "reembolsavel" => floatval($row['totalReembolsavel']),
        "nao_reembolsavel" => floatval($row['totalNaoReembolsavel']),
        "reembolsado" => floatval($row['totalReembolsado']),
        "pago_razao" => floatval($row['totalPago']),
    )
);

header("Content-Type: application/json");
echo json_encode($retorno);
http_response_code(200);
exit();
?>
