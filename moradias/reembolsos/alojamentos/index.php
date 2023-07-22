<?php
    include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');

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

    $query = "SELECT a.*, COALESCE( SUM( CASE WHEN tt.refundable = 1 THEN avr.valor_taxa ELSE 0 END ), 0 ) AS valor_reembolsavel, COALESCE( SUM( CASE WHEN tt.refundable = 0 THEN avr.valor_taxa ELSE 0 END ), 0 ) AS valor_nao_reembolsavel, b.id AS id_boleto, b.codigo_interno AS arquivo_boleto, b.arquivo_comprovante AS comprovante_boleto, r.documento AS titulo_razao, r.data_baixa, avr.`status` AS status_solicitacao FROM alojamentos a LEFT JOIN alojamentos_valores_reembolso avr ON a.id = avr.id_alojamento AND avr.mes = {$month} AND avr.ano = {$year} LEFT JOIN boletos b ON b.id_alojamento = a.id AND MONTH(b.data_vencimento) = {$month} AND YEAR(b.data_vencimento) = {$year} LEFT JOIN razao r ON b.lancamento = r.documento LEFT JOIN tipos_taxas tt ON avr.id_taxa = tt.id GROUP BY a.id;";
    $result = mysqli_query($mysqli, $query);
    $rows = array();
    while($row = mysqli_fetch_array($result)){
        array_push($rows, $row);
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moradias Grupo Madero | Todas as Moradias</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="/moradias/reembolsos/defaultStyle.css" />
    <link rel="stylesheet" href="./index.css" />
    <link rel="stylesheet" href="/assets/styles/tooltips.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php
    require($_SERVER['DOCUMENT_ROOT'] . '/moradias/reembolsos/header.php');
    ?>

    <main>
        <div class="page-content">
            <div class="card">
                <div class="card-header">
                    <h3>Lista de Moradias</h3>
                    <div class="option-list">
                        <div class="option">
                            <i class='bx bx-plus'></i> ADICIONAR MORADIA
                        </div>
                        <div id="export-table-btn" class="option">
                            <i class='bx bxs-file-export'></i> EXPORTAR TABELA
                        </div>
                    </div>
                </div>

                <input type="text" id="filterInput" placeholder="Digite o texto para filtrar">

                <table class="ranking-table" id="dataTable">
                    <tr>
                        <th>#</th>
                        <th></th>
                        <th>Contrato</th>
                        <th>Endereço</th>
                        <th>Dia Vcto.</th>
                        <th>Valor Condomínio</th>
                        <th class="sortable" data-column="despesas">Valor Reembolsável</th>
                        <th></th>
                        <th></th>
                    </tr>
                    <?php
                        foreach($rows as $row) {
                            $statusColor = "status-gray";
                            $statusCode = "?";
                            $statusDatabase = $row['status_solicitacao'];

                            $valorReembolsavel = $row['valor_reembolsavel'];
                            $valorNaoReembolsavel = $row['valor_nao_reembolsavel'];

                            if(empty($statusDatabase)) {
                                if(empty($valorNaoReembolsavel) || $valorNaoReembolsavel == 0) {
                                    $statusColor = "status-red";
                                    $statusCode = "SM";
                                    $statusCode = "Sem medição";
    
                                } elseif(!empty($valorNaoReembolsavel) && $valorNaoReembolsavel > 0 && empty($valorReembolsavel) || $valorReembolsavel == 0) {
                                    $statusColor = "status-green";
                                    $statusCode = "OK";
    
                                } elseif(empty($row['id_boleto']) || empty($row['arquivo_boleto'])) {
                                    $statusColor = "status-orange";
                                    $statusCode = "SB";
                                    $statusCode = "Sem boleto";
    
                                } elseif(empty($row['titulo_razao']) || empty($row['data_baixa'])) {
                                    $statusColor = "status-orange";
                                    $statusCode = "SR";
                                    $statusCode = "Sem razão";
    
                                } elseif(empty($row['comprovante_boleto'])) {
                                    $statusColor = "status-orange";
                                    $statusCode = "SC";
                                    $statusCode = "Sem comprovante";
    
                                } else {
                                    $statusColor = "status-yellow";
                                    $statusCode = "PE";
                                    $statusCode = "Pronto para envio";
                                }
                            } else {
                                switch ($statusDatabase) {
                                    case 'enviado':
                                        $statusColor = "status-blue";
                                        $statusCode = "EV";
                                        $statusCode = "Solicitação enviada";
                                        break;

                                    case 'reembolsado':
                                        $statusColor = "status-green";
                                        $statusCode = "Reembolsado";
                                        break;

                                    case 'pronto':
                                        $statusColor = "status-yellow";
                                        $statusCode = "PE";
                                        $statusCode = "Pronto para envio";
                                        break;

                                    default:
                                        $statusColor = "status-gray";
                                        $statusCode = "?";
                                        break;
                                }
                            }

                            $boletoColor = 'gray';

                            if(empty($row['arquivo_boleto'])) {
                                $boletoColor = 'red';

                            } else {
                                if(empty($row['comprovante_boleto'])) {
                                    $boletoColor = 'yellow';

                                } else {
                                    $boletoColor = 'green';
                                }
                            }
                    ?>

                    <tr
                        onclick="window.open('./detalhes/?id_alojamento=<?php echo($row['id']); ?>', '_blank').focus();">
                        <td style="text-align: center;"><?php echo((int) $row['id']); ?></td>
                        <td style="text-align: center;">
                            <div class="status-circle status-<?php echo($boletoColor); ?>"></div>
                        </td>
                        <td style="text-align: center;"><?php echo($row['contrato_totvs']); ?></td>
                        <td><?php echo($row['endereco']); ?></td>
                        <td style="text-align: center;"><?php echo($row['dia_vencimento']); ?></td>
                        <td style="text-align: center;">R$
                            <?php echo(number_format($valorNaoReembolsavel, 2, ",", ".")); ?></td>
                        <td style="text-align: center;">R$
                            <?php echo(number_format($valorReembolsavel, 2, ",", ".")); ?></td>
                        <td>
                            <div class="status <?php echo($statusColor); ?>"><?php echo($statusCode); ?></div>
                        </td>
                        <td>
                            <div class="house-options">
                                <div class="option">
                                    <span data-tooltip="Detalhes da Moradia" data-flow="left"
                                        style="text-align: center; z-index: 999;">
                                        <a href="./detalhes/?id_alojamento=<?php echo($row['id']); ?>"><i
                                                class='bx bx-detail'></i></a>
                                    </span>
                                </div>
                                <div class="option">
                                    <span data-tooltip="Editar Moradia" data-flow="left"
                                        style="text-align: center; z-index: 999;"><a href="./editar/?id_alojamento=<?php echo($row['id']); ?>"><i
                                            class='bx bxs-edit-alt'></i></a></span>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <?php   
                        }
                    ?>
                </table>
            </div>
        </div>
        </div>
    </main>

    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="/mobile-navbar.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script>
    $("#export-table-btn").click(function() {
        window.location.href = "/moradias/reembolsos/exporter/tableHabitations.php";
    });

    const filterInput = document.getElementById('filterInput');
    const dataTable = document.getElementById('dataTable');

    filterInput.addEventListener('input', function() {
        const filterText = filterInput.value.toLowerCase();
        const rows = dataTable.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                const cellText = cell.textContent.toLowerCase();

                if (cellText.includes(filterText)) {
                    found = true;
                    break;
                }
            }

            if (found) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
    </script>
</body>

</html>