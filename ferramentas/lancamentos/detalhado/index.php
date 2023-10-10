<?php
    if(!isset($_SESSION)) {
        session_start();
    }

    include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

    $colunasPesquisa = array("documento", "ctt_custo", "cod_fornecedor", "nome_fornecedor", "valor_liquido", "loja_fornecedor", "contrato", "natureza", "numero_bordero", "data_bordero", "data_vencimento", "data_baixa");

    $conditions = array();

    foreach ($colunasPesquisa as $filtro) {
        if (!empty($_GET[$filtro])) {
            $conditions[] = $filtro . " = '" . mysqli_real_escape_string($mysqli, $_GET[$filtro]) . "'";
        }
    }

    $queryRazao = "SELECT * FROM razao";
    $rowsRazao = array();
    if (!empty($conditions)) {
        $queryRazao .= " WHERE " . implode(" AND ", $conditions);
    }
    $queryRazao .= " ORDER BY documento DESC LIMIT 75;";
    $resultRazao = mysqli_query($mysqli, $queryRazao);
    while($row = mysqli_fetch_array($resultRazao)){
        array_push($rowsRazao, $row);
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
    <title>Controladoria Grupo Madero | Pesquisa Avançada</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="../defaultStyle.css" />
    <link rel="stylesheet" href="./index.css" />
    <link rel="stylesheet" href="/assets/styles/tooltips.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php
    require($_SERVER['DOCUMENT_ROOT'] . '/ferramentas/lancamentos/header.php');
    ?>

    <main>
        <div class="overlay" id="overlay">
            <div class="overlay__inner">
                <div class="overlay__content">
                    <span class="spinner"></span>
                    <br>
                    Aguarde enquanto carregamos lançamentos
                </div>
            </div>
        </div>

        <div class="page-content">
            <!--
            <div class="testing-card">
                <div class="card-header">
                    <h3><i class='bx bx-test-tube bx-tada bx-rotate-270' ></i> Módulo em Testes</h3>
                </div>
                Este módulo ainda está em fase de testes e <span class="bold">pode apresentar erros, problemas ou inconsistências</span>.
                <br>Certifique-se de conferir todas as informações fornecidas antes de prosseguir.
                <br>
                <br>Se você <span class="bold">encontrar algum erro</span> ou divergência, <span class="bold">entre em contato</span> com o administrador do sistema.
            </div>
            -->

            <div class="card">
                <div class="card-header">
                    <h3>Pesquisa Avançada de Lançamentos</h3>
                </div>
                <div class="card-description">
                    <span>Insira abaixo o item que deseja procurar. Serão procurados por Centro de Custo, Fornecedor e
                        Contrato.<br>
                        Quando terminar a digitação, pressione <span class="key-input"><i class='bx bxs-keyboard'></i>
                            ENTER</span> ou
                        clique em <span class="key-input"><i class='bx bx-chevrons-right'></i> PROSSEGUIR</span>.</span>
                </div>

                <div class="input-lcto">
                    <!--
                    <div class="button" onclick="manusearInput();"><i class='bx bx-chevrons-right'></i></div>
                    -->
                    <div class="double-inputs">
                        <div class="input-group" style="width: 100%;">
                            <label for="input_documento">Número do Lançamento</label>
                            <input type="text" id="input_documento" value="<?php  if(!empty($_GET["documento"])) echo($_GET["documento"]); ?>">
                        </div>
                        <div class="input-group" style="width: 100%;">
                            <label for="input_ctt_custo">Centro de Custo</label>
                            <input type="text" id="input_ctt_custo" value="<?php  if(!empty($_GET["ctt_custo"])) echo($_GET["ctt_custo"]); ?>">
                        </div>
                        <div class="input-group" style="width: 100%;">
                            <label for="input_cod_fornecedor">Código do Fornecedor</label>
                            <input type="text" id="input_cod_fornecedor" value="<?php  if(!empty($_GET["cod_fornecedor"])) echo($_GET["cod_fornecedor"]); ?>">
                        </div>
                        <div class="input-group" style="width: 100%;">
                            <label for="input_nome_fornecedor">Nome do Fornecedor</label>
                            <input type="text" id="input_nome_fornecedor" value="<?php  if(!empty($_GET["nome_fornecedor"])) echo($_GET["nome_fornecedor"]); ?>">
                        </div>
                        <div class="input-group" style="width: 100%;">
                            <label for="input_valor_liquido">Valor</label>
                            <input type="text" id="input_valor_liquido" value="<?php  if(!empty($_GET["valor_liquido"])) echo($_GET["valor_liquido"]); ?>">
                        </div>

                    </div>
                    <div class="double-inputs">
                        <div class="input-group" style="width: 100%;">
                            <label for="input_loja_fornecedor">Loja do Fornecedor</label>
                            <input type="text" id="input_loja_fornecedor" value="<?php  if(!empty($_GET["loja_fornecedor"])) echo($_GET["loja_fornecedor"]); ?>">
                        </div>
                        <div class="input-group" style="width: 100%;">
                            <label for="input_contrato">Contrato</label>
                            <input type="text" id="input_contrato" value="<?php  if(!empty($_GET["contrato"])) echo($_GET["contrato"]); ?>">
                        </div>
                        <div class="input-group" style="width: 100%;">
                            <label for="input_natureza">Código da Natureza</label>
                            <input type="text" id="input_natureza" value="<?php  if(!empty($_GET["natureza"])) echo($_GET["natureza"]); ?>">
                        </div>
                        <div class="input-group" style="width: 100%;">
                            <label for="input_numero_bordero">Número do Borderô</label>
                            <input type="text" id="input_numero_bordero" value="<?php  if(!empty($_GET["numero_bordero"])) echo($_GET["numero_bordero"]); ?>">
                        </div>
                    </div>
                    <div class="double-inputs">
                        <div class="input-group" style="width: 100%;">
                            <label for="input_data_bordero">Data do Borderô</label>
                            <input type="text" id="input_data_bordero" value="<?php  if(!empty($_GET["data_bordero"])) echo($_GET["data_bordero"]); ?>">
                        </div>
                        <div class="input-group" style="width: 100%;">
                            <label for="input_data_vencimento">Data de Vencimento</label>
                            <input type="text" id="input_data_vencimento" value="<?php  if(!empty($_GET["data_vencimento"])) echo($_GET["data_vencimento"]); ?>">
                        </div>
                        <div class="input-group" style="width: 100%;">
                            <label for="input_data_baixa">Data de Baixa</label>
                            <input type="text" id="input_data_baixa" value="<?php  if(!empty($_GET["data_baixa"])) echo($_GET["data_baixa"]); ?>">
                        </div>
                    </div>
                    <div class="button" onclick="manusearInput();">Filtrar <i class='bx bx-chevrons-right'></i></div>
                </div>
                <div class="mensagem-inicial" id="div_msgInicial">
                    <i class='bx bx-chevrons-up'></i>
                    Insira acima os filtros que deseja utilizar.
                    <i class='bx bx-chevrons-up'></i>
                </div>

                <?php
                    if(sizeof($rowsRazao) > 0) {
                ?>
                <div class="lista-lancamentos">
                    <table>
                        <tr>
                            <th></th>
                            <th>Documento</th>
                            <th>Contrato</th>
                            <th>Filial</th>
                            <th>Fornecedor</th>
                            <th>Emissão</th>
                            <th>Borderô</th>
                            <th>Baixa</th>
                            <th>Valor</th>
                            <th>Natureza</th>
                            <th></th>
                        </tr>

                        <?php
                            foreach($rowsRazao as $lcto) {
                                $emissionDate = "-";
                                $datePaid = "-";
                                $bordero = "-";
                                $comprovante = "";
                                if(!empty($lcto['data_emissao'])) {
                                    $emissionDateObj = date_create($lcto['data_emissao']);
                                    $emissionDate = date_format($emissionDateObj, "d/m/Y");

                                    if(!empty($lcto['data_baixa']) && $lcto['data_baixa'] != "0000-00-00") {
                                        $statusColor = "status-orange";
                                        $paidDateObj = date_create($lcto['data_baixa']);
                                        $datePaid = date_format($paidDateObj, "d/m/Y");

                                    } else {
                                        $statusColor = "status-red";
                                    }

                                    if(!empty($lcto['comprovante_pagamento'])) {
                                        $comprovante = $lcto['comprovante_pagamento'];
                                        $statusColor = "status-green";
                                    }
                                }

                                if(!empty($lcto['numero_bordero']) && $lcto['numero_bordero'] != 0) {
                                    $bordero = $lcto['numero_bordero'];
                                }
                        ?>
                        <tr>
                            <td style="text-align: center;">
                                <div class="status <?php echo($statusColor); ?>"></div>
                            </td>
                            <td style="font-weight: bold; text-align: center;"><?php echo($lcto['documento']); ?></td>
                            <td style="text-align: center;"><?php echo($lcto['contrato']); ?></td>
                            <td><?php echo($lcto['descr_filial']); ?></td>
                            <td><?php echo($lcto['cod_fornecedor'] . ' :: ' . $lcto['nome_fornecedor']); ?></td>
                            <td style="text-align: center;"><?php echo($emissionDate); ?></td>
                            <td style="text-align: center;"><?php echo($bordero); ?></td>
                            <td style="text-align: center;"><?php echo($datePaid); ?></td>
                            <td style="font-weight: bold; text-align: center;">
                                <?php echo('R$ ' . number_format($lcto['valor_unitario'], 2, ",", ".")); ?></td>
                            <td><?php echo($lcto['descr_natureza']); ?></td>
                            <td>
                                <div class="options-list">
                                    <?php
                                        if($datePaid != "-" && !empty($comprovante)) {
                                    ?>
                                    <div class="option">
                                        <span data-tooltip="Comprovante de Pagamento" data-flow="left"
                                            style="text-align: center; z-index: 999;">
                                            <a href="/uploads/<?php echo($comprovante); ?>" target="_blank"><i
                                                    class='bx bx-file-blank'></i></a>
                                        </span>
                                    </div>
                                    <?php
                                        } else if($datePaid != "-" && empty($comprovante)) {
                                    ?>
                                    <div class="option">
                                        <span data-tooltip="Apurar Comprovante" data-flow="left"
                                            style="text-align: center; z-index: 999;">
                                            <a onclick="apurarComprovante('<?php echo($lcto['documento']); ?>')"><i
                                                    class='bx bx-file-find'></i></a>
                                        </span>
                                    </div>
                                    <?php
                                        } else {
                                    ?>
                                    <div class="option">
                                        <span data-tooltip="Comprovante Indisponível" data-flow="left"
                                            style="text-align: center; z-index: 999;">
                                            <a style="cursor: not-allowed;"><i style="color: #AA0000;"
                                                    class='bx bx-block'></i></a>
                                        </span>
                                    </div>
                                    <?php
                                        }
                                    ?>
                                </div>
                            </td>
                        </tr>
                        <?php
                            }
                        ?>
                    </table>
                </div>
                <?php
                    } else if(strlen($queryRazao) > 0) {
                ?>
                <div class="card error-card" style="font-size: 1rem;">
                    <h3 class="title">Sem resultados</h3>
                    Nenhum titulo foi encontrado nos filtros informados.
                </div>
                <?php
                    }
                ?>
            </div>
    </main>

    <?php
    //require('footer.php');
    ?>

    <script src="/mobile-navbar.js"></script>
    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script>
    function abrirModal(modalId) {
        $("#" + modalId).css("display", "flex");

        setTimeout(function() {
            $("#" + modalId).addClass("show");
        }, 10);
    }

    function closeModal(modalId) {
        $("#" + modalId).removeClass("show");

        setTimeout(function() {
            $("#" + modalId).css("display", "none");
        }, 300);
    }

    $(document).ready(function() {
        $(document).on("keyup", function(event) {
            if (event.keyCode === 27) {
                $('[id^="modal_"]').removeClass("show");

                setTimeout(function() {
                    $('[id^="modal_"]').css("display", "none");
                }, 300);
            }
        });
    });

    function manusearInput() {
        var queryParams = {};
        
        // Itera sobre os inputs que têm IDs que começam com "input_"
        $('input[id^="input_"]').each(function() {
            var columnName = $(this).attr("id").replace("input_", ""); // Extrai o nome da coluna
            var columnValue = $(this).val();
            
            // Verifica se o valor não está vazio e adiciona aos parâmetros da consulta
            if (columnValue !== "") {
                queryParams[columnName] = columnValue;
            }
        });
        
        // Construa a URL com base nos parâmetros da consulta
        var url = "?";
        for (var key in queryParams) {
            url += key + "=" + encodeURIComponent(queryParams[key]) + "&";
        }
        
        // Remova o último "&" da URL, se houver
        if (url.charAt(url.length - 1) === "&") {
            url = url.slice(0, -1);
        }
        
        // Redirecione o usuário para a nova URL
        console.log(url);
        window.location.href = url;
    }

    function formatISODateToCustomFormat(isoDateTime) {
        if (!isValidISODate(isoDateTime)) {
            return "-";
        }
        // Criar um objeto Date a partir da data ISO
        var jsDate = new Date(isoDateTime);

        // Ajustar para o fuso horário local
        var timeZoneOffset = jsDate.getTimezoneOffset(); // Em minutos
        jsDate.setMinutes(jsDate.getMinutes() + timeZoneOffset);

        // Função para adicionar um zero à esquerda, se necessário
        function addLeadingZero(number) {
            return number < 10 ? "0" + number : number;
        }

        // Obter o dia, mês e ano da data
        var day = jsDate.getDate();
        var month = jsDate.getMonth() + 1; // Os meses em JavaScript são baseados em zero
        var year = jsDate.getFullYear();

        // Formatando para "dd/mm/aaaa"
        var formattedDate = `${addLeadingZero(day)}/${addLeadingZero(month)}/${year}`;

        return formattedDate;
    }

    function isValidISODate(dateStr) {
        const date = new Date(dateStr);
        // Verifica se o valor não é inválido e se não é igual a "-0001-11-30T00:00:00Z"
        return !isNaN(date) && date.toISOString() !== "-0001-11-30T00:00:00.000Z";
    }

    $(document).ready(function() {
        $(document).on("keyup", function(event) {
            if (event.keyCode === 13) {
                manusearInput();
            }
        });
    });

    function numberFormat(number, decimals = 2, decimalSeparator = ',', thousandSeparator = '.') {
        const fixedNumber = number.toFixed(decimals);
        const [integerPart, decimalPart] = fixedNumber.split('.');

        const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);

        return `${formattedInteger}${decimalSeparator}${decimalPart}`;
    }

    function apurarComprovante(titulo) {
        $("#overlay").show();
        $.ajax({
            type: "POST",
            url: "../apurarComprovante.php",
            data: {
                titulo: titulo,
            },
            success: function(result) {
                $("#overlay").hide();

                tata.success('Comprovante apurado',
                    'O comprovante de pagamento foi apurado com sucesso.', {
                        duration: 6000
                    });

                setTimeout(() => {
                    window.location.reload();
                }, 500);
                return;
            },
            error: function(result) {
                $("#overlay").hide();

                tata.error('Um erro ocorreu',
                    'Ocorreu um erro ao tentar apurar o comprovante de pagamento. (' + result
                    .responseJSON.descricao_erro + ')', {
                        duration: 6000
                    });
            }
        });
    }
    </script>

</body>

</html>