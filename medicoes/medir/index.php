<?php
include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');
include('../../libs/databaseConnection.php');
include('../../libs/utils.php');

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
    $addressId = $_GET['addressId'];

} else {
    $newAddressId = pickRandomAddress($month, $year);

    if($newAddressId == -1) {
        header('location: ../allFilled');
        exit();
    }

    header('location: ./?year=' . $year . '&month=' . $month . '&addressId=' . $newAddressId);
    exit();
}

$addressData = getAddressData($addressId);

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

        <div class="header">
            <span><?php echo ($monthList[$month - 1] . "/" . $year); ?></span>
            <h2>Preencher cobrança de condomínio</h2>
            <p>Informe as cobranças presentes no boleto de condomínio.</p>
            <?php
                if(hasActiveMeditions($addressId, $month, $year)) {
            ?>
                <div class="warning">Este alojamento já possui medições ativas.<br>Inserir uma nova medição agora irá excluir as outras medições ativas.</div>
                <div class="address" style="border-color: #eea302;"><?php echo($addressData['endereco']); ?></div>
            <?php
                } else {
            ?>
                <div class="address"><?php echo($addressData['endereco']); ?></div>
            <?php
                }
            ?>
        </div>

        <div id="taxes-list">
            <div class="tax">
                <div class="tax-type">
                    <span>Condomínio</span>
                </div>

                <div class="tax-value">
                    <input type="text" id="tax-value" value="R$ 0" oninput="formatarValor('tax-value')" autofocus>
                </div>
            </div>
        </div>

        <div id="add-tax-btn" class="add-tax-btn">
            <i class='bx bx-plus'></i>
        </div>

        <div class="continue-btn" id="continue-button">
            PRÓXIMO <i class='bx bx-right-arrow-alt'></i>
        </div>
        <div class="home-btn">
            <a href="/">VOLTAR AO INÍCIO</a>
        </div>

    </div>



    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script>
        function formatarValor(inputId) {
            let input = document.getElementById(inputId);
            let valor = input.value.replace(/\D/g, '');

            valor = (parseFloat(valor) / 100).toFixed(2).toString().replace('.', ',');
            valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');

            if (valor == "NaN") {
                valor = 0;
            }

            input.value = 'R$ ' + valor;
        }


        $('#continue-button').on('click', () => {
            var taxData = $(".tax").map(function() {
                var taxTypeElement = $(this).find(".tax-type");
                var taxTypeValue;
                var inputValue;

                if (taxTypeElement.find("select").length > 0) {
                    // Se houver um select dentro da div "tax-type"
                    taxTypeValue = taxTypeElement.find("select").val();
                } else if (taxTypeElement.find("span").length > 0) {
                    // Se houver um span dentro da div "tax-type"
                    taxTypeValue = 1;
                }

                inputValue = $(this).find("input").val().replace(/\D/g, '');
                inputValue = parseFloat(inputValue.slice(0, -2) + '.' + inputValue.slice(-2))

                return {
                    id: taxTypeValue,
                    value: inputValue
                };
            }).get();

            var url = new URL(window.location.href);
            var searchParams = new URLSearchParams(url.search);

            var year = searchParams.get('year');
            var month = searchParams.get('month');
            var addressId = searchParams.get('addressId');
            var callback = searchParams.get('callback');

            var obj = {
                'addressId': addressId,
                'year': year,
                'month': month,
                'taxes': taxData
            }

            let hasError;

            obj.taxes.forEach(element => {
                if(element.id == "reject") {
                    tata.error('Selecione o tipo de taxa', 'Existem campos que não possuem o tipo da taxa selecionada.', {
                        duration: 6000
                    });
                    hasError = true;
                    return;

                } else if (element.value <= 0) {
                    tata.error('Informe o valor das taxas', 'Existem campos que não possuem o valor das taxas.', {
                        duration: 6000
                    });
                    hasError = true;
                    return;
                }

            });

            if(hasError) return;

            $.ajax({
                type: "POST",
                url: "./createMedition.php",
                data: obj,
                success: function(result) {
                    tata.success('Medição bem sucedida', 'A medição foi bem sucedida.', {
                        duration: 1500
                    });

                    setTimeout(() => {
                        if(callback) {
                            window.location.href = callback;
                        } else {
                            window.location.href = `./?year=${year}&month=${month}`
                        }
                    }, 1500);

                },
                error: function(result) {
                    console.error(result);
                }
            });

            console.log(obj);
        });

        $("#add-tax-btn").click(function() {
            var taxCount = $(".tax").length;
            if (taxCount >= 7) {
                tata.error('Limite de taxas excedido', 'Existem muitos campos de taxas criados.', {
                    duration: 6000
                });
                return; // Retorna o código, evitando adicionar uma nova div "tax"
            }

            $.ajax({
                url: "taxInput.php",
                type: "GET",
                dataType: "html",
                success: function(data) {
                    var taxHTML = $(data).filter(".tax");
                    $("#taxes-list").append(taxHTML);
                }
            });
        });

        $(document).on("click", ".tax-remove", function() {
            $(this).closest(".tax").remove();
        });
    </script>

</body>

</html>