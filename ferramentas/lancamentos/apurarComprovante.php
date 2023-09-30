<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['USER_ID'])) {
    http_response_code(401);
    echo "Não Autorizado";
    exit();
}

use setasign\Fpdi\Fpdi;

include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');
require($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

if(!isset($_POST['titulo'])) {
    header("Content-Type: application/json");
    echo json_encode(array("apurado" => false, "descricao_erro" => "Arquivo não especificado"));
    http_response_code(400);
    exit();
}

$titulo = mysqli_real_escape_string($mysqli, $_POST['titulo']);

$queryTitulo = "SELECT r.documento, r.data_baixa, r.valor_liquido AS valor_total, c.nome_interno AS arquivo_comprovante_bruto FROM razao r LEFT JOIN comprovantes c ON r.data_baixa=c.referencia WHERE r.documento={$titulo} ORDER BY r.id DESC LIMIT 1;";
$resultTitulo = mysqli_query($mysqli, $queryTitulo);
if(mysqli_num_rows($resultTitulo) != 1) {
    header("Content-Type: application/json");
    echo json_encode(array("apurado" => false, "descricao_erro" => "Múltiplos títulos encontrados."));
    http_response_code(400);
    exit();
}
$dadosTitulo = mysqli_fetch_assoc($resultTitulo);
$titulo = $dadosTitulo['documento'];

if(!isset($dadosTitulo['arquivo_comprovante_bruto']) || empty($dadosTitulo['arquivo_comprovante_bruto'])) {
    header("Content-Type: application/json");
    echo json_encode(array("apurado" => false, "descricao_erro" => "Grupo de comprovantes de pagamento faltante."));
    http_response_code(400);
    exit();
}

$arquivoPDF = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $dadosTitulo['arquivo_comprovante_bruto'];
$textoProcurado = strval('R$' . number_format($dadosTitulo['valor_total'], 2, ",", "."));
$paginasEncontradas = array();

$parser = new \Smalot\PdfParser\Parser();
$pdf = $parser->parseFile($arquivoPDF);

for ($i=0; $i < sizeof($pdf->getPages()); $i++) { 
    $text = strval($pdf->getPages()[$i]->getText());
    $cleanText = preg_replace('/[^a-zA-Z0-9\p{P}\s$]/u', '', $text);
    $cleanText = preg_replace('/\s+/', '', $cleanText);

    if (strpos($cleanText, $textoProcurado) !== false) {
        array_push($paginasEncontradas, $i + 1);
        break;
    }
}

if (sizeof($paginasEncontradas) == 1) {
    $paginaEncontrada = $paginasEncontradas[0];
    $newFileCode = uniqid();
    $newFilePath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $newFileCode . '.pdf';

    saveSpecificPage($arquivoPDF, $paginaEncontrada, $newFilePath);

    $updateQuery = "UPDATE razao SET comprovante_pagamento='{$newFileCode}.pdf' WHERE `documento`={$titulo};";
    $result = mysqli_query($mysqli, $updateQuery);
    if ($result) {
        header("Content-Type: application/json");
        echo json_encode(array("apurado" => true, "nome_arquivo" =>  $newFileCode . ".pdf"));
        http_response_code(200);
        exit();

    } else {
        header("Content-Type: application/json");
        echo json_encode(array("apurado" => false, "descricao_erro" => "Erro ao executar query."));
        http_response_code(400);
        exit();
    }
    
} else {
    header("Content-Type: application/json");
    echo json_encode(array("apurado" => false, "descricao_erro" => "Comprovante não encontrado.", "tec_details" => "O texto procurado (" . $textoProcurado . ") não foi encontrado no PDF" . $arquivoPDF . "."));
    http_response_code(400);
    exit();
}


function saveSpecificPage($sourcePDF, $pageToSave, $outputName) {
    $pdf = new FPDI();
    
    $pdf->setSourceFile($sourcePDF);
    $importedPage = $pdf->importPage($pageToSave);
    $pdf->addPage();
    $pdf->useImportedPage($importedPage);
    
    $pdf->Output($outputName, 'F');
  }
?>