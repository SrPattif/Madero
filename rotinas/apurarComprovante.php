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

include('../libs/databaseConnection.php');
require($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

if(!isset($_POST['id_boleto'])) {
    http_response_code(400);
    echo "Arquivo desconhecido";
    var_dump($_POST);
    exit();
}

$idBoleto = mysqli_real_escape_string($mysqli, $_POST['id_boleto']);

$queryBoleto = "SELECT b.*, r.data_baixa, r.valor_total, c.nome_interno AS arquivo_comprovante_bruto FROM boletos b INNER JOIN razao r ON r.documento=b.lancamento LEFT JOIN comprovantes c ON r.data_baixa=c.referencia WHERE b.id={$idBoleto};";
$rowsBoleto = array();
$resultBoletos = mysqli_query($mysqli, $queryBoleto);
if(mysqli_num_rows($resultBoletos) != 1) {
    http_response_code(401);
    echo "Boleto duplicado";
    exit();
}
$dadosBoleto = mysqli_fetch_assoc($resultBoletos);

$arquivoPDF = $_SERVER['DOCUMENT_ROOT'] . 'uploads/' . $dadosBoleto['arquivo_comprovante_bruto'];
$textoProcurado = strval('R$' . number_format($dadosBoleto['valor_total'], 2, ",", "."));
$paginaEncontrada = -1;

$parser = new \Smalot\PdfParser\Parser();
$pdf = $parser->parseFile($arquivoPDF);

for ($i=0; $i < sizeof($pdf->getPages()); $i++) { 
    $text = strval($pdf->getPages()[$i]->getText());
    $cleanText = preg_replace('/[^a-zA-Z0-9\p{P}\s$]/u', '', $text);
    $cleanText = preg_replace('/\s+/', '', $cleanText);

    if (strpos($cleanText, $textoProcurado) !== false) {
        $paginaEncontrada = $i + 1;
        break;
    }
}

if ($paginaEncontrada > -1) {
    $newFileCode = uniqid();
    $newFilePath = $_SERVER['DOCUMENT_ROOT'] . 'uploads/' . $newFileCode . '.pdf';

    saveSpecificPage($arquivoPDF, $paginaEncontrada, $newFilePath);

    $updateQuery = "UPDATE boletos SET arquivo_comprovante='{$newFileCode}.pdf' WHERE `id`={$idBoleto};";
    $result = mysqli_query($mysqli, $updateQuery);
    if ($result) {
        echo('Arquivo salvo como ' . $newFileCode . '.pdf');
        http_response_code(200);
        exit();

    } else {
        echo('Erro de servidor');
        http_response_code(500);
        exit();
    }
    
} else {
    echo '<br> O texto procurado (' . $textoProcurado . ') não foi encontrado no PDF ' . $arquivoPDF . '.';
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