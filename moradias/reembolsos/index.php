<?php
    if(!isset($_SESSION)) {
        session_start();
    }

    include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');

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
    require($_SERVER['DOCUMENT_ROOT'] . '/moradias/reembolsos/header.php');
    ?>

    <div class="blur-manager">
        <div class="blur-button" id="div_blurButton" onclick="alternarBlur()">
            <i class='bx bx-show'></i> Reexibir Valores
        </div>
    </div>

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
                            <td style="text-align: center;"><?php echo($index); ?></td>
                            <td><?php echo($address); ?></td>
                            <td style="text-align: center;" class="td_value">R$ <?php echo(number_format($cond, 2, ",", ".")); ?></td>
                            <td style="text-align: center;" class="td_value">R$ <?php echo(number_format($refund, 2, ",", ".")); ?></td>
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
                                <td style="text-align: center;"><?php echo($index); ?></td>
                                <td><?php echo($row['description']); ?></td>
                                <td style="text-align: center;" class="td_value">R$
                                    <?php echo(number_format($row['maior_valor'], 2, ",", ".")); ?></td>
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
                        <h1 id="div_qtdeAlojamentos"><i class='bx bx-spin bx-loader-alt'></i></h1>
                        <span>Alojamentos Cadastrados</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h1 id="div_qtdeMedicoes"><i class='bx bx-spin bx-loader-alt'></i></h1>
                        <span>Medições Realizadas</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <?php
                        /*
                        $percentage = 0.0;
                        if($qtdeAlojamentos > 0) {
                            $percentage = ($alojamentosComMedicao / $qtdeAlojamentos) * 100;
                        }
                        */
                        ?>
                        <h1 id="div_indiceMedicoes"><i class='bx bx-spin bx-loader-alt'></i></h1>
                        <span>Índice de Medições Realizadas</span>
                    </div>
                </div>
            </div>

            <div class="double-cards">
                <div class="card">
                    <div class="card-header">
                        <h1 id="div_boletosEnviados"><i class='bx bx-spin bx-loader-alt'></i></h1>
                        <span>Boletos Enviados</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h1 id="div_boletosBaixados"><i class='bx bx-spin bx-loader-alt'></i></h1>
                        <span>Boletos Baixados</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h1 id="div_indiceBoletos"><i class='bx bx-spin bx-loader-alt'></i></h1>
                        <span>Índice de Boletos Baixados</span>
                    </div>
                </div>
            </div>

            <div class="double-cards">
                <div class="card">
                    <div class="card-header">
                        <h1 id="div_totalReembolsavel"><i class='bx bx-spin bx-loader-alt'></i></h1>
                        <span>Total Reembolsável</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h1 id="div_totalReembolsado"><i class='bx bx-spin bx-loader-alt'></i></h1>
                        <span>Total Reembolsado</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h1 id="div_indiceValoresReembolsados"><i class='bx bx-spin bx-loader-alt'></i></h1>
                        <span>Índice de Valores Reembolsados</span>
                    </div>
                </div>
            </div>

            <div class="double-cards">
                <div class="card">
                    <div class="card-header">
                        <h1 id="div_totalPagoRazao"><i class='bx bx-spin bx-loader-alt'></i></h1>
                        <span>Valor Total Pago (razão)</span>
                    </div>
                </div>
                <div class="card" style="max-height: fit-content !important;">
                    <div class="card-header">
                        <h1 id="div_totalNaoReembolsavel"><i class='bx bx-spin bx-loader-alt'></i></h1>
                        <span>Valor Total em Taxas não Reembolsáveis (medições)</span>
                    </div>
                </div>
            </div>

            <div class="double-cards">
                <div class="card">
                    <div>
                        <canvas id="chart_totalPago"></canvas>
                    </div>
                </div>
                <div class="card">
                    <div>
                        <canvas id="chart_taxasMensais"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php
    //require('footer.php');
    ?>

    <script src="/mobile-navbar.js"></script>
    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    var blurElements = [
        "#div_qtdeAlojamentos",
        "#div_qtdeMedicoes",
        "#div_indiceMedicoes",
        "#div_boletosEnviados",
        "#div_boletosBaixados",
        "#div_indiceBoletos",
        "#div_totalReembolsavel",
        "#div_totalReembolsado",
        "#div_indiceValoresReembolsados",
        "#div_totalPagoRazao",
        "#div_totalNaoReembolsavel",
        "#chart_taxasMensais",
        "#chart_totalPago",
        ".td_value"
    ]

    var blurred = false;

    $(document).ready(() => {
        if(blurred) {
            $('#div_blurButton').html("<i class='bx bx-show'></i> Reexibir Valores")

            blurElements.forEach(div => {
                $(div).addClass("number-blur");
            });

        } else {
            $('#div_blurButton').html("<i class='bx bx-hide' ></i> Ocultar Valores")
        }

        $.ajax({
            type: "GET",
            url: "./index_analiseTaxas.php",
            success: function(result) {
                loadGraphMeditions(result);
                return;
            },
            error: function(result) {
                console.log(result);
            }
        });

        $.ajax({
            type: "GET",
            url: "./index_analiseTotalPago.php",
            success: function(result) {
                loadGraphTotal(result);
                return;
            },
            error: function(result) {
                console.log(result);
            }
        });

        $.ajax({
            type: "GET",
            url: "./index_resumoGeral.php",
            success: function(result) {
                $("#div_qtdeAlojamentos").text(result.alojamentos);
                $("#div_qtdeMedicoes").text(result.medicoes);
                $("#div_indiceMedicoes").text(numberFormat((result.medicoes / result.alojamentos) * 100) + "%");

                $("#div_boletosEnviados").text(result.boletos.enviados);
                $("#div_boletosBaixados").text(result.boletos.baixados);
                $("#div_indiceBoletos").text(numberFormat((result.boletos.baixados / result.boletos.enviados) * 100) + "%");

                $("#div_totalReembolsavel").text("R$ " + numberFormat(result.totais.reembolsado, 2, ",", "."));
                $("#div_totalReembolsado").text("R$ " + numberFormat(result.totais.reembolsado, 2, ",", "."));
                $("#div_indiceValoresReembolsados").text(numberFormat((result.totais.reembolsado / result.totais.reembolsavel) * 100) + "%");

                $("#div_totalPagoRazao").text("R$ " + numberFormat(result.totais.pago_razao, 2, ",", "."));
                $("#div_totalNaoReembolsavel").text("R$ " + numberFormat(result.totais.nao_reembolsavel, 2, ",", "."));
                return;
            },
            error: function(result) {
                console.log(result);
            }
        });
    });


    function loadGraphMeditions(requestData) {
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
                        var color = getColorFromArray();
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

    function loadGraphTotal(requestData) {
        // Preparar os dados para o gráfico
        var labels = [];
        var datasets = [{
            label: 'Valor Pago',
            data: [],
            borderColor: 'rgba(0, 0, 0, 1)',
            backgroundColor: 'rgba(0, 0, 0, 0.5)',
            borderWidth: 1.5,
            borderRadius: 10,
            borderSkipped: false,
        }];

        for (var year in requestData) {
            for (var month in requestData[year]) {
                var valorPago = requestData[year][month];

                /*
                if (valorPago === 0) {
                    continue; // Ignorar meses sem dados
                }
                */

                datasets[0].data.push(valorPago);

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
                    text: 'Gráfico de Valor Pago (razão)'
                }
            }
        };

        // Criação do gráfico de linha
        var ctx = document.getElementById('chart_totalPago').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'bar',
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

    var colorIndex = 0;

    function getColorFromArray() {
        var colorArray = [
            "#63b598", "#ce7d78", "#ea9e70", "#a48a9e", "#c6e1e8", "#648177", "#0d5ac1",
            "#f205e6", "#1c0365", "#14a9ad", "#4ca2f9", "#a4e43f", "#d298e2", "#6119d0",
            "#d2737d", "#c0a43c", "#f2510e", "#651be6", "#79806e", "#61da5e", "#cd2f00",
            "#9348af", "#01ac53", "#c5a4fb", "#996635", "#b11573", "#4bb473", "#75d89e",
            "#2f3f94", "#2f7b99", "#da967d", "#34891f", "#b0d87b", "#ca4751", "#7e50a8",
            "#c4d647", "#e0eeb8", "#11dec1", "#289812", "#566ca0", "#ffdbe1", "#2f1179",
            "#935b6d", "#916988", "#513d98", "#aead3a", "#9e6d71", "#4b5bdc", "#0cd36d",
            "#250662", "#cb5bea", "#228916", "#ac3e1b", "#df514a", "#539397", "#880977",
            "#f697c1", "#ba96ce", "#679c9d", "#c6c42c", "#5d2c52", "#48b41b", "#e1cf3b",
            "#5be4f0", "#57c4d8", "#a4d17a", "#be608b", "#96b00c", "#088baf", "#f158bf",
            "#e145ba", "#ee91e3", "#05d371", "#5426e0", "#4834d0", "#802234", "#6749e8",
            "#0971f0", "#8fb413", "#b2b4f0", "#c3c89d", "#c9a941", "#41d158", "#fb21a3",
            "#51aed9", "#5bb32d", "#21538e", "#89d534", "#d36647", "#7fb411", "#0023b8",
            "#3b8c2a", "#986b53", "#f50422", "#983f7a", "#ea24a3", "#79352c", "#521250",
            "#c79ed2", "#d6dd92", "#e33e52", "#b2be57", "#fa06ec", "#1bb699", "#6b2e5f",
            "#64820f", "#21538e", "#89d534", "#d36647", "#7fb411", "#0023b8", "#3b8c2a",
            "#986b53", "#f50422", "#983f7a", "#ea24a3", "#79352c", "#521250", "#c79ed2",
            "#d6dd92", "#e33e52", "#b2be57", "#fa06ec", "#1bb699", "#6b2e5f", "#64820f",
            "#9cb64a", "#996c48", "#9ab9b7", "#06e052", "#e3a481", "#0eb621", "#fc458e",
            "#b2db15", "#aa226d", "#792ed8", "#73872a", "#520d3a", "#cefcb8", "#a5b3d9",
            "#7d1d85", "#c4fd57", "#f1ae16", "#8fe22a", "#ef6e3c", "#243eeb", "#dd93fd",
            "#3f8473", "#e7dbce", "#421f79", "#7a3d93", "#635f6d", "#93f2d7", "#9b5c2a",
            "#15b9ee", "#0f5997", "#409188", "#911e20", "#1350ce", "#10e5b1", "#fff4d7",
            "#cb2582", "#ce00be", "#32d5d6", "#608572", "#c79bc2", "#00f87c", "#77772a",
            "#6995ba", "#fc6b57", "#f07815", "#8fd883", "#060e27", "#96e591", "#21d52e",
            "#d00043", "#b47162", "#1ec227", "#4f0f6f", "#1d1d58", "#947002", "#bde052",
            "#e08c56", "#28fcfd", "#36486a", "#d02e29", "#1ae6db", "#3e464c", "#a84a8f",
            "#911e7e", "#3f16d9", "#0f525f", "#ac7c0a", "#b4c086", "#c9d730", "#30cc49",
            "#3d6751", "#fb4c03", "#640fc1", "#62c03e", "#d3493a", "#88aa0b", "#406df9",
            "#615af0", "#2a3434", "#4a543f", "#79bca0", "#a8b8d4", "#00efd4", "#7ad236",
            "#7260d8", "#1deaa7", "#06f43a", "#823c59", "#e3d94c", "#dc1c06", "#f53b2a",
            "#b46238", "#2dfff6", "#a82b89", "#1a8011", "#436a9f", "#1a806a", "#4cf09d",
            "#c188a2", "#67eb4b", "#b308d3", "#fc7e41", "#af3101", "#71b1f4", "#a2f8a5",
            "#e23dd0", "#d3486d", "#00f7f9", "#474893", "#3cec35", "#1c65cb", "#5d1d0c",
            "#2d7d2a", "#ff3420", "#5cdd87", "#a259a4", "#e4ac44", "#1bede6", "#8798a4",
            "#d7790f", "#b2c24f", "#de73c2", "#d70a9c", "#88e9b8", "#c2b0e2", "#86e98f",
            "#ae90e2", "#1a806b", "#436a9e", "#0ec0ff", "#f812b3", "#b17fc9", "#8d6c2f",
            "#d3277a", "#2ca1ae", "#9685eb", "#8a96c6", "#dba2e6", "#76fc1b", "#608fa4",
            "#20f6ba", "#07d7f6", "#dce77a", "#77ecca"
        ];

        var color = rgbToHsl(hexToRGB(colorArray[colorIndex]));
        colorIndex++;
        return color;
    }

    function hexToRGB(h) {
        let r = 0,
            g = 0,
            b = 0;

        // 3 digits
        if (h.length == 4) {
            r = "0x" + h[1] + h[1];
            g = "0x" + h[2] + h[2];
            b = "0x" + h[3] + h[3];

            // 6 digits
        } else if (h.length == 7) {
            r = "0x" + h[1] + h[2];
            g = "0x" + h[3] + h[4];
            b = "0x" + h[5] + h[6];
        }

        return "rgb(" + +r + "," + +g + "," + +b + ")";
    }

    function rgbToHsl(rgbString) {
        // Extrai os valores R, G e B da string de entrada
        const rgbValues = rgbString.match(/\d+/g);
        const r = parseInt(rgbValues[0]);
        const g = parseInt(rgbValues[1]);
        const b = parseInt(rgbValues[2]);

        // Divide os valores R, G e B por 255 para normalizá-los no intervalo de 0 a 1
        const normalizedR = r / 255;
        const normalizedG = g / 255;
        const normalizedB = b / 255;

        // Calcula o valor máximo e mínimo entre R, G e B
        const max = Math.max(normalizedR, normalizedG, normalizedB);
        const min = Math.min(normalizedR, normalizedG, normalizedB);

        // Calcula a luminosidade (lightness)
        var lightness = (max + min) / 2;

        // Calcula a saturação (saturation)
        var delta = max - min;
        let saturation;
        if (max === min) {
            saturation = 0; // A cor é acromática (cinza)
        } else {
            saturation = delta / (1 - Math.abs(2 * lightness - 1));
        }

        // Calcula o matiz (hue)
        let hue;
        if (max === min) {
            hue = 0; // A cor é acromática (cinza)
        } else if (max === normalizedR) {
            hue = ((normalizedG - normalizedB) / delta) % 6;
        } else if (max === normalizedG) {
            hue = ((normalizedB - normalizedR) / delta) + 2;
        } else {
            hue = ((normalizedR - normalizedG) / delta) + 4;
        }

        // Converte o matiz para o intervalo de 0 a 360 graus
        hue = Math.round(hue * 60);
        if (hue < 0) {
            hue += 360;
        }

        // Converte a saturação e a luminosidade para o intervalo de 0 a 100
        saturation = Math.round(saturation * 100);
        lightness = Math.round(lightness * 100);

        // Retorna a string HSLA
        return `hsl(${hue}, ${saturation}%, ${lightness}%)`;
    }

    function getDarkerColor(color, percent) {
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

    function getTransparentedColor(color, opacity) {
        var hslRegex = /^hsl\((\d+),\s*(\d+)%,\s*(\d+)%\)$/i;
        var match = color.match(hslRegex);

        if (!match) {
            return color;
        }

        var h = parseInt(match[1]);
        var s = parseInt(match[2]);
        var l = parseInt(match[3]);

        // Verificar se a opacidade está no intervalo válido (0 a 1)
        if (opacity < 0 || opacity > 1) {
            console.error("A opacidade fornecida está fora do intervalo válido (0 a 1).");
            return;
        }

        // Construir a cor no formato HSLA (Hue, Saturation, Lightness, Alpha)
        const hslaColor = `hsla(${h}, ${s}%, ${l}%, ${opacity})`;

        return hslaColor;
    }

    function numberFormat(number, decimals = 2, decimalSeparator = ',', thousandSeparator = '.') {
        if(number == NaN || number == undefined || !number) return 0;
        const fixedNumber = number.toFixed(decimals);
        const [integerPart, decimalPart] = fixedNumber.split('.');
        
        const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);

        return `${formattedInteger}${decimalSeparator}${decimalPart}`;
    }

    function alternarBlur() {
        if(blurred) {
            blurred = false;
            $('#div_blurButton').html("<i class='bx bx-hide' ></i> Ocultar Valores")

            blurElements.forEach(div => {
                $(div).each((i, el) => {
                    $(el).removeClass("number-blur")
                });
            });

        } else {
            blurred = true;
            $('#div_blurButton').html("<i class='bx bx-show'></i> Reexibir Valores")

            blurElements.forEach(div => {
                $(div).each((i, el) => {
                    $(el).addClass("number-blur")
                });
            });
        }
    }
    </script>

</body>

</html>