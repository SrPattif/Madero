<?php
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');
require($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if (!isset($_SESSION)) {
    session_start();
}

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

// Consulta para obter os tipos de taxa
$query_tipos_taxas = "SELECT id, description FROM tipos_taxas";
$result_tipos_taxas = mysqli_query($mysqli, $query_tipos_taxas);

// Array para armazenar as colunas dinâmicas
$colunas_dinamicas = array();

// Loop para construir as colunas dinâmicas
while ($row_tipos_taxas = mysqli_fetch_assoc($result_tipos_taxas)) {
    $id_taxa = $row_tipos_taxas['id'];
    $nome_taxa = $row_tipos_taxas['description'];

    // Construir a parte da consulta para a coluna dinâmica
    $coluna = "SUM(CASE WHEN avr.id_taxa = $id_taxa THEN avr.valor_taxa ELSE 0 END) AS `$nome_taxa`";

    // Adicionar a coluna ao array de colunas dinâmicas
    $colunas_dinamicas[] = $coluna;
}

// Consulta para obter todas as colunas (fixas e dinâmicas)
$colunas_fixas = "a.contrato_totvs, a.centro_custo, a.digito_financeiro, a.status, a.operacao, a.endereco AS nome_moradia";
$colunas_dinamicas_concatenadas = implode(", ", $colunas_dinamicas);
$colunas_query = "$colunas_fixas, $colunas_dinamicas_concatenadas";

// Consulta principal com todas as colunas
$query_principal = "SELECT $colunas_query FROM alojamentos a LEFT JOIN alojamentos_valores_reembolso avr ON a.id = avr.id_alojamento AND avr.mes = {$month} AND avr.ano = {$year} GROUP BY a.id";


// Executar a consulta principal e obter o resultado
$result = mysqli_query($mysqli, $query_principal);

// Criar uma nova planilha
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Desativar as linhas de grade
$sheet->setShowGridlines(false);

// Definir os dados das colunas
$columnNames = array();
$fieldCount = $result->field_count;
for ($i = 0; $i < $fieldCount; $i++) {
    $columnNames[] = $result->fetch_field()->name;
}

// Escrever os nomes das colunas na primeira linha
$columnIndex = 1;
foreach ($columnNames as $columnName) {
    $sheet->setCellValueByColumnAndRow($columnIndex, 1, $columnName);
    $columnIndex++;
}

// Definir estilos da primeira linha
$firstRow = $sheet->getRowIterator(1)->current();
$cellIterator = $firstRow->getCellIterator();
$cellIterator->setIterateOnlyExistingCells(true);

$fill = $spreadsheet->getActiveSheet()->getStyle($firstRow->getRowIndex())->getFill();
$fill->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('000000');

$font = $spreadsheet->getActiveSheet()->getStyle($firstRow->getRowIndex())->getFont();
$font->getColor()->setRGB('FFFFFF');
$font->setBold(true);

$alignment = $spreadsheet->getActiveSheet()->getStyle($firstRow->getRowIndex())->getAlignment();
$alignment->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
$alignment->setWrapText(true);


// Escrever os dados na planilha
$rowIndex = 2;
while ($row = $result->fetch_assoc()) {
    $columnIndex = 1;
    foreach ($row as $columnData) {
        $sheet->setCellValueByColumnAndRow($columnIndex, $rowIndex, $columnData);
        $columnIndex++;
    }
    $rowIndex++;
}

// Adicionar uma coluna "mês" no início da planilha
$sheet->insertNewColumnBefore('A', 1);

// Preencher todas as células da coluna "mês" com o valor "05/2023"
$lastRow = $sheet->getHighestRow();
$monthValue = $month . '/' . $year;
$sheet->setCellValue('A1', 'mês');
for ($row = 2; $row <= $lastRow; $row++) {
    $sheet->setCellValue('A' . $row, $monthValue);
}

foreach ($cellIterator as $cell) {
    $cell->getStyle()->applyFromArray([
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => '000000',
            ],
        ],
        'font' => [
            'color' => [
                'rgb' => 'FFFFFF',
            ],
            'bold' => true,
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'wrapText' => true,
        ],
    ]);
}

$sheet->getStyle('A:E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Definir largura automática das colunas
foreach (range('A', $sheet->getHighestDataColumn()) as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Definir altura da primeira linha
$sheet->getRowDimension(1)->setRowHeight(34);

// Definir formato contábil nas colunas a partir da B
$lastColumn = $sheet->getHighestDataColumn();
$columnRange = 'H:' . $lastColumn;
$numberFormat = '#,##0.00;[Red]-#,##0.00';
$formatCode = 'R$ ' . $numberFormat;

$sheet->getStyle($columnRange)->getNumberFormat()->setFormatCode($formatCode);

// Salvar a planilha como um arquivo XLSX na memória
$writer = new Xlsx($spreadsheet);

// Configurar o cabeçalho HTTP
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Condominios com Medições Preenchidas.xlsx"');
header('Cache-Control: max-age=0');

// Enviar a planilha como download para o cliente
$writer->save('php://output');
?>
