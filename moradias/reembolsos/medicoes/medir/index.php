<?php
include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');
include($_SERVER['DOCUMENT_ROOT'] . '/libs/utils.php');

$year = $_GET['year'];
$month = $_GET['month'];

if (!is_numeric($year) || !is_numeric($month)) {
    header('location: ../iniciar/');
    exit();
}

$year = (int) $year;
$month = (int) $month;

if ($year < 2023 || $year > 2024) {
    header('location: ../iniciar/');
    exit();
}

if ($month <= 0 || $month > 12) {
    header('location: ../iniciar/');
    exit();
}

$addressId = 0;
if(isset($_GET['addressId'])) {
    $addressId = mysqli_real_escape_string($mysqli, $_GET['addressId']);

} else {
    $query = "SELECT DISTINCT a.* FROM alojamentos a LEFT JOIN alojamentos_valores_reembolso ar ON a.id = ar.id_alojamento AND ar.ano = {$year} AND ar.mes = {$month} INNER JOIN boletos b ON b.id_alojamento = a.id AND MONTH(b.data_vencimento)={$month} AND YEAR(b.data_vencimento)={$year} WHERE ar.id_alojamento IS NULL;";
    $result = mysqli_query($mysqli, $query);
    $rows = array();
    while($row = mysqli_fetch_array($result)){
        array_push($rows, $row);
    }

    if (empty($rows)) {
        header('location: ../completo/');
        exit();
    }

    $addressId = $rows[0]['id'];
}

$query = "SELECT a.*, b.nome_interno AS arquivo_boleto FROM alojamentos a LEFT JOIN boletos b ON b.id_alojamento = a.id AND MONTH(b.data_vencimento)={$month} AND YEAR(b.data_vencimento)={$year} WHERE a.id = {$addressId};";
$result = mysqli_query($mysqli, $query);
$row = mysqli_num_rows($result);

if ($row > 1) {
    header('location: ../iniciar/');
    exit();
}
$addressData = mysqli_fetch_assoc($result);

$queryTaxas = "SELECT * FROM tipos_taxas WHERE id != 1 ORDER BY refundable DESC, id ASC;"; // Obter todas as taxas, exceto condomínio.
$resultTaxas = mysqli_query($mysqli, $queryTaxas);
$rowsTaxas = array();
while($row = mysqli_fetch_array($resultTaxas)){
    array_push($rowsTaxas, $row);
}

