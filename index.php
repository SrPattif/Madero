<?php
    include('./libs/databaseConnection.php');
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
    <title>Moradias Grupo Madero | Gestão de Reembolsos</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="./defaultStyle.css" />
    <link rel="stylesheet" href="./index.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php
    require($_SERVER['DOCUMENT_ROOT'] . '/header.php');
    ?>

    <main>
        <div class="page-content">
            <div class="double-cards">
                <div class="card">
                    <h3>Ranking de Moradias</h3>

                    <table class="ranking-table">
                        <tr>
                            <th>#</th>
                            <th>Endereço</th>
                            <th>Valor Condomínio</th>
                            <th>Valor Reembolsável</th>
                        </tr>
                        <?php
                            $query = "SELECT ar.ano,ar.mes,ar.id_alojamento,ar.valor_taxa,ar.status,tt.description,tt.refundable,a.endereco,a.id FROM alojamentos_valores_reembolso ar INNER JOIN tipos_taxas tt ON ar.id_taxa=tt.id INNER JOIN alojamentos a ON ar.id_alojamento=a.id WHERE ano={$year} AND mes={$month} ORDER BY CASE WHEN refundable = 1 THEN valor_taxa ELSE NULL END DESC;";
                            $result = mysqli_query($mysqli, $query);
                            $rows = array();
                            while($row = mysqli_fetch_array($result)){
                                array_push($rows, $row);
                            }

                            $addresses = array();
                            $refundableValue = array();
                            $condominiumValue = array();

                            $index = 1;
                            foreach($rows as $row) {
                                $addressId = $row['id'];
                                $address = $row['endereco'];

                                $addresses[$addressId] = $address;

                                if(boolval($row['refundable'])) {
                                    if(isset($refundableValue[$addressId])) {
                                        $refundableValue[$addressId] += $row['valor_taxa'];
                                    } else {
                                        $refundableValue[$addressId] = $row['valor_taxa'];
                                    }
                                } else {
                                    if(isset($condominiumValue[$addressId])) {
                                        $condominiumValue[$addressId] += $row['valor_taxa'];
                                    } else {
                                        $condominiumValue[$addressId] = $row['valor_taxa'];
                                    }
                                }
                            }

                            foreach ($addresses as $addressId => $address) {
                                $cond = 0.0;
                                if(isset($condominiumValue[$addressId])) {
                                    $cond = $condominiumValue[$addressId];
                                }

                                $refund = 0.0;
                                if(isset($refundableValue[$addressId])) {
                                    $refund = $refundableValue[$addressId];
                                }
                        ?>

                        <tr>
                            <td><?php echo($index); ?></td>
                            <td><?php echo($address); ?></td>
                            <td>R$ <?php echo(number_format($cond, 2, ",", ".")); ?></td>
                            <td>R$ <?php echo(number_format($refund, 2, ",", ".")); ?></td>
                        </tr>

                        <?php   
                                if($index >= 5) {
                                    break;
                                } 
                                $index++;
                            }
                        ?>
                    </table>
                </div>
                <div class="card">
                    <h3>Ranking de Taxas Extras</h3>

                    <div class="custom-scroll">
                        <table class="ranking-table">
                            <tr>
                                <th>#</th>
                                <th>Descrição</th>
                                <th>Valor Total</th>
                            </tr>

                            <?php
                            $query = "SELECT tt.id, tt.description, SUM(avr.valor_taxa) AS maior_valor FROM tipos_taxas tt JOIN alojamentos_valores_reembolso avr ON tt.id = avr.id_taxa WHERE tt.refundable=1 AND avr.mes={$month} AND avr.ano={$year} GROUP BY tt.id, tt.description ORDER BY maior_valor DESC LIMIT 5";
                            $result = mysqli_query($mysqli, $query);
                            $rows = array();
                            while($row = mysqli_fetch_array($result)){
                                array_push($rows, $row);
                            }

                            $index = 1;
                            foreach($rows as $row) {
                        ?>

                            <tr>
                                <td><?php echo($index); ?></td>
                                <td><?php echo($row['description']); ?></td>
                                <td>R$ <?php echo(number_format($row['maior_valor'], 2, ",", ".")); ?></td>
                            </tr>
                            <?php 
                                $index++;
                            }
                        ?>
                        </table>
                    </div>
                </div>
            </div>

            <div class="double-cards">
                <div class="card">
                    <div class="card-header">
                        <?php
                            $query = "SELECT SUM(avr.valor_taxa) AS soma_valores FROM tipos_taxas tt JOIN alojamentos_valores_reembolso avr ON tt.id = avr.id_taxa WHERE tt.refundable = 1 AND avr.mes={$month} AND avr.ano={$year};";
                            $result = mysqli_query($mysqli, $query);
                            $row = mysqli_num_rows($result);

                            $refundableValue = 0.0;
                            if ($row == 1) {
                                $refundableObject = mysqli_fetch_assoc($result);
                                $refundableValue = $refundableObject['soma_valores'];
                            }
                        ?>
                        <h1>R$ <?php echo(number_format($refundableValue, 2, ",", ".")); ?></h1>
                        <span>Total Reembolsável</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <?php
                            $query = "SELECT SUM(avr.valor_taxa) AS soma_valores FROM tipos_taxas tt JOIN alojamentos_valores_reembolso avr ON tt.id = avr.id_taxa WHERE tt.refundable = 1 AND avr.mes={$month} AND avr.ano={$year} AND avr.status='refunded';";
                            $result = mysqli_query($mysqli, $query);
                            $row = mysqli_num_rows($result);

                            $refundedValue = 0.0;
                            if ($row == 1) {
                                $refundedObject = mysqli_fetch_assoc($result);
                                $refundedValue = $refundedObject['soma_valores'];
                            }
                        ?>
                        <h1>R$ <?php echo(number_format($refundedValue, 2, ",", ".")); ?></h1>
                        <span>Total Reembolsado</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <?php
                            $percentage = 0.0;
                            if($refundableValue > 0) {
                                $percentage = ($refundedValue / $refundableValue) * 100;
                            }
                        ?>
                        <h1><?php echo(number_format($percentage, 1, ",", ".")); ?>%</h1>
                        <span>Índice de Valores Reembolsados</span>
                    </div>
                </div>
            </div>

            <div class="double-cards">
                <div class="card">
                    <div class="card-header">
                        <?php
                            $query = "SELECT SUM(r.valor_baixa) AS soma_valores FROM alojamentos a LEFT JOIN boletos b ON b.id=a.id LEFT JOIN razao r ON r.documento=b.lancamento AND MONTH(b.data_vencimento)={$month} AND YEAR(b.data_vencimento)={$year};";
                            $result = mysqli_query($mysqli, $query);
                            $row = mysqli_num_rows($result);

                            $totalAmount = 0.0;
                            if ($row == 1) {
                                $totalAmountObject = mysqli_fetch_assoc($result);
                                $totalAmount = $totalAmountObject['soma_valores'];
                            }
                        ?>
                        <h1>R$ <?php echo(number_format($totalAmount, 2, ",", ".")); ?></h1>
                        <span>Valor Total Pago (razão)</span>
                    </div>
                </div>
                <div class="card" style="max-height: fit-content !important;">
                    <div class="card-header">
                        <?php
                            $query = "SELECT SUM(avr.valor_taxa) AS soma_valores FROM tipos_taxas tt JOIN alojamentos_valores_reembolso avr ON tt.id = avr.id_taxa WHERE tt.refundable = 0 AND avr.mes={$month} AND avr.ano={$year};";
                            $result = mysqli_query($mysqli, $query);
                            $row = mysqli_num_rows($result);

                            $refundableValue = 0.0;
                            if ($row == 1) {
                                $refundableObject = mysqli_fetch_assoc($result);
                                $refundableValue = $refundableObject['soma_valores'];
                            }
                        ?>
                        <h1>R$ <?php echo(number_format($refundableValue, 2, ",", ".")); ?></h1>
                        <span>Valor Total em Taxas de Condomínio (medições)</span>
                    </div>
                </div>
            </div>

            <div class="double-cards">
                <div class="card">
                    <div>
                        <canvas id="chart_taxasMensais"></canvas>
                    </div>
                </div>
                <div class="card">
                </div>
            </div>
        </div>
    </main>

    <?php
    //require('footer.php');
    ?>

    <script src="mobile-navbar.js"></script>
    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    $.ajax({
        type: "GET",
        url: "/index_analiseTaxas.php",
        success: function(result) {
            loadGraph(result);
            return;
        },
        error: function(result) {
            console.log(result);
        }
    });

    function loadGraph(requestData) {
        console.log(requestData)
        // Preparar os dados para o gráfico
        var labels = [];
        var datasets = [];

        for (var year in requestData) {
            for (var month in requestData[year]) {
                var dataPoints = requestData[year][month];

                if (dataPoints.length === 0) {
                    continue; // Ignorar meses sem dados
                }

                dataPoints.forEach(dp => {
                    var dataset = datasets.find(function(dataset) {
                        return dataset.label === dp.name;
                    });

                    if (!dataset) {
                        var color = getRandomColor();
                        dataset = {
                            label: dp.name,
                            data: [],
                            borderColor: color,
                            backgroundColor: getDarkerColor(color, 1),
                            tension: 0.0
                        };
                        datasets.push(dataset);
                    }

                    dataset.data.push(dp.totalAmount);
                });

                labels.push(month + '/' + year);
            }
        }

        // Configurações do gráfico
        var options = {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Gráfico de Taxas Mensais'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        };

        // Criação do gráfico de linha
        var ctx = document.getElementById('chart_taxasMensais').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: options
        });

    }

    // Função auxiliar para gerar cores aleatórias
    function getRandomColor() {
        var color = 'hsl(' + Math.floor(Math.random() * 360) + ', 70%, 50%)';
        return color;
    }

    function getDarkerColor(color, percent) {
        console.log(color)
        var hslRegex = /^hsl\((\d+),\s*(\d+)%,\s*(\d+)%\)$/i;
        var match = color.match(hslRegex);

        if (!match) {
            return color;
        }

        var h = parseInt(match[1]);
        var s = parseInt(match[2]);
        var l = parseInt(match[3]);

        var darkenAmount = Math.round((100 - percent) / 100 * l) * 0.4;

        l = Math.max(0, l - darkenAmount);

        var darkColor = "hsl(" + h + ", " + s + "%, " + l + "%)";

        return darkColor;
    }
    </script>

</body>

</html>