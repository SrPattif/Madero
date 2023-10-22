<?php
    if(!isset($_SESSION)) {
        session_start();
    }

    include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

    $numeroDoMes = date('n');
    $nomesDosMeses = [ 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro' ];
    $nomeDoMes = $nomesDosMeses[$numeroDoMes - 1];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Síndicos | Controladoria Grupo Madero</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="./defaultStyle.css" />
    <link rel="stylesheet" href="./index.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php
    require($_SERVER['DOCUMENT_ROOT'] . '/ativos/sindicos/header.php');
    ?>
    <main>
        <div class="page-content">
            <div class="url-container">
                <div class="url" id="btn_copyUrl"><i class='bx bxs-copy'></i>
                    <span id="url_copy">https://<?php echo($_SERVER['HTTP_HOST']); ?>/formularios/sindicos/</span>
                </div>
            </div>
            <div class="double-cards">
                <div class="card card-button">
                    <div class="lines">
                        <div class="line"><i class='bx bxs-edit-alt'></i></div>
                        <div class="line">EDITAR</div>
                        <div class="line">FORMULÁRIO</div>
                    </div>
                </div>

                <div class="card card-button">
                    <div class="lines">
                        <div class="line"><i class='bx bxs-paper-plane'></i></div>
                        <div class="line">VER TODAS</div>
                        <div class="line">AS SOLICITAÇÕES</div>
                    </div>
                </div>
            </div>
            <div class="double-cards">
                <div class="card">
                    <div class="card-header">
                        <h1 id="div_qtdeMedicoes">Ativo</h1>
                        <span>Status do Formulário</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h1 id="div_qtdeMedicoes">0 usuários</h1>
                        <span>Ativos no Momento</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Novas Trocas de Síndico</h3>

                <table class="ranking-table">
                    <tr>
                        <th></th>
                        <th>#</th>
                        <th>Endereço da Moradia</th>
                        <th>Novo Síndico</th>
                    </tr>
                    <?php
                                $query = "SELECT ss.*, a.endereco, atv.nome FROM solicitacoes_sindicos ss LEFT JOIN alojamentos a ON ss.id_alojamento=a.id LEFT JOIN ativos atv ON ss.id_sindico=atv.id ORDER BY ss.id DESC LIMIT 10;";
                                $result = mysqli_query($mysqli, $query);
                                $rows = array();
                                while($row = mysqli_fetch_array($result)){
                                    array_push($rows, $row);
                                }
                                foreach($rows as $row) {
                                    $statusColor = "orange";
                                    switch ($row['situacao']) {
                                        case 'aprovado':
                                            $statusColor = "green";
                                            break;
                                        case 'pendente':
                                            $statusColor = "orange";
                                            break;
                                        case 'rejeitado':
                                            $statusColor = "red";
                                            break;
                                    }
                            ?>
                    <tr>
                        <td>
                            <div class="circle circle-<?php echo($statusColor); ?>"></div>
                        </td>
                        <td style="text-align: center;"><?php echo(intval($row['id'])); ?></td>
                        <td><?php echo($row['endereco']); ?></td>
                        <td><?php echo($row['nome']); ?></td>
                    </tr>
                    <?php
                                }
                            ?>
                </table>
            </div>
            <div class="double-cards">
                <div class="card">
                    <div class="card-header">
                        <h1 id="div_qtdeSindicos"><i class='bx bx-spin bx-loader-alt'></i></h1>
                        <span>Síndicos Cadastrados</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h1 id="div_qtdeTrocas"><i class='bx bx-spin bx-loader-alt'></i></h1>
                        <span>Trocas de Síndico</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h1 id="div_qtdeTrocasMes"><i class='bx bx-spin bx-loader-alt'></i></h1>
                        <span>Trocas de Síndico em <?php echo($nomeDoMes); ?></span>
                    </div>
                </div>
            </div>

            <div class="double-cards">
                <div class="card">
                    <div>
                        <canvas id="chart_sindicosMensais"></canvas>
                    </div>
                </div>
                <div class="card">
                    <div>
                        <canvas id="chart_sindicosMensais"></canvas>
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
    $(document).ready(() => {
        $('#btn_copyUrl').on('click', async () => {
            let text = $('#url_copy').text();
            try {
                await navigator.clipboard.writeText(text);
                tata.info('Link copiado', 'O link do formulário foi copiado para sua área de transferência.', {
                        duration: 3000
                    });
            } catch (err) {
                console.error('Failed to copy: ', err);
            }

        })
    })

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
        if (number == NaN || number == undefined || !number) return 0;
        const fixedNumber = number.toFixed(decimals);
        const [integerPart, decimalPart] = fixedNumber.split('.');

        const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);

        return `${formattedInteger}${decimalSeparator}${decimalPart}`;
    }
    </script>
    <script async>
    async function getData() {
        const request3 = $.ajax({
            type: "GET",
            url: "./index_resumoGeral.php",
            async: true,
        });

        const request2 = $.ajax({
            type: "GET",
            url: "./index_analiseTrocasMensal.php",
            async: true,
        });

        return Promise.all([request2, request3]);
    }

    function updateDOM(result) {
        $("#div_qtdeSindicos").text(result.sindicos_cadastrados);
        $("#div_qtdeTrocas").text(result.qtde_solicitacoes);
        $("#div_qtdeTrocasMes").text(result.qtde_solicitacoes_mes);
    }

    function loadGraph(requestData) {
        console.log(requestData)
        // Preparar os dados para o gráfico
        var labels = [];
        var datasets = [];

        for (var year in requestData) {
            for (var month in requestData[year]) {
                var dataValue = requestData[year][month];

                if (!dataValue) {
                    continue; // Ignorar meses sem dados
                }

                var dataset = datasets.find(function(dataset) {
                    return dataset.label === "Trocas de Sindico";
                });

                if (!dataset) {
                    var color = "#000000";
                    dataset = {
                        label: "Trocas de Sindico",
                        data: [],
                        borderColor: color,
                        backgroundColor: "#FFFFFF",
                        tension: 0.0
                    };
                    datasets.push(dataset);
                }

                dataset.data.push(dataValue);

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
                    text: 'Gráfico de Trocas Mensais'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        };

        // Criação do gráfico de linha
        var ctx = document.getElementById('chart_sindicosMensais').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: options
        });

    }

    $(document).ready(() => {
        getData()
            .then(results => {
                const [data2, data3] = results;
                updateDOM(data3);
                loadGraph(data2);
            })
            .catch(error => {
                console.error(error);
            });

    });
    </script>

</body>

</html>