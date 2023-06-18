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

    $queryComprovantes = "SELECT * FROM comprovantes WHERE mes={$month} AND ano={$year};";
    $rowsComprovantes = array();
    $resultComprovantes = mysqli_query($mysqli, $queryComprovantes);
    while($row = mysqli_fetch_array($resultComprovantes)){
        array_push($rowsComprovantes, $row);
    }

    $queryBoletos = "SELECT b.*, r.data_baixa, r.valor_total, a.endereco AS endereco_contratual, a.id AS id_alojamento FROM boletos b INNER JOIN razao r ON b.lancamento=r.documento INNER JOIN alojamentos a ON b.id_alojamento=a.id WHERE MONTH(data_vencimento) = {$month} AND YEAR(data_vencimento) = {$year};";
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
                    <tr>
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

                <table>
                    <tr>
                        <th>#</th>
                        <th></th>
                        <th>Nome Interno</th>
                        <th>Nome Original</th>
                        <th>Moradia</th>
                        <th>Título</th>
                        <th>Fornecedor</th>
                        <th>Loja</th>
                        <th>Vencimento</th>
                        <th>Baixa</th>
                        <th>Valor Total</th>
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
                            $maxLength = 55;
                            if (strlen($originalName) > $maxLength) {
                                $originalName = substr($originalName, 0, $maxLength) . "...";
                            }

                            
                        ?>
                    <tr>
                        <td style="text-align: center;"><?php echo($index); ?></td>
                        <td style="text-align: center;">
                            <div class="status <?php echo($statusColor); ?>"></div>
                        </td>
                        <td style="text-align: center;"><?php echo($row['nome_interno']); ?></td>
                        <td><?php echo($originalName); ?></td>
                        <td><?php echo((int) $row['id_alojamento'] . ' : ' . $row['endereco_contratual']); ?></td>
                        <td style="text-align: center;"><?php echo($row['lancamento']); ?></td>
                        <td style="text-align: center;"><?php echo($row['fornecedor']); ?></td>
                        <td style="text-align: center;"><?php echo(str_pad($row['loja'], 4, "0", STR_PAD_LEFT)); ?></td>
                        <td style="text-align: center;"><?php echo($dateExpires); ?></td>
                        <td style="text-align: center;"><?php echo($datePaid); ?></td>
                        <td style="text-align: center;"><?php echo($paidAmount); ?></td>
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
                    }

                    progressDone();
                },
                error: function(response) {
                    console.log(response)
                    tata.error('Erro ao processar arquivo', 'Ocorreu um erro ao processar ' + fileName + '. (' + response.responseJSON.descricao_erro + ')', {
                            duration: 6000
                        });
                }
            });

        }
    }

    function isNumeric(value) {
        return /^\d+$/.test(value);
    }
    </script>
</body>

</html>