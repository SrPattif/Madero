<?php
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');
include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');

$month = date('n');
$year = date('Y');

if (isset($_SESSION['year'])) {
    if ($_SESSION['year'] == 2023 || $_SESSION['year'] == 2024) {
        $year = mysqli_real_escape_string($mysqli, $_SESSION['year']);
    }
}

if (isset($_SESSION['month'])) {
    if ($_SESSION['month'] > 0 && $_SESSION['month'] <= 12) {
        $month = mysqli_real_escape_string($mysqli, $_SESSION['month']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2F2Z7S0VR0"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    </script>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moradias Grupo Madero | Solicitações de Reembolso</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="/moradias/reembolsos/defaultStyle.css" />
    <link rel="stylesheet" href="./index.css" />

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
                    <h3>Todas as Solicitações</h3>
                    <div class="option-list">
                        <div class="option" onclick="window.location.href='./enviar/'">
                            <i class='bx bx-envelope'></i> ENVIAR SOLICITAÇÕES
                        </div>
                    </div>
                </div>

                <input type="text" id="filterInput" placeholder="Digite o texto para filtrar">

                <table class="ranking-table" id="dataTable">
                    <tr>
                        <th>#</th>
                        <th>Contrato</th>
                        <th>Endereço</th>
                        <th>Valor Reembolsável</th>
                        <th>Status</th>
                    </tr>
                    <?php
                    $query = "SELECT a.*, COALESCE(SUM(CASE WHEN tt.refundable = 1 THEN avr.valor_taxa ELSE 0 END), 0) AS valor_reembolsavel, COALESCE(SUM(CASE WHEN tt.refundable = 0 THEN avr.valor_taxa ELSE 0 END), 0) AS valor_nao_reembolsavel, avr.status, b.id AS id_boleto, b.codigo_interno AS arquivo_boleto, b.arquivo_comprovante AS arquivo_comprovante  FROM alojamentos a  LEFT JOIN alojamentos_valores_reembolso avr ON a.id = avr.id_alojamento AND avr.mes = {$month} AND avr.ano = {$year} LEFT JOIN boletos b ON a.id = b.id_alojamento AND MONTH(b.data_vencimento) = {$month} AND YEAR(b.data_vencimento) = {$year} LEFT JOIN tipos_taxas tt ON avr.id_taxa = tt.id  GROUP BY a.id  HAVING valor_reembolsavel > 0;";
                    $result = mysqli_query($mysqli, $query);
                    $rows = array();
                    while ($row = mysqli_fetch_array($result)) {
                        array_push($rows, $row);
                    }
                    foreach ($rows as $row) {
                        $status = $row['status'];

                        if(empty($status)) {
                            if(empty($row['id_boleto'])) {
                                $status = "aguardando_boleto";

                            } else if(empty($row['arquivo_comprovante'])) {
                                $status = "aguardando_comprovante";

                            } else {
                                $status = "pronto";
                            }
                        }
                    ?>

                    <tr onclick="window.open('./enviar/?id_alojamento=<?php echo($row['id']); ?>', '_blank').focus();">
                        <td><?php echo ((int) $row['id']); ?></td>
                        <td style="text-align: center;"><?php echo ($row['contrato_totvs']); ?></td>
                        <td><?php echo ($row['endereco']); ?></td>
                        <td style="text-align: center;">R$
                            <?php echo (number_format($row['valor_reembolsavel'], 2, ",", ".")); ?></td>
                        <td style="text-align: center;">
                            <?php
                                switch ($status) {
                                    case 'enviado':
                                        echo ('<div class="status status-blue"><i class="bx bx-envelope"></i> Enviado</div>');
                                        break;

                                    case 'reembolsado':
                                        echo ('<div class="status status-green"><i class="bx bx-check"></i> Reembolsado</div>');
                                        break;

                                    case 'pronto':
                                        echo ('<div class="status status-orange"><i class="bx bx-envelope-open"></i> Pronto para Envio</div>');
                                        break;

                                    case 'aguardando_boleto':
                                        echo ('<div class="status status-red"><i class="bx bx-file-blank" ></i> Sem Boletos</div>');
                                        break;
                                    
                                    case 'aguardando_comprovante':
                                        echo ('<div class="status status-red"><i class="bx bx-time"></i> Sem Comprovantes</div>');
                                        break;

                                    default:
                                        echo ('<div class="status status-gray"><i class="bx bx-question-mark"></i> Desconhecido</div>');
                                        break;
                                }
                                ?>
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