$monthList = ["janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moradias Grupo Madero | Preenchimento</title>

    <link rel="stylesheet" href="./index.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <div class="page-content">
        <div class="container">

            <div class="left-content">
                <?php
                    if(empty($addressData['arquivo_boleto'])) {
                ?>
                <div class="vertical-center"><span>
                        <i class='bx bxs-error'></i>
                        <br>
                        A moradia não possui um boleto de condomínio cadastrado.
                    </span></div>
                <?php
                    } else {
                ?>
                <embed src="/uploads/<?php echo($addressData['arquivo_boleto']); ?>">
                <?php 
                    }
                ?>
            </div>

            <div class="right-content">
                <div class="header">
                    <span><?php echo ($monthList[$month - 1] . "/" . $year); ?></span>
                    <h2>Preencher cobrança de condomínio</h2>
                    <p>Informe as cobranças presentes no boleto de condomínio.</p>
                    <?php
                if(hasActiveMeditions($addressId, $month, $year)) {
            ?>
                    <div class="warning">Este alojamento já possui medições ativas.<br>Inserir uma nova medição
                        agora
                        irá
                        excluir as outras medições ativas.</div>
                    <div class="address" style="border-color: #eea302;"><?php echo($addressData['endereco']); ?>
                    </div>
                    <?php
                } else {
            ?>
                    <div class="address"><?php echo($addressData['endereco']); ?></div>
                    <?php
                }
            ?>
                </div>

                <div id="taxes-list" class="taxes-list">
                    <div class="tax">
                        <div class="tax-type">
                            <span>1 : Condomínio</span>
                        </div>

                        <div class="tax-value">
                            <input type="text" id="input_produtoStarter" value="R$ 0" oninput="formatarValor('input_produtoStarter')"
                                autofocus>
                        </div>
                    </div>
                </div>

                <div id="add-tax-btn" class="add-tax-btn">
                    <i class='bx bx-plus'></i>
                </div>

                <div class="valor-boleto">
                    Valor Total: <span class="bold" id="valor-total">R$ 0,00</span>
                </div>

                <div class="continue-btn" id="continue-button">
                    PRÓXIMO <i class='bx bx-right-arrow-alt'></i>
                </div>
                <div class="home-btn">
                    <a href="/moradias/reembolsos/">VOLTAR AO INÍCIO</a>
                </div>
            </div>
        </div>

        <div id="modal_produto" class="modal">
            <div class="modal-content">
                <h2>Selecionar Produto</h2>
                <span>Selecione o produto desejado.</span>
                <div class="filter">
                    <input type="text" id="filterInput" oninput="filtrarProdutos()"
                        placeholder="Digite o texto para filtrar">
                </div>
                <div class="painel-produtos" id="painel-produtos">
                    <ul>
                        <?php
                            foreach($rowsTaxas as $row) {
                                $idTaxa = $row['id'];
                                $protheusTaxa = $row['codigo_protheus'];
                                $naturezaTaxa = $row['natureza'];
                                $reembolsavelTaxa = boolval($row['refundable']);
                                $nomeTaxa = $row['description'];
                        ?>
                        <li onclick="addTaxModal(<?php echo((int) $idTaxa); ?>)">
                            <span class="nome-produto" id="span_nomeProduto"><?php echo($nomeTaxa); ?></span>
                            <div class="desc-produto">
                                <span>Código Interno: <span class="bold"><?php echo((int) $idTaxa); ?></span></span>
                                <span>Produto Protheus: <span class="bold"><?php echo($protheusTaxa); ?></span></span>
                                <span>Natureza: <span class="bold"><?php echo($naturezaTaxa); ?></span></span>
                                <span>Reembolsável: <span class="bold"
                                        style="color: <?php echo($reembolsavelTaxa ? "#00AA00" : "#AA0000"); ?>;"><?php echo($reembolsavelTaxa ? "SIM" : "NÃO"); ?></span></span>
                            </div>
                        </li>
                        <?php
                            }
                        ?>
                    </ul>
                </div>
                <div class="button" onclick="closeModal('modal_produto')" style="width: 100%;">FECHAR</div>
            </div>
        </div>


    </div>



    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $(document).keypress(function(event) {
            if (event.keyCode === 13) {
                const $lis = $('#modal_produto ul li');
                const $visibleLis = $lis.filter(':visible');
                if ($visibleLis.length === 1) {
                    $visibleLis.click();
                }

            } else if (event.keyCode == 61) {
                event.preventDefault();
                $("#add-tax-btn").click();
                $('#filterInput').focus();
            }
        });
    });

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

    function formatarValor(inputId) {
        let input = document.getElementById(inputId);
        let valor = input.value.replace(/\D/g, '');

        valor = (parseFloat(valor) / 100).toFixed(2).toString().replace('.', ',');
        valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');

        if (valor == "NaN") {
            valor = 0;
        }

        input.value = 'R$ ' + valor;

        // calculando valor total e mudando SPAN
        let total = 0;

        // Seleciona todos os inputs cujo ID começa com "input_produto"
        $('input[id^="input_produto"]').each(function() {
            // Extrai o valor decimal do input
            let valorDecimal = parseFloat($(this).val().replace(/\D/g, '').replace(',', '.')) / 100;
            console.log(valorDecimal);

            // Soma o valor decimal ao total
            total += valorDecimal;
        });

        let currencyFormatter = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
        });

        let valorTotalFormatado = currencyFormatter.format(total)

        // Atualiza o texto do elemento "span" com o ID "valor-total"
        $("#valor-total").text(valorTotalFormatado);
    }


    $('#continue-button').on('click', () => {
        var taxObj = [];
        $(".tax").map(function() {
            var taxTypeElement = $(this).find(".tax-type");
            var taxTypeValue;
            var inputValue;

            if (taxTypeElement.find("span").length == 1) {
                var taxTypeFullName = taxTypeElement.find("span")[0].textContent;
                taxTypeValue = taxTypeFullName.split(" : ")[0];
            }

            inputValue = $(this).find("input").val().replace(/\D/g, '');
            inputValue = parseFloat(inputValue.slice(0, -2) + '.' + inputValue.slice(-2))

            taxObj.push({
                "id": taxTypeValue,
                "value": inputValue
            })
        }).get();

        var url = new URL(window.location.href);
        var searchParams = new URLSearchParams(url.search);

        var year = searchParams.get('year');
        var month = searchParams.get('month');
        var addressId = <?php echo((int) $addressId); ?>;
        var callback = searchParams.get('callback');

        var obj = {
            'addressId': addressId,
            'year': year,
            'month': month,
            'taxes': taxObj
        }

        let hasError;

        obj.taxes.forEach(element => {
            if (element.id == "reject") {
                tata.error('Selecione o tipo de taxa',
                    'Existem campos que não possuem o tipo da taxa selecionada.', {
                        duration: 6000
                    });
                hasError = true;
                return;

            } else if (element.value <= 0) {
                tata.error('Informe o valor das taxas',
                    'Existem campos que não possuem o valor das taxas.', {
                        duration: 6000
                    });
                hasError = true;
                return;
            }

        });

        if (hasError) return;

        $.ajax({
            type: "POST",
            url: "./createMedition.php",
            data: obj,
            success: function(result) {
                tata.success('Medição bem sucedida', 'A medição foi bem sucedida.', {
                    duration: 1000
                });

                setTimeout(() => {
                    if (callback) {
                        window.location.href = callback;

                    } else {
                        window.location.href = `./?year=${year}&month=${month}`
                    }
                }, 1000);

            },
            error: function(result) {
                console.error(result);
            }
        });

        console.log(obj);
    });

    function filtrarProdutos() {
        var textoFiltro = removerAcentos(document.getElementById('filterInput').value.toLowerCase());

        var listaProdutos = document.getElementById('painel-produtos').getElementsByTagName('li');

        if (listaProdutos.length > 0) {
            for (var i = 0; i < listaProdutos.length; i++) {
                var nomeProduto = listaProdutos[i].querySelector('#span_nomeProduto').textContent.toLowerCase();

                if (removerAcentos(nomeProduto).includes(textoFiltro)) {
                    listaProdutos[i].style.display = 'block';
                } else {
                    listaProdutos[i].style.display = 'none';
                }
            }
        }
    }


    $("#add-tax-btn").click(function() {
        var taxCount = $(".tax").length;
        if (taxCount >= 12) {
            tata.error('Limite de taxas excedido', 'Existem muitos campos de taxas criados.', {
                duration: 6000
            });
            return;
        }

        abrirModal('modal_produto');
        $('#filterInput').val("");
        filtrarProdutos();
        $('#filterInput').focus();


    });

    function addTaxModal(taxId) {
        closeModal('modal_produto');
        addTax(taxId);
    }

    function addTax(taxId) {
        fetch("./productData.php?idProduto=" + taxId)
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    var taxDiv = document.createElement("div");
                    taxDiv.classList.add("tax");

                    var removeDiv = document.createElement("div");
                    removeDiv.classList.add("tax-remove");
                    removeDiv.innerHTML = "<i class='bx bx-x'></i>";

                    var typeDiv = document.createElement("div");
                    typeDiv.classList.add("tax-type");

                    var typeSpan = document.createElement("span");
                    typeSpan.textContent = `${parseInt(data.dadosProduto.id)} : ${data.dadosProduto.nomeTaxa}`;

                    typeDiv.appendChild(typeSpan);

                    var valueDiv = document.createElement("div");
                    valueDiv.classList.add("tax-value");

                    var valueInput = document.createElement("input");
                    valueInput.id = `input_produto${data.dadosProduto.id}`;
                    valueInput.value = "R$ 0";
                    valueInput.setAttribute("oninput", `formatarValor('input_produto${data.dadosProduto.id}')`);

                    // Adiciona o input dentro da div de valor
                    valueDiv.appendChild(valueInput);

                    // Adiciona as divs dentro da div de imposto
                    taxDiv.appendChild(removeDiv);
                    taxDiv.appendChild(typeDiv);
                    taxDiv.appendChild(valueDiv);

                    // Adiciona a div de imposto dentro de taxes-list
                    var taxesList = document.getElementById("taxes-list");
                    taxesList.appendChild(taxDiv);

                    valueInput.focus();
                }
            })
            .catch(error => {
                // Trate erros que possam ocorrer durante a solicitação
                console.error('Ocorreu um erro:', error);
            });
    }

    $(document).on("click", ".tax-remove", function() {
        $(this).closest(".tax").remove();
    });

    function getRandomNum(minimo, maximo) {
        return Math.floor(Math.random() * (maximo - minimo + 1)) + minimo;
    }

    function removerAcentos(texto) {
        return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }
    </script>

</body>

</html>