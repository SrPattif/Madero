<?php
    include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');
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

    $query = "SELECT a.*, b.id AS id_boleto, b.codigo_interno, b.nome_interno AS arquivo_boleto, b.nome_original AS nome_boleto, b.arquivo_comprovante, b.data_vencimento, r.data_baixa, r.valor_liquido AS valor_total FROM alojamentos a LEFT JOIN boletos b ON b.id_alojamento=a.id AND MONTH(data_vencimento) = {$month} AND YEAR(data_vencimento) = {$year} LEFT JOIN razao r ON r.documento=b.lancamento WHERE a.id = {$houseId};";
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

    $queryValores = "SELECT a.*, COALESCE(SUM(CASE WHEN tt.refundable = 1 THEN avr.valor_taxa ELSE 0 END), 0) AS valor_reembolsavel, COALESCE(SUM(CASE WHEN tt.refundable = 0 THEN avr.valor_taxa ELSE 0 END), 0) AS valor_nao_reembolsavel, b.id AS id_boleto, b.codigo_interno AS arquivo_boleto, b.arquivo_comprovante AS comprovante_boleto, r.documento AS titulo_razao, r.data_baixa, avr.`status` AS status_solicitacao FROM alojamentos a LEFT JOIN alojamentos_valores_reembolso avr ON a.id = avr.id_alojamento AND avr.mes = {$month} AND avr.ano = {$year} LEFT JOIN boletos b ON b.id_alojamento = a.id AND MONTH(b.data_vencimento)={$month} AND YEAR(b.data_vencimento)={$year} LEFT JOIN razao r ON b.lancamento=r.documento LEFT JOIN tipos_taxas tt ON avr.id_taxa = tt.id WHERE a.id={$houseId} GROUP BY a.id;";
    $resultValores = mysqli_query($mysqli, $queryValores);
    $rowsValores = array();
    while($row = mysqli_fetch_array($resultValores)){
        array_push($rowsValores, $row);
    }

    $queryContatos = "SELECT * FROM contatos_reembolso WHERE id_alojamento={$houseId};";
    $resultContatos = mysqli_query($mysqli, $queryContatos);
    $rowsContatos = array();
    while($row = mysqli_fetch_array($resultContatos)){
        array_push($rowsContatos, $row);
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
                    <h3>Detalhes da Moradia</h3>
                </div>
                <div class="double-inputs">
                    <div class="input-group" style="width: fit-content;">
                        <label for="input_id">Identificador</label>
                        <input type="text" id="input_id" value="<?php echo((int) $houseData['id']); ?>"
                            style="text-align: center;" readonly>
                    </div>
                    <div class="input-group" style="width: 100%;">
                        <label for="input_endereco">Endereço da Moradia</label>
                        <input type="text" id="input_endereco" value="<?php echo($houseData['endereco']); ?>">
                    </div>
                    <div class="input-group" style="width: fit-content;">
                        <label for="input_contrato">Contrato</label>
                        <input type="text" id="input_contrato" value="<?php echo($houseData['contrato_totvs']); ?>">
                    </div>
                </div>

                <div class="double-inputs">
                    <div class="input-group" style="width: 25%;">
                        <label for="input_digFinanceiro">Digito Financeiro</label>
                        <input type="text" id="input_digFinanceiro"
                            value="<?php echo($houseData['digito_financeiro']); ?>">
                    </div>
                    <div class="input-group" style="width: 25%;">
                        <label for="input_status">Status</label>
                        <input type="text" id="input_status" value="<?php echo($houseData['status']); ?>">
                    </div>
                    <div class="input-group" style="width: 25%;">
                        <label for="input_centroCusto">Centro de Custo</label>
                        <input type="text" id="input_centroCusto" value="<?php echo($houseData['centro_custo']); ?>">
                    </div>
                    <div class="input-group" style="width: 25%;">
                        <label for="input_operacao">Operação</label>
                        <input type="text" id="input_operacao" value="<?php echo($houseData['operacao']); ?>">
                    </div>
                </div>

                <button class="button" id="btn_salvarAlojamento" style="margin-bottom: 2em;">SALVAR</button>

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
                                $expiresDateObj = date_create($houseData['data_vencimento']);
                                $expiresDate = date_format($expiresDateObj, "d/m/Y");
                                $paidDateObj = date_create($houseData['data_baixa']);
                                $datePaid = date_format($paidDateObj, "d/m/Y");

                                $daysDifference = $paidDateObj->diff($expiresDateObj)->format("%a");
                                $differenceStatus = "";
                                if($expiresDateObj < $paidDateObj) {
                                    $differenceStatus = "(" . $daysDifference . " dias depois)";

                                } else if($paidDateObj < $expiresDateObj) {
                                    $differenceStatus = "(" . $daysDifference . " dias antes)";
                                }
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
                            <span>Data de baixa: <b><?php echo($datePaid); ?></b>
                                <?php echo($differenceStatus); ?></span>
                        </div>
                    </div>
                    <?php
                            if(empty($houseData['arquivo_comprovante'])) {
                        ?>
                    <div class="comprovante">
                        <span class="no-file">Comprovante de pagamento não encontrado.</span>
                        <div class="button" style="width: 45%; margin: 1em auto 0 auto;" onclick="apurarComprovante()">
                            APURAR COMPROVANTE
                        </div>
                        <div class="simple-button" id="btn_importarComprovante">IMPORTAR COMPROVANTE</div>
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

                <?php

                        $statusColor = "status-gray";
                        $statusCode = "Desconhecido";
                        $statusDatabase = $rowsValores[0]['status_solicitacao'];
                        $statusDescription = "Não há informações à respeito da situação da solicitação de reembolso.";

                        $valorReembolsavel = $rowsValores[0]['valor_reembolsavel'];
                        $valorNaoReembolsavel = $rowsValores[0]['valor_nao_reembolsavel'];

                        if(empty($statusDatabase)) {
                            if(empty($valorNaoReembolsavel) || $valorNaoReembolsavel == 0) {
                                $statusColor = "red";
                                $statusCode = "Medição pendente";
                                $statusDescription = "A moradia ainda não teve os valores reembolsáveis informados.<br>Realize a medição <a href='/moradias/reembolsos/medicoes/iniciar/'>aqui</a>.";

                            } elseif(!empty($valorNaoReembolsavel) && $valorNaoReembolsavel > 0 && empty($valorReembolsavel) || $valorReembolsavel == 0) {
                                $statusColor = "green";
                                $statusCode = "Sem valores reembolsáveis";
                                $statusDescription = "A moradia não possui valores que possam ser reembolsados.";

                            } elseif(empty($rowsValores[0]['id_boleto']) || empty($rowsValores[0]['arquivo_boleto'])) {
                                $statusColor = "orange";
                                $statusCode = "Envio do boleto pendente";
                                $statusDescription = "O boleto de condomínio ainda não foi enviado.<br>Realize o envio <a href='/arquivos/'>clicando aqui</a>.";

                            } elseif(empty($rowsValores[0]['titulo_razao']) || empty($rowsValores[0]['data_baixa'])) {
                                $statusColor = "orange";
                                $statusCode = "Não encontrado na razão";
                                $statusDescription = "O número do título do boleto de condomínio não foi encontrado na razão atual.<br>Realize a atualização da razão para apurar a situação do processamento desse boleto.";

                            } elseif(empty($rowsValores[0]['comprovante_boleto'])) {
                                $statusColor = "orange";
                                $statusCode = "Apuração do comprovante de pagamento pendente";
                                $statusDescription = "A razão e o boleto de condomínio já estão reunidos, mas o comprovante de pagamento ainda não foi apurado.<br>Realize a apuração automática ou envie o comprovante de pagamento manualmente.";

                            } else {
                                $statusColor = "yellow";
                                $statusCode = "Pronto para envio";
                                $statusDescription = "A razão, o boleto de condomínio e o comprovante de pagamento já estão reunidos e a solicitação de reembolso já pode ser enviada.";
                            }
                        } else {
                            switch ($statusDatabase) {
                                case 'enviado':
                                    $statusColor = "blue";
                                    $statusCode = "Solicitação de reembolso enviada";
                                    $statusDescription = "A solicitação de reembolso foi enviada.";
                                    break;

                                case 'reembolsado':
                                    $statusColor = "green";
                                    $statusCode = "Reembolsado";
                                    $statusDescription = "Os valores de taxa extra foram reembolsados.";
                                    break;

                                case 'pronto':
                                    $statusColor = "yellow";
                                    $statusCode = "Pronto para envio";
                                    $statusDescription = "A razão, o boleto de condomínio e o comprovante de pagamento já estão reunidos e a solicitação de reembolso já pode ser enviada.";
                                    break;

                                default:
                                    $statusColor = "gray";
                                    $statusCode = "Desconhecido";
                                    $statusDescription = "Não há informações à respeito da situação da solicitação de reembolso.";
                                    break;
                            }
                        }

                    ?>

                <div class="card card-<?php echo($statusColor); ?>">
                    <div class="card-header" style="margin: 0;">
                        <div class="status status-<?php echo($statusColor); ?>"
                            style="width: fit-content; margin-bottom: 1em;">
                            <?php echo($statusCode); ?></div>
                    </div>
                    <div class="status-desc"><?php echo($statusDescription); ?></div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Medições</h3>
                    </div>
                    <div class="button" style="width: 45%; margin: 0 auto 1em auto;"
                        onclick="window.location.href='/moradias/reembolsos/medicoes/medir/?year=<?php echo($year); ?>&month=<?php echo($month); ?>&addressId=<?php echo($houseData['id']); ?>'">
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
                                $refundableColor = boolval($row['reembolsavel']) ? "#048a01" : "#000";
                        ?>
                        <tr>
                            <td><?php echo($row['mes'] . '/' . $row['ano']); ?></td>
                            <td><span
                                    style="font-weight: bold; color: <?php echo($refundableColor); ?>;"><?php echo($refundableText); ?></span>
                            </td>
                            <td><?php echo($row['nome_taxa']); ?></td>
                            <td>R$ <?php echo(number_format($row['valor_taxa'], 2, ",", ".")); ?></td>
                        </tr>
                        <?php 
                            }
                        ?>
                    </table>
                    <div class="totals">
                        <h4>Total Reembolsável</h4>
                        <span>R$ <?php echo(number_format($valorReembolsavel, 2, ',', '.')); ?></span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header" style="display: flex;">
                        <h3>Contatos para Reembolso</h3>
                        <div class="option-list">
                            <div class="option" onclick="abrirModal('modal_adicionarContato')">
                                <i class='bx bxs-user-plus'></i> ADICIONAR CONTATO
                            </div>
                        </div>
                    </div>

                    <table>
                        <tr>
                            <th>Endereço de e-mail</th>
                            <th>Observações</th>
                            <th></th>
                        </tr>
                        <?php
                            foreach($rowsContatos as $row) {
                                $id = $row['id'];
                                $email = $row['email_reembolso'];
                                $observacoes = $row['observacoes'];
                                
                        ?>
                        <tr>
                            <td><?php echo($email); ?></td>
                            <td><?php echo($observacoes); ?></td>
                            <td>
                                <div class="options-list">
                                    <div class="option" onclick="confirmarExclusaoContato(<?php echo((int) $id); ?>)">
                                        <span data-tooltip="Remover Contato" data-flow="left"
                                            style="text-align: center; color: #AA0000;"><i
                                                class='bx bxs-user-x'></i></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php
                            }
                        ?>
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
                        <div class="button" onclick="closeModal('modal_comprovante')" style="width: 100%;">FECHAR</div>
                    </div>
                </div>

                <div id="modal_confirmarExclusao" class="modal">
                    <div class="modal-content vertical-center">
                        <div class="center">
                            <h2>Excluir contato de reembolso?</h2>
                            <span>Tem certeza que deseja excluir o contato de reembolso selecionado?
                                <br>
                                Não sera possível restaurar as informações.</span>
                        </div>

                        <div class="modal-footer">
                            <div class="double-buttons">
                                <div class="button" onclick="closeModal('modal_confirmarExclusao')" style="width: 50%;">
                                    FECHAR</div>
                                <div class="button" onclick="confirmarExclusaoContato(0, true);"
                                    style="width: 50%; background-color: rgba(255, 3, 3, 0.7); border-color:rgba(255, 3, 3, 0.7);">
                                    EXCLUIR CONTATO</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="modal_adicionarContato" class="modal">
                    <div class="modal-content vertical-center">
                        <div class="center">
                            <h2>Cadastrar Contato para Reembolso</h2>
                            <span>Insira abaixo as informações do contato.</span>

                            <div class="double-inputs" style="width: 70%; margin: 2em auto;">
                                <div class="input-group" style="width: 50%;">
                                    <label for="input_email">Endereço de E-mail</label>
                                    <input type="text" id="input_email" value="">
                                </div>
                                <div class="input-group" style="width: 50%;">
                                    <label for="input_obs">Observações</label>
                                    <input type="text" id="input_obs" value="">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <div class="double-buttons">
                                <div class="simple-button" onclick="closeModal('modal_adicionarContato')"
                                    style="width: 50%;">
                                    FECHAR</div>
                                <div class="button" id="btn_cadastrarContato" style="width: 50%;">
                                    CADASTRAR CONTATO</div>
                            </div>
                        </div>
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
    var idContatoExclusao = 0;

    function confirmarExclusaoContato(idContato, confirmado = false) {
        if (confirmado) {
            closeModal('modal_confirmarExclusao');
            removerContato(idContatoExclusao);

        } else {
            idContatoExclusao = idContato;
            abrirModal('modal_confirmarExclusao');
        }
    }

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

    function apurarComprovante() {
        $.ajax({
            type: "POST",
            url: "/moradias/reembolsos/backend/apurarComprovante.php",
            data: {
                id_boleto: <?php echo((int) $houseData['id_boleto']); ?>,
            },
            success: function(result) {
                tata.success('Comprovante apurado',
                    'O comprovante de pagamento foi apurado com sucesso.', {
                        duration: 6000
                    });

                setTimeout(() => {
                    window.location.reload();
                }, 2500);
                return;
            },
            error: function(result) {
                tata.error('Um erro ocorreu',
                    'Ocorreu um erro ao tentar apurar o comprovante de pagamento. (' + result
                    .responseJSON.descricao_erro + ')', {
                        duration: 6000
                    });
            }
        });
    }

    function removerContato(idContato) {
        $.ajax({
            type: "POST",
            url: "/moradias/reembolsos/backend/removerContatoReembolso.php",
            data: {
                id_contato: idContato,
            },
            success: function(result) {
                tata.success('Contato removido',
                    'O contato para reembolso foi removido com sucesso.', {
                        duration: 3000
                    });

                setTimeout(() => {
                    window.location.reload();
                }, 2500);
                return;
            },
            error: function(result) {
                tata.error('Um erro ocorreu',
                    'Ocorreu um erro ao tentar remover o contato de reembolso. (' + result
                    .responseJSON.mensagem + ')', {
                        duration: 6000
                    });
            }
        });
    }

    $(document).ready(function() {
        // Ao clicar na div, o seletor de arquivos será aberto
        $('#btn_importarComprovante').click(function() {
            $('<input type="file" accept="application/pdf">').on('change', function(e) {
                var file = e.target.files[0];
                var formData = new FormData();
                formData.append('pdfFile', file);
                formData.append('idBoleto', '<?php echo($houseData['id_boleto']); ?>');

                $.ajax({
                    url: '/moradias/reembolsos/backend/importarComprovante.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        console.log(
                            response
                        ); // Use essa função para lidar com a resposta do servidor
                        tata.success('Comprovante enviado',
                            'O comprovante de pagamento foi enviado com sucesso.', {
                                duration: 6000
                            });

                        setTimeout(() => {
                            window.location.reload();
                        }, 2500);
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                        tata.error('Um erro ocorreu',
                            'Ocorreu um erro ao enviar o comprovante de pagamento. (' +
                            xhr.responseText + ')', {
                                duration: 6000
                            });
                    }
                });
            }).click();
        });

        $('#btn_salvarAlojamento').click(() => {
            var idAlojamento = $('#input_id').val();
            var data_endereco = $('#input_endereco').val();
            var data_contrato = $('#input_contrato').val();
            var data_digitoFinanceiro = $('#input_digFinanceiro').val();
            var data_status = $('#input_status').val();
            var data_centroCusto = $('#input_centroCusto').val();
            var data_operacao = $('#input_operacao').val();

            $.ajax({
                url: '/moradias/reembolsos/backend/editarMoradia.php',
                type: 'POST',
                data: {
                    id_alojamento: idAlojamento,
                    endereco: data_endereco,
                    contrato: data_contrato,
                    digito_financeiro: data_digitoFinanceiro,
                    status: data_status,
                    centro_custo: data_centroCusto,
                    operacao: data_operacao
                },
                success: function(response) {
                    console.log(response);
                    tata.success('Moradia editada',
                        'Os dados da moradia foram atualizados com sucesso.', {
                            duration: 6000
                        });

                    setTimeout(() => {
                        window.location.reload();
                    }, 2500);
                },
                error: function(xhr, status, error) {
                    tata.error('Um erro ocorreu',
                        'Ocorreu um erro ao editar os dados da moradia. (' +
                        xhr.responseText + ')', {
                            duration: 6000
                        });
                }
            });
        });

        $('#btn_cadastrarContato').click(() => {
            var idAlojamento = $('#input_id').val();
            var data_email = $('#input_email').val();
            var data_obs = $('#input_obs').val();

            if (data_email == '') {
                tata.error('Preencha os campos obrigatórios',
                    'É necessário preencher todos os campos obrigatórios para cadastrar um contato de reembolso.', {
                        duration: 6000
                    });
                return;
            }

            closeModal('modal_adicionarContato');

            $('#input_obs').val("");
            $('#input_email').val("");

            $.ajax({
                url: '/moradias/reembolsos/backend/cadastrarContatoReembolso.php',
                type: 'POST',
                data: {
                    id_alojamento: idAlojamento,
                    email: data_email,
                    observacoes: data_obs
                },
                success: function(response) {
                    console.log(response);
                    tata.success('Contato de reembolsoo cadastrato',
                        'O contato para reembolsos foi cadastrado com sucesso.', {
                            duration: 3000
                        });

                    setTimeout(() => {
                        window.location.reload();
                    }, 2500);
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                    tata.error('Um erro ocorreu',
                        'Ocorreu um erro ao adicionar o contato de reembolso. (' +
                        xhr.responseText + ')', {
                            duration: 6000
                        });
                }
            });
        });


    });
    </script>
</body>

</html>