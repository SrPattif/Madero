<?php
    include('../../libs/databaseConnection.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');

    $houseId = $_GET['id_alojamento'];
    
    if (!is_numeric($houseId)) {
        header('location: ../');
        exit();
    }

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

    $query = "SELECT a.*, b.id AS id_boleto, b.codigo_interno, b.nome_interno AS arquivo_boleto, b.nome_original AS nome_boleto, b.arquivo_comprovante, b.data_vencimento, r.data_baixa, r.valor_total FROM alojamentos a LEFT JOIN boletos b ON b.id_alojamento=a.id AND MONTH(data_vencimento) = {$month} AND YEAR(data_vencimento) = {$year} LEFT JOIN razao r ON r.documento=b.lancamento WHERE a.id = {$houseId};";
    $result = mysqli_query($mysqli, $query);
    if(mysqli_num_rows($result) != 1) {
        header('location: ../');
        exit();
    }

    $queryMedicoes = "SELECT avr.*, tt.description AS nome_taxa, tt.refundable AS reembolsavel FROM alojamentos_valores_reembolso avr INNER JOIN tipos_taxas tt ON avr.id_taxa=tt.id WHERE mes={$month} AND ano={$year} AND id_alojamento={$houseId};";
    $resultMedicoes = mysqli_query($mysqli, $queryMedicoes);
    $rowsMedicoes = array();
    while($row = mysqli_fetch_array($resultMedicoes)){
        array_push($rowsMedicoes, $row);
    }

    $houseData = mysqli_fetch_assoc($result);

    
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moradias Grupo Madero | Visualizar Moradia</title>

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
                <div class="card-header">
                    <h3>Detalhes da Moradia</h3>
                </div>
                <form action="">
                    <div class="double-inputs">
                        <div class="input-group" style="width: 50%;">
                            <label for="input_endereco">Endereço da Moradia</label>
                            <input type="text" id="input_endereco" value="<?php echo($houseData['endereco']); ?>">
                        </div>
                        <div class="input-group" style="width: 25%;">
                            <label for="input_contrato">Contrato</label>
                            <input type="text" id="input_contrato" value="<?php echo($houseData['contrato_totvs']); ?>">
                        </div>

                        <div class="input-group" style="width: 25%;">
                            <label for="input_digFinanceiro">Digito Financeiro</label>
                            <input type="text" id="input_digFinanceiro"
                                value="<?php echo($houseData['digito_financeiro']); ?>">
                        </div>
                    </div>

                    <div class="double-inputs">
                        <div class="input-group" style="width: 50%;">
                            <label for="input_status">Status</label>
                            <input type="text" id="input_status" value="<?php echo($houseData['status']); ?>">
                        </div>
                        <div class="input-group" style="width: 50%;">
                            <label for="input_centroCusto">Centro de Custo</label>
                            <input type="text" id="input_centroCusto"
                                value="<?php echo($houseData['centro_custo']); ?>">
                        </div>
                        <div class="input-group" style="width: 50%;">
                            <label for="input_operacao">Operação</label>
                            <input type="text" id="input_operacao" value="<?php echo($houseData['operacao']); ?>">
                        </div>
                    </div>

                    <button class="button" style="margin-bottom: 2em;">SALVAR</button>
                </form>

                <div class="file-container">
                    <?php
                            if(empty($houseData['id_boleto'])) {
                        ?>
                    <div class="no-file">
                        <span>Boleto de condominio não encontrado.</span>
                    </div>
                    <?php
                            } else {
                                $amount = "R$ " . number_format($houseData['valor_total'], 2, ",", ".");
                                $expiresDate = date_format(date_create($houseData['data_vencimento']), "d/m/Y");
                                $datePaid = date_format(date_create($houseData['data_baixa']), "d/m/Y");;
                        ?>
                    <div class="file-details" onclick="abrirModal('modal_boleto')">
                        <div class="file-icon"><i class='bx bxs-file-pdf'></i></div>
                        <div class="file-information">
                            <span><b><?php echo($houseData['nome_boleto']); ?></b></span>
                            <br>
                            <span>Valor: <b><?php echo($amount); ?></b></span>
                            <br>
                            <span>Data de vencimento: <b><?php echo($expiresDate); ?></b></span>
                            <br>
                            <span>Data de baixa: <b><?php echo($datePaid); ?></b> (0 dias depois)</span>
                        </div>
                    </div>
                    <?php
                            if(empty($houseData['arquivo_comprovante'])) {
                        ?>
                    <div class="comprovante">
                        <span class="no-file">Comprovante de pagamento não encontrado.</span>
                        <div class="button" style="width: 45%; margin: 1em auto 0 auto;"
                            onclick="apurarComprovante(<?php echo($houseData['id_boleto']); ?>)">APURAR COMPROVANTE
                        </div>
                    </div>
                    <?php
                            } else {
                        ?>
                    <div class="comprovante" onclick="abrirModal('modal_comprovante')">
                        <b><i class='bx bxs-file-pdf'></i> Comprovante de Pagamento</b>
                    </div>
                    <?php
                                }
                            }
                        ?>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Medições</h3>
                    </div>
                    <div class="button" style="width: 45%; margin: 0 auto 1em auto;"
                        onclick="window.location.href='/medicoes/medir/?year=<?php echo($year); ?>&month=<?php echo($month); ?>&addressId=<?php echo($houseData['id']); ?>'">
                        INICIAR MEDIÇÃO</div>

                    <table>
                        <tr>
                            <th>Competência</th>
                            <th>Tipo</th>
                            <th>Taxa</th>
                            <th>Valor</th>
                        </tr>
                        <?php 
                            foreach($rowsMedicoes as $row) {
                                $refundableText = boolval($row['reembolsavel']) ? "Reembolsável" : "Não Reembolsável";
                                $refundableColor = boolval($row['reembolsavel']) ? "green" : "red";
                        ?>
                        <tr>
                            <td><?php echo($row['mes'] . '/' . $row['ano']); ?></td>
                            <td><span
                                    class="badge badge-<?php echo($refundableColor); ?>"><?php echo($refundableText); ?></span>
                            </td>
                            <td><?php echo($row['nome_taxa']); ?></td>
                            <td>R$ <?php echo(number_format($row['valor_taxa'], 2, ",", ".")); ?></td>
                        </tr>
                        <?php 
                            }
                        ?>
                    </table>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Contatos para Reembolso</h3>
                    </div>

                    <table>
                        <tr>
                            <th>Endereço de e-mail</th>
                            <th>Observações</th>
                            <th>Data de Inclusão</th>
                            <th></th>
                        </tr>
                        <tr>
                            <td>a@gmail.com</td>
                            <td>Responsavel tecnico</td>
                            <td>17/6/2023</td>
                            <td>ações</td>
                        </tr>
                    </table>
                </div>

                <div id="modal_boleto" class="modal">
                    <div class="modal-content">
                        <embed src="/uploads/<?php echo($houseData['arquivo_boleto']); ?>">
                        <div class="button" onclick="closeModal('modal_boleto')" style="width: 100%;">FECHAR</div>
                    </div>
                </div>

                <div id="modal_comprovante" class="modal">
                    <div class="modal-content">
                        <embed src="/uploads/<?php echo($houseData['arquivo_comprovante']); ?>">
                        <div class="button" onclick="closeModal('modal_boleto')" style="width: 100%;">FECHAR</div>
                    </div>

                </div>
            </div>
        </div>
        </div>
    </main>

    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="/mobile-navbar.js"></script>
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

    function abrirModal(modalId) {
        $("#" + modalId).css("display", "flex");

        setTimeout(function() {
            $("#" + modalId).addClass("show");
        }, 10);
    }

    function closeModal(modalId) {
        console.log('close');
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

    function apurarComprovante(idBoleto) {
        $.ajax({
            type: "POST",
            url: "/rotinas/apurarComprovante.php",
            data: {
                id_boleto: idBoleto,
            },
            success: function(result) {
                console.log(result);
                return;
            },
            error: function(result) {
                tata.error('Um erro ocorreu',
                    'Ocorreu um erro ao tentar apurar o comprovante de pagamento.', {
                        duration: 6000
                    });
            }
        });
    }
    </script>
</body>

</html>