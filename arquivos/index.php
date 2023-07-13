<?php
    include('../libs/databaseConnection.php');
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

    $queryComprovantes = "SELECT * FROM comprovantes WHERE mes={$month} AND ano={$year} ORDER BY referencia ASC;";
    $rowsComprovantes = array();
    $resultComprovantes = mysqli_query($mysqli, $queryComprovantes);
    while($row = mysqli_fetch_array($resultComprovantes)){
        array_push($rowsComprovantes, $row);
    }

    $queryBoletos = "SELECT b.*, r.data_baixa, r.valor_liquido AS valor_total, a.endereco AS endereco_contratual, a.id AS id_alojamento FROM boletos b LEFT JOIN razao r ON b.lancamento=r.documento INNER JOIN alojamentos a ON b.id_alojamento=a.id WHERE MONTH(b.data_vencimento) = {$month} AND YEAR(b.data_vencimento) = {$year} ORDER BY b.data_vencimento ASC;";
    $rowsBoletos = array();
    $resultBoletos = mysqli_query($mysqli, $queryBoletos);
    while($row = mysqli_fetch_array($resultBoletos)){
        array_push($rowsBoletos, $row);
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
    <title>Moradias Grupo Madero | Arquivos de Condomínio</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="/defaultStyle.css" />
    <link rel="stylesheet" href="/assets/styles/tooltips.css" />
    <link rel="stylesheet" href="./index.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <div class="progress-container fixed-top">
        <progress class="progress-bar" id="progress-bar" max=100 value=0></progress>
    </div>
    <?php
    require($_SERVER['DOCUMENT_ROOT'] . '/header.php');
    ?>

    <main>
        <div class="overlay" id="overlay">
            <div class="overlay__inner">
                <div class="overlay__content">
                    <span class="spinner"></span>
                    <br>
                    Aguarde enquanto carregamos os comprovantes
            </div>
            </div>
        </div>

        <div class="page-content">

            <div class="card">
                <div class="card-header">
                    <h3>Enviar Arquivos</h3>
                </div>

                <div class="drop-files" id="drop-files-area">
                    <form class="center">
                        <span><i class='bx bx-cloud-upload'></i></span>
                        <br>
                        <h4>Solte aqui os arquivos que deseja enviar</h4>
                        <p id="drop-description" class="font-size: .2em;">Envie apenas comprovantes ou boletos.
                            Tentaremos identificar o tipo
                            de cada arquivo.</p>
                    </form>
                </div>

                <div class="simple-button" id="btn_importarComprovante"><a href="./razao/">ATUALIZAR RAZÃO</a></div>
            </div>

            <div class="card" id="card_upload">
                <div class="card-header">
                    <h3>Envio atual</h3>
                </div>

                <div class="file-list" id="file-list">
                </div>

            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Lista de Comprovantes</h3>
                </div>

                <table>
                    <tr>
                        <th>#</th>
                        <th>Nome Interno</th>
                        <th>Nome Original</th>
                        <th>Referência</th>
                        <th>Data de Inclusão</th>
                    </tr>
                    <?php
                        $index = 0;
                        foreach($rowsComprovantes as $row) {
                            $index++;
                            $reference = str_pad($row['dia'], 2, "0", STR_PAD_LEFT) . '/' . str_pad($row['mes'], 2, "0", STR_PAD_LEFT) . '/' . $row['ano'];
                            $dateCreated = date_format(date_create($row['data_inclusao']), "d/m/Y H:i");
                        ?>
                    <tr onclick="window.open('/uploads/<?php echo($row['nome_interno']); ?>', '_blank').focus();">
                        <td style="text-align: center;"><?php echo($index); ?></td>
                        <td style="text-align: center;"><?php echo($row['nome_interno']); ?></td>
                        <td style="text-align: center;"><?php echo($row['nome_original']); ?></td>
                        <td style="text-align: center;"><?php echo($reference); ?></td>
                        <td style="text-align: center;"><?php echo($dateCreated); ?></td>
                    </tr>
                    <?php
                             }
                        ?>
                </table>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Lista de Boletos</h3>
                </div>

                <div class="button" style="width: 100%;" onclick="apurarComprovantesLote()">APURAR COMPROVANTES EM LOTE
                </div>

                <table>
                    <tr>
                        <th></th>
                        <th>Nome Interno</th>
                        <th>Moradia</th>
                        <th>Título</th>
                        <th>Fornecedor</th>
                        <th>Loja</th>
                        <th>Vencimento</th>
                        <th>Baixa</th>
                        <th>Valor Total</th>
                        <th></th>
                    </tr>
                    <?php
                        $index = 0;
                        foreach($rowsBoletos as $row) {
                            $index++;
                            $statusColor = "status-red";
                            $dateExpires = date_format(date_create($row['data_vencimento']), "d/m/Y");

                            $datePaid = "-";
                            $paidAmount = "-";
                            if(!empty($row['data_baixa'])) {
                                $datePaid = date_format(date_create($row['data_baixa']), "d/m/Y");
                                $statusColor = "status-orange";

                                if(!empty($row['arquivo_comprovante'])) {
                                    $statusColor = "status-green";
                                }

                            } else {
                                $statusColor = "status-red";
                            }

                            if(!empty($row['valor_total'])) {
                                $paidAmount = "R$ " . number_format($row['valor_total'], 2, ",", ".");
                            }

                            $originalName = $row['nome_original'];
                            $maxLength = 35;
                            if (strlen($originalName) > $maxLength) {
                                $originalName = substr($originalName, 0, $maxLength) . "...";
                            }

                            
                        ?>
                    <tr>
                        <td style="text-align: center;">
                            <div class="status <?php echo($statusColor); ?>"></div>
                        </td>
                        <td style="text-align: center;"><?php echo($row['nome_interno']); ?></td>
                        <td><a href="/alojamentos/detalhes/?id_alojamento=<?php echo($row['id_alojamento']); ?>"
                                target="_blank"><span data-tooltip="Detalhes da Moradia" data-flow="left"
                                    style="text-align: center; z-index: 999;"><?php echo((int) $row['id_alojamento'] . ' : ' . $row['endereco_contratual']); ?></span></a>
                        </td>
                        <td style="text-align: center;"><?php echo($row['lancamento']); ?></td>
                        <td style="text-align: center;"><?php echo($row['fornecedor']); ?></td>
                        <td style="text-align: center;"><?php echo(str_pad($row['loja'], 4, "0", STR_PAD_LEFT)); ?></td>
                        <td style="text-align: center;"><?php echo($dateExpires); ?></td>
                        <td style="text-align: center;"><?php echo($datePaid); ?></td>
                        <td style="text-align: center;"><?php echo($paidAmount); ?></td>
                        <td>
                            <div class="file-options">
                                <div class="option">
                                    <a href="/uploads/<?php echo($row['nome_interno']); ?>" target="_blank"><span
                                            data-tooltip="Abrir Arquivo" data-flow="left"
                                            style="text-align: center; z-index: 999;"><i
                                                class='bx bxs-file'></i></span></a>
                                </div>
                                <div class="option">
                                    <a href="/alojamentos/detalhes/?id_alojamento=<?php echo($row['id_alojamento']); ?>"
                                        target="_blank"><span data-tooltip="Detalhes da Moradia" data-flow="left"
                                            style="text-align: center; z-index: 999;"><i
                                                class='bx bxs-home'></i></span></a>
                                </div>
                            </div>
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
    <script src="/mobile-navbar.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script>
    $("#export-table-btn").click(function() {
        window.location.href = "/exporter/tableHabitations.php";
    });
    </script>
    <script>
    $(document).ready(function() {
        $('#card_upload').hide();
    });

    let dropArea = document.getElementById('drop-files-area')
    let dropInstruictions = document.getElementById('drop-description');
    let filesDone = 0
    let filesToDo = 0
    let progressBar = document.getElementById('progress-bar')


    ;
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false)
    })

    function preventDefaults(e) {
        e.preventDefault()
        e.stopPropagation()
    }

    ;
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false)
    })

    ;
    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false)
    })

    function highlight(e) {
        dropArea.classList.add('highlight')
        dropInstruictions.style = 'display: none;';

    }

    function unhighlight(e) {
        dropArea.classList.remove('highlight')
        dropInstruictions.style = 'display: block;';
    }

    dropArea.addEventListener('drop', handleDrop, false)

    function handleDrop(e) {
        let dt = e.dataTransfer
        let files = dt.files

        handleFiles(files)
    }

    function initializeProgress(numfiles) {
        progressBar.value = 0;
        filesDone = 0;
        filesToDo = numfiles;
    }

    function progressDone() {
        filesDone++;
        progressBar.value = filesDone / filesToDo * 100;

        if (filesDone == filesToDo) {
            progressBar.value = 0;

            tata.info('Arquivos processados', 'Os arquivos enviados foram processados.', {
                duration: 3000
            });
        }
    }

    function handleFiles(files) {
        initializeProgress(files.length);

        ([...files]).forEach(uploadFile);
    }

    function uploadFile(file) {
        if (file.type != "application/pdf") {
            tata.error('Formato não permitido', 'Apenas documentos de extensão PDF podem ser enviados.', {
                duration: 6000
            });

        } else {
            $('#card_upload').show();

            let fileName = file.name;
            let fileNameExact = fileName.replace('.pdf', '');

            let fileData = {
                'originalName': file.fileName,
                'nameWithoutExtension': file.fileNameExact,
                'type': 'desconhecido'
            };

            if (fileNameExact.includes('.')) { // 05.06.2023
                var splitArray = fileNameExact.split('.');

                if (splitArray.length == 3) {
                    let forIndex = 0;

                    splitArray.forEach(separ => {
                        forIndex++;

                        if (isNumeric(separ)) {
                            if ((forIndex == 1 || forIndex == 2) && separ.length == 2) {
                                fileData.type = "comprovante";

                            } else if (forIndex == 3 && separ.length == 4) {
                                fileData.type = "comprovante";

                            } else {
                                fileData.type = "desconhecido";
                                return;
                            }
                        } else {
                            fileData.type = "desconhecido";
                            return;
                        }
                    });

                } else {
                    fileData.type = "desconhecido";
                }

            }

            if (fileNameExact.includes(' - ') && fileData.type ==
                "desconhecido") { // 400313 - 0001 - 558721 - 05-06 - Cond - R. Voluntarios da Patria, 475 ap 401
                var splitArray = fileNameExact.split(' - ');

                console.log(splitArray.length)

                if (splitArray.length >= 6) {
                    let forIndex = 0;

                    splitArray.forEach(separ => {
                        forIndex++;

                        if (forIndex == 1 && separ.length == 6 && isNumeric(separ)) {
                            fileData.type = "boleto";

                        } else if (forIndex == 2 && separ.length == 4 && isNumeric(separ)) {
                            fileData.type = "boleto";

                        } else if (forIndex == 3 && separ.length == 6 && isNumeric(separ)) {
                            fileData.type = "boleto";

                        } else if (forIndex == 4 && separ.length == 5 && separ.includes("-")) {
                            fileData.type = "boleto";

                        } else if (forIndex == 5 && separ == "Cond") {
                            fileData.type = "boleto";

                        } else if (forIndex != 6) {
                            fileData.type = "desconhecido";
                            return;
                        }
                    });

                } else {
                    fileData.type = "desconhecido";
                }

            }

            if (fileData.type == "desconhecido") {
                tata.error('Tipo não reconhecido', 'Não conseguimos reconhecer o arquivo ' + fileName, {
                    duration: 6000
                });
            }


            let formData = new FormData()

            formData.append('file', file)

            insertFileDiv(fileName, "identificando");

            $.ajax({
                url: 'uploadFile.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (!response.salvo) {
                        tata.error('Erro ao processar arquivo', 'Ocorreu um erro ao processar ' + fileName +
                            '. (' +
                            response.status + ')', {
                                duration: 6000
                            });

                        insertFileDiv(fileName, "erro", response);
                    } else {
                        insertFileDiv(fileName, response.tipo_arquivo, response);
                    }

                    progressDone();
                },
                error: function(response) {
                    progressDone();
                    if (!response || !response.responseJSON || !response.responseJSON.salvo) {
                        insertFileDiv(response.responseJSON.arquivo_nome_original, "erro", response
                            .responseJSON);
                    }
                }
            });

        }
    }

    var filesIds = {};

    function insertFileDiv(nomeOriginal, tipo, dados) {
        var id = generateRandomString(5);

        switch (tipo) {
            case "identificando":
                var arquivoIdentificandoDiv = `<div class="file-details" id="fileDetails_${id}">
                        <div class="file-icon"><i class="bx bxs-file-pdf"></i></div>
                        <div class="file-info">
                            <div class="badge badge-gray"><i class="bx bx-loader-alt bx-spin"></i> Identificando...</div>
                            <span><b>${nomeOriginal}</b></span>
                        </div>
                    </div>`;

                $('#file-list').append(arquivoIdentificandoDiv);
                filesIds[nomeOriginal] = id;
                break;

            case "comprovante":
                var arquivoComprovanteDiv = `<div class="file-details" id="fileDetails_${id}">
                        <div class="file-icon"><i class="bx bxs-file-pdf"></i></div>
                        <div class="file-info">
                            <div class="badge badge-blue"><i class="bx bx-receipt"></i> Grupo de Comprovantes</div>
                            <span><b>${nomeOriginal}</b></span>
                            <br>
                            <span>Codigo interno: <b>${dados.arquivo_codigo_interno}</b></span>
                            <br>
                            <span>Competência: <b>${dados.competencia}</b></span>
                        </div>
                    </div>`;

                var nomeOriginal = dados.arquivo_nome_original;
                if (filesIds[nomeOriginal]) {
                    $('#fileDetails_' + filesIds[nomeOriginal]).html(arquivoComprovanteDiv);
                    filesIds[nomeOriginal] = id;

                } else {
                    $('#file-list').append(arquivoComprovanteDiv);
                    filesIds[nomeOriginal] = id;
                }
                break;

            case "boleto":
                var arquivoBoletoDiv = `<div class="file-details" id="fileDetails_${id}">
                        <div class="file-icon"><i class="bx bxs-file-pdf"></i></div>
                        <div class="file-info">
                            <div class="badge badge-yellow"><i class="bx bx-barcode"></i> Boleto de Condomínio</div>
                            <span><b>${nomeOriginal}</b></span>
                            <br>
                            <span>Codigo interno: <b>${dados.arquivo_codigo_interno}</b></span>
                            <br>
                            <span>Alojamento: <b>${dados.id_moradia} : ${dados.endereco_moradia}</b></span>
                            <br>
                            <span>Vencimento: <b>${formatarData(dados.vencimento)}</b></span>
                            <br>
                            <span>Número do lançamento: <b>${dados.titulo}</b></span>
                        </div>
                    </div>`;

                var nomeOriginal = dados.arquivo_nome_original;
                if (filesIds[nomeOriginal]) {
                    $('#fileDetails_' + filesIds[nomeOriginal]).html(arquivoBoletoDiv);
                    filesIds[nomeOriginal] = id;

                } else {
                    $('#file-list').append(arquivoBoletoDiv);
                    filesIds[nomeOriginal] = id;
                }
                break;


            case "erro":
                var arquivoErroDiv = `<div class="file-details" id="fileDetails_${id}">
                        <div class="file-icon"><i class="bx bxs-file-pdf"></i></div>
                        <div class="file-info">
                            <div class="badge badge-red"><i class="bx bx-x"></i> Erro ao Processar</div>
                            <span><b>${nomeOriginal}</b></span>
                            <br>
                            <span>Não foi possível identificar o tipo do arquivo. Verifique se o nome do arquivo está correto, ou importe manualmente.<br>Retorno do servidor: ${dados.descricao_erro}</span>
                        </div>
                    </div>`;

                var nomeOriginal = dados.arquivo_nome_original;
                if (filesIds[nomeOriginal]) {
                    $('#fileDetails_' + filesIds[nomeOriginal]).html(arquivoErroDiv);
                    filesIds[nomeOriginal] = id;

                } else {
                    $('#file-list').append(arquivoErroDiv);
                    filesIds[nomeOriginal] = id;
                }
                break;

            default:
                break;
        }
    }

    function isNumeric(value) {
        return /^\d+$/.test(value);
    }

    function generateRandomString(length) {
        let result = '';
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        for (let i = 0; i < length; i++) {
            const randomIndex = Math.floor(Math.random() * characters.length);
            result += characters.charAt(randomIndex);
        }

        return result;
    }

    function formatarData(data) {
        const partes = data.split('-');
        const ano = partes[0];
        const mes = partes[1];
        const dia = partes[2];

        return `${dia}/${mes}/${ano}`;
    }

    function apurarComprovantesLote() {
        $("#overlay").show();

        $.ajax({
            type: "POST",
            url: "/backend/apurarComprovanteLote.php",
            success: function(result) {
                $("#overlay").hide();

                tata.success('Comprovantes apurados',
                    'Os comprovantes disponíveis foram apurados.', {
                        duration: 6000
                    });

                setTimeout(() => {
                    window.location.reload();
                }, 2500);
                return;
            },
            error: function(result) {
                $("#overlay").hide();
                
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