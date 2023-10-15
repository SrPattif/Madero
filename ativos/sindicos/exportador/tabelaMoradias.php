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

$query_principal = "SELECT a.contrato_totvs, a.centro_custo, a.digito_financeiro, a.status, a.operacao, a.endereco, atv.chapa AS chapa_sindico, atv.nome AS nome_sindico FROM alojamentos a LEFT JOIN ativos atv ON a.id_sindico=atv.id;";
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

// Salvar a planilha como um arquivo XLSX na memória
$writer = new Xlsx($spreadsheet);

// Configurar o cabeçalho HTTP
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Síndicos Atualizados.xlsx"');
header('Cache-Control: max-age=0');

// Enviar a planilha como download para o cliente
$writer->save('php://output');
?>
