<?php
include('../libs/databaseConnection.php');
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
    <title>Moradias Grupo Madero | Todas as Moradias</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="/defaultStyle.css" />
    <link rel="stylesheet" href="./index.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php
    require($_SERVER['DOCUMENT_ROOT'] . '/header.php');
    ?>

    <main>
        <div class="page-content">
            <div class="card">
                <table class="ranking-table">
                    <tr>
                        <th>#</th>
                        <th>Contrato</th>
                        <th>Endereço</th>
                        <th>Valor Reembolsável</th>
                        <th>Status</th>
                    </tr>
                    <?php
                    $query = "SELECT a.*, COALESCE(SUM(CASE WHEN tt.refundable = 1 THEN avr.valor_taxa ELSE 0 END), 0) AS valor_reembolsavel, COALESCE(SUM(CASE WHEN tt.refundable = 0 THEN avr.valor_taxa ELSE 0 END), 0) AS valor_nao_reembolsavel, avr.status  FROM alojamentos a  LEFT JOIN alojamentos_valores_reembolso avr ON a.id = avr.id_alojamento AND avr.mes = {$month} AND avr.ano = {$year}  LEFT JOIN tipos_taxas tt ON avr.id_taxa = tt.id  GROUP BY a.id  HAVING valor_reembolsavel > 0;";
                    $result = mysqli_query($mysqli, $query);
                    $rows = array();
                    while ($row = mysqli_fetch_array($result)) {
                        array_push($rows, $row);
                    }
                    foreach ($rows as $row) {
                        $status = $row['status'];
                    ?>

                    <tr>
                        <td><?php echo ((int) $row['id']); ?></td>
                        <td style="text-align: center;"><?php echo ($row['contrato_totvs']); ?></td>
                        <td><?php echo ($row['endereco']); ?></td>
                        <td style="text-align: center;">R$
                            <?php echo (number_format($row['valor_reembolsavel'], 2, ",", ".")); ?></td>
                        <td style="text-align: center;">
                            <?php
                                switch ($status) {
                                    case 'sent':
                                        echo ('<div class="status status-blue"><i class="bx bx-envelope"></i> Enviado</div>');
                                        break;

                                    case 'refunded':
                                        echo ('<div class="status status-green"><i class="bx bx-check"></i> Reembolsado</div>');
                                        break;

                                    case 'ready':
                                        echo ('<div class="status status-orange"><i class="bx bx-envelope-open"></i> Pronto para Envio</div>');
                                        break;

                                    case 'waiting_taxes':
                                        echo ('<div class="status status-red"><i class="bx bx-time"></i> Sem Boletos</div>');
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
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script>
    $("#export-table-btn").click(function() {
        window.location.href = "/exporter/tableHabitations.php";
    });
    </script>
</body>

</html>