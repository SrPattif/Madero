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

    $idAlojamento = 0;
    if(isset($_GET['id_alojamento'])) {
        $idAlojamento = $_GET['id_alojamento'];

    } else {
        $query = "SELECT DISTINCT a.id, a.endereco FROM alojamentos a LEFT JOIN alojamentos_valores_reembolso ar ON a.id = ar.id_alojamento AND ar.ano = {$year} AND ar.mes = {$month} LEFT JOIN tipos_taxas tt ON ar.id_taxa=tt.id INNER JOIN boletos b ON b.id_alojamento=a.id AND MONTH(b.data_vencimento)={$month} AND YEAR(b.data_vencimento)={$year} WHERE ar.valor_taxa > 0 AND tt.refundable=1 AND ar.`status` IS NULL AND b.arquivo_comprovante IS NOT NULL;";
        $result = mysqli_query($mysqli, $query);
        $rows = array();
        while($row = mysqli_fetch_array($result)){
            array_push($rows, $row);
        }

        if (!empty($rows)) {
            $idAlojamento = $rows[0]['id'];

        } else {
            header('location: ../');
        }
    }
    
    if (!is_numeric($idAlojamento) || $idAlojamento < 1) {
        header('location: ../');
        exit();
    }

    $query = "SELECT a.*, b.id AS id_boleto, b.codigo_interno, b.nome_interno AS arquivo_boleto, b.nome_original AS nome_boleto, b.arquivo_comprovante, b.data_vencimento, r.data_baixa, r.valor_liquido AS valor_total FROM alojamentos a LEFT JOIN boletos b ON b.id_alojamento=a.id AND MONTH(data_vencimento) = {$month} AND YEAR(data_vencimento) = {$year} LEFT JOIN razao r ON r.documento=b.lancamento WHERE a.id = {$idAlojamento};";
    $result = mysqli_query($mysqli, $query);
    if(mysqli_num_rows($result) != 1) {
        header('location: ../');
        exit();
    }

    $queryMedicoes = "SELECT avr.*, tt.description AS nome_taxa, tt.refundable AS reembolsavel FROM alojamentos_valores_reembolso avr INNER JOIN tipos_taxas tt ON avr.id_taxa=tt.id WHERE mes={$month} AND ano={$year} AND id_alojamento={$idAlojamento} ORDER BY tt.id ASC, tt.refundable ASC;";
    $resultMedicoes = mysqli_query($mysqli, $queryMedicoes);
    $rowsMedicoes = array();
    while($row = mysqli_fetch_array($resultMedicoes)){
        array_push($rowsMedicoes, $row);
    }

    $queryValores = "SELECT a.*, COALESCE(SUM(CASE WHEN tt.refundable = 1 THEN avr.valor_taxa ELSE 0 END), 0) AS valor_reembolsavel, COALESCE(SUM(CASE WHEN tt.refundable = 0 THEN avr.valor_taxa ELSE 0 END), 0) AS valor_nao_reembolsavel, b.id AS id_boleto, b.codigo_interno AS arquivo_boleto, b.arquivo_comprovante AS comprovante_boleto, r.documento AS titulo_razao, r.data_baixa, avr.`status` AS status_solicitacao, avr.data_reembolso, avr.data_envio, avr.data_medicao FROM alojamentos a LEFT JOIN alojamentos_valores_reembolso avr ON a.id = avr.id_alojamento AND avr.mes = {$month} AND avr.ano = {$year} LEFT JOIN boletos b ON b.id_alojamento = a.id AND MONTH(b.data_vencimento)={$month} AND YEAR(b.data_vencimento)={$year} LEFT JOIN razao r ON b.lancamento=r.documento LEFT JOIN tipos_taxas tt ON avr.id_taxa = tt.id WHERE a.id={$idAlojamento} GROUP BY a.id;";
    $resultValores = mysqli_query($mysqli, $queryValores);
    $rowsValores = array();
    while($row = mysqli_fetch_array($resultValores)){
        array_push($rowsValores, $row);
    }

    $queryContatos = "SELECT * FROM contatos_reembolso WHERE id_alojamento={$idAlojamento};";
    $resultContatos = mysqli_query($mysqli, $queryContatos);
    $rowsContatos = array();
    while($row = mysqli_fetch_array($resultContatos)){
        array_push($rowsContatos, $row);
    }

    $houseData = mysqli_fetch_assoc($result);

    // ------------------------- GERADOR DE CORPO DE EMAIL
    $listaMeses = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
    $nomeMes = $listaMeses[$month - 1];

    $queryAlojamento = "SELECT a.endereco, a.id, tt.description AS nome_taxa, avr.valor_taxa, b.nome_interno AS arquivo_boleto, b.arquivo_comprovante FROM alojamentos_valores_reembolso avr INNER JOIN tipos_taxas tt ON avr.id_taxa=tt.id LEFT JOIN alojamentos a ON a.id=avr.id_alojamento LEFT JOIN boletos b ON b.id_alojamento=a.id AND MONTH(b.data_vencimento)={$month} AND YEAR(b.data_vencimento)={$year} WHERE a.id={$idAlojamento} AND tt.refundable=1 AND avr.mes={$month} AND avr.ano={$year};";
    $rowsAlojamento = array();
    $resultAlojamento = mysqli_query($mysqli, $queryAlojamento);
    if(mysqli_num_rows($resultAlojamento) < 1) {
        http_response_code(400);
        exit();
    }
    $rowsAlojamento = array();
    while($row = mysqli_fetch_array($resultAlojamento)){
        array_push($rowsAlojamento, $row);
    }
    $idAlojamento = $houseData['id'];

    $b64boleto = '';
    $b64comprovante = '';

    if(!empty($houseData['arquivo_boleto'])) {
        $b64boleto = chunk_split(base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $houseData['arquivo_boleto'])));
    }
    if(!empty($houseData['arquivo_comprovante'])) {
        $b64comprovante = chunk_split(base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $houseData['arquivo_comprovante'])));
    }

    $totalReembolsavel = 0.0;
    // ------------------------- GERADOR DE CORPO DE EMAIL


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moradias Grupo Madero | Enviar Solicitação</title>

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
                    $statusDescription = "A moradia ainda não teve os valores reembolsáveis informados.<br>Realize a medição <a href='/medicoes/iniciar/'>aqui</a>.";

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
                        $dataEnvio = date_create($rowsValores[0]['data_envio']);
                        $statusDescription = "A solicitação de reembolso foi enviada.<br>Enviado em: <b>" . date_format($dataEnvio, "d/m/Y H:i") . "</b>";
                        break;

                    case 'reembolsado':
                        $statusColor = "green";
                        $statusCode = "Reembolsado";
                        $dataReembolso = date_create($rowsValores[0]['data_reembolso']);
                        $statusDescription = "Os valores de taxa extra foram reembolsados.<br>Reembolsado em: <b>" . date_format($dataReembolso, "d/m/Y H:i") . "</b>";
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
                        <input type="text" id="input_endereco" value="<?php echo($houseData['endereco']); ?>" readonly>
                    </div>
                    <div class="input-group" style="width: fit-content;">
                        <label for="input_contrato">Contrato</label>
                        <input type="text" id="input_contrato" value="<?php echo($houseData['contrato_totvs']); ?>"
                            readonly>
                    </div>
                </div>

                <div class="double-inputs">
                    <div class="input-group" style="width: 25%;">
                        <label for="input_digFinanceiro">Digito Financeiro</label>
                        <input type="text" id="input_digFinanceiro"
                            value="<?php echo($houseData['digito_financeiro']); ?>" readonly>
                    </div>
                    <div class="input-group" style="width: 25%;">
                        <label for="input_status">Status</label>
                        <input type="text" id="input_status" value="<?php echo($houseData['status']); ?>" readonly>
                    </div>
                    <div class="input-group" style="width: 25%;">
                        <label for="input_centroCusto">Centro de Custo</label>
                        <input type="text" id="input_centroCusto" value="<?php echo($houseData['centro_custo']); ?>"
                            readonly>
                    </div>
                    <div class="input-group" style="width: 25%;">
                        <label for="input_operacao">Operação</label>
                        <input type="text" id="input_operacao" value="<?php echo($houseData['operacao']); ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="display: flex;">
                    <h3>E-mail Preparado</h3>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Destinatários</h4>
                    </div>
                    <div class="destinations-list">
                        <?php
                            if(sizeof($rowsContatos) < 1) {
                        ?>
                        Não há destinatários cadastrados.
                        <?php
                            } else {
                                foreach($rowsContatos as $row) {
                                    $id = $row['id'];
                                    $email = $row['email_reembolso'];
                                    $observacoes = $row['observacoes'];
                                    $dataInclusao = date_format(date_create($row['data_inclusao']), "d/m/Y");
                        ?>
                        <div class="badge">・ <?php echo($email); ?></div>
                        <?php
                                }
                            }
                        ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Conteúdo</h4>
                    </div>
                    A pré-visualização deste conteúdo está indispoível. Realize o Download do e-mail para visualizá-lo.
                    <div class="button" onclick="downloadEmail()">BAIXAR E-MAIL E ANEXOS</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Anexos</h4>
                    </div>
                    <div class="file-container">
                        <?php
                            if(empty($houseData['id_boleto'])) {
                        ?>
                        <div class="no-file">
                            <span>Boleto de condominio não encontrado.</span>
                        </div>
                        <?php
                            } else {
                        ?>
                        <div class="file-details" onclick="abrirModal('modal_boleto')">
                            <div class="file-icon"><i class='bx bxs-file-pdf'></i></div>
                            <div class="file-information">
                                <span><b>Boleto de Condomínio - <?php echo($houseData['endereco']); ?></b></span>
                            </div>
                        </div>
                        <?php
                            if(empty($houseData['arquivo_comprovante'])) {
                        ?>
                        <div class="comprovante">
                            <span class="no-file">Comprovante de pagamento não encontrado.</span>
                        </div>
                        <?php
                            } else {
                        ?>
                        <div class="file-details" onclick="abrirModal('modal_comprovante')">
                            <div class="file-icon"><i class='bx bxs-file-pdf'></i></div>
                            <div class="file-information">
                                <span><b>Comprovante de Pagamento - <?php echo($houseData['endereco']); ?></b></span>
                            </div>
                        </div>
                        <?php
                                }
                            }
                        ?>
                    </div>
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
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Medição</h3>
                </div>

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

            <div class="double-buttons">
                <div class="simple-button" onclick="window.location.href='../'">VOLTAR</div>
                <?php
                    if(empty($statusDatabase) || ($statusDatabase != "enviado" && $statusDatabase != "reembolsado")) {
                ?>
                <div class="button" onclick="emailSent(false)"><i class='bx bx-envelope'></i> MARCAR COMO ENVIADO</div>
                <div class="button" onclick="emailSent(true)"><i class='bx bx-mail-send'></i> MARCAR COMO ENVIADO E PROSSEGUIR</div>

                <?php
                    } else if($statusDatabase == "enviado") {
                ?>
                <div class="button" onclick="changeStatus('none').then((r) => window.location.reload());"><i class='bx bx-x'></i> REMOVER STATUS</div>
                <div class="button" onclick="changeStatus('reembolsado').then((r) => window.location.reload());"><i class='bx bx-check'></i> MARCAR COMO REEMBOLSADO</div>

                <?php
                    } else if($statusDatabase == "reembolsado") {
                ?>
                <div class="button" onclick="changeStatus('none').then((r) => window.location.reload());"><i class='bx bx-x'></i> REMOVER STATUS</div>
                <?php
                    }
                ?>
            </div>
        </div>
    </main>

    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="/mobile-navbar.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script>
    function downloadEmail() {
        var qtdeContatos = <?php echo(sizeof($rowsContatos)); ?>;
        var comBoleto = <?php echo(empty($houseData['arquivo_boleto']) ? 'false' : 'true'); ?>;
        var comComprovante = <?php echo(empty($houseData['arquivo_boleto']) ? 'false' : 'true'); ?>;

        if (qtdeContatos <= 0) {
            tata.error('Não há contatos cadastrados',
                'Não existem contatos cadastrados para enviar a solicitação.', {
                    duration: 4000
                });

        } else if (!comBoleto) {
            tata.error('Boleto de condomínio não encontrado',
                'O boleto de condomínio não foi encontrado para gerar a solicitação.', {
                    duration: 4000
                });

        } else if (!comComprovante) {
            tata.error('Comprovante de pagamento não encontrado',
                'O comprovante de pagamento não foi encontrado para gerar a solicitação.', {
                    duration: 4000
                });

        } else {
            //window.location.href = '../../backend/geradorSolicitacao.php?id_moradia=<?php #echo((int) $houseData['id']); ?>'

            $.ajax({
                url: '/moradias/reembolsos/backend/geradorSolicitacao.php?id_moradia=<?php echo((int) $houseData['id']); ?>', // Caminho para o arquivo PHP que retorna as colunas
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var content = response;
                    var fileName = 'Email - <?php echo($houseData['endereco']); ?>.eml';
                    var blob = new Blob([content], {
                        type: 'text/plain'
                    });

                    // Cria um link para download
                    var downloadLink = document.createElement('a');
                    downloadLink.href = URL.createObjectURL(blob);
                    downloadLink.download = fileName;

                    downloadLink.click();
                },
                error: function(response) {
                    var content = response.responseText;
                    var fileName = 'Email - <?php echo($houseData['endereco']); ?>.eml';
                    var blob = new Blob([content], {
                        type: 'text/plain'
                    });

                    // Cria um link para download
                    var downloadLink = document.createElement('a');
                    downloadLink.href = URL.createObjectURL(blob);
                    downloadLink.download = fileName;

                    downloadLink.click();
                }
            });
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

    function changeStatus(status) {
        var idAlojamento = <?php echo((int) $houseData['id']); ?>;
        var mes = <?php echo((int) $month); ?>;
        var ano = <?php echo((int) $year); ?>;

        const promise = new Promise((resolve, reject) => {
            $.ajax({
                url: '/moradias/reembolsos/backend/mudarStatusSolicitacao.php',
                type: 'POST',
                data: {
                    id_alojamento: idAlojamento,
                    mes: mes,
                    ano: ano,
                    status: status,
                },
                success: function(response) {
                    console.log(response);

                    resolve(true);
                },
                error: function(xhr, status, error) {
                    tata.error('Um erro ocorreu',
                        'Um erro ocorreu ao atualizar o status da solicitação de reembolso. (' +
                        xhr.responseText + ')', {
                            duration: 6000
                        });
                    resolve(false);
                }
            });
        });

        return promise;
    }

    function emailSent(openNew) {
        changeStatus('enviado').then((r) => {
            if (r) {
                if (openNew) {
                    window.location.href = "../enviar/";
                } else {
                    window.location.reload();
                }
            }
        })
    }
    </script>
</body>

</html>