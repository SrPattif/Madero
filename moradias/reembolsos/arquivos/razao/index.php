<?php
    include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');

    $month = date('n');
    $year = date('Y');
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
    <title>Atualizar Razão | Controladoria Grupo Madero</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="/moradias/reembolsos/defaultStyle.css" />
    <link rel="stylesheet" href="./index.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <div class="progress-container fixed-top">
        <progress class="progress-bar" id="progress-bar" max=100 value=0></progress>
    </div>
    <?php
    require($_SERVER['DOCUMENT_ROOT'] . '/moradias/reembolsos/header.php');
    ?>

    <main>
        <div class="page-content">
            <div class="success-card" id="card_successMessage" style="display: none;">
                <div class="card-header">
                    <h3>Banco de Dados Atualizado!</h3>
                </div>
                O banco de dados foi atualizado com a razão enviada. As informações atualizadas já estão disponíveis
                para utilização.
            </div>

            <div class="card" id="card_inputUpload">
                <div class="card-header">
                    <h3>Enviar Arquivos</h3>
                    <br>
                </div>

                <div class="drop-files" id="drop-files-area">
                    <div class="center">
                        <div class="info">
                            <span>Ainda não é possível detectar automaticamente as colunas do razão. <br>Envie o arquivo no
                                formato CSV e selecione as colunas e seus representantes na base de dados.</span>
                        </div>

                        <input type="file" id="csvFileInput">
                    </div>
                </div>
            </div>

            <div class="card" id="card_upload" style="display: none;">
                <div class="card-header">
                    <h3>Envio atual</h3>
                </div>

                <table id="columnMappingTable">
                    <th>
                        <tr>
                            <th>Coluna CSV</th>
                            <th>Coluna Banco de Dados</th>
                        </tr>
                    </th>
                </table>

                <div class="button" id="verifyColumnsButton">Processar</div>
                <div class="button btn-neutral" onclick="performAutoSelect()" id="btn_autoFill">Preencher
                    Automaticamente</div>

            </div>
        </div>
    </main>

    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="/mobile-navbar.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script>
    // Função para preencher a tabela com as colunas do CSV e as opções do select
    function fillColumnMappingTable(columns) {
        const columnMappingTable = document.getElementById('columnMappingTable');

        columns.forEach(function(column) {
            const row = document.createElement('tr');

            const csvColumnCell = document.createElement('td');
            csvColumnCell.textContent = column;
            row.appendChild(csvColumnCell);

            const dbColumnCell = document.createElement('td');
            const select = document.createElement('select');
            select.classList.add('dbColumnSelect');
            dbColumnCell.appendChild(select);
            row.appendChild(dbColumnCell);

            columnMappingTable.appendChild(row);
        });

        // Realiza o selecionador automático
        performAutoSelect();
    }

    // Função para realizar o selecionador automático
    function performAutoSelect() {
        const dbColumnSelects = document.querySelectorAll('.dbColumnSelect');

        dbColumnSelects.forEach(function(selectElement) {
            const csvColumnName = selectElement.parentNode.previousSibling.textContent.toLocaleLowerCase()
                .replaceAll('"', '').replace(' ', '_');
            const options = selectElement.options;

            for (let i = 0; i < options.length; i++) {
                const optionText = options[i].textContent.toLowerCase().replace(' ', '_');

                if (optionText === csvColumnName) {
                    options[i].selected = true;
                    break;
                }
            }
        });
    }

    // Função para preencher as opções do select com as colunas da tabela 'razao'
    function fillDbColumnOptions(selectElement, columns) {
        const emptyOption = document.createElement('option');
        emptyOption.value = '';
        emptyOption.textContent = '';
        selectElement.appendChild(emptyOption);

        columns.forEach(function(column) {
            const option = document.createElement('option');
            option.value = column;
            option.textContent = column;
            selectElement.appendChild(option);
        });
    }


    // Event listener para o elemento de entrada de arquivo
    const fileInput = document.querySelector('#csvFileInput');
    fileInput.addEventListener('change', handleFileUpload);

    var databaseColumns = [];

    // Função para lidar com o upload do arquivo
    function handleFileUpload(event) {
        const file = event.target.files[0];
        const reader = new FileReader();

        reader.onload = function(e) {
            const contents = e.target.result;
            const csvData = parseCSV(contents);

            // Preenche as opções dos selects com as colunas da tabela 'razao'
            const dbColumnSelects = document.querySelectorAll('.dbColumnSelect');
            $.ajax({
                url: 'get_columns.php', // Caminho para o arquivo PHP que retorna as colunas
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    dbColumnSelects.forEach(function(selectElement) {
                        fillDbColumnOptions(selectElement, response);
                        databaseColumns = response;
                    });
                },
                error: function() {
                    console.log('Erro ao obter as colunas da tabela.');
                }
            });

            // Aqui você pode fazer o processamento adicional com os dados do CSV
            $('#card_upload').show();
            $('#card_inputUpload').hide();
        };

        reader.readAsText(file);
    }

    // Função para analisar o CSV em matriz de dados
    function parseCSV(csv) {
        const delimiter = detectDelimiter(csv);
        const rows = csv.split('\n');
        const data = [];

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i].trim();

            if (row !== '') {
                const columns = row.split(delimiter);
                data.push(columns);
            }
        }

        // Preenche a tabela com as colunas do CSV
        const csvColumns = data[0];
        fillColumnMappingTable(csvColumns);

        return data;
    }

    // Event listener para o botão de verificação das colunas
    const verifyColumnsButton = document.querySelector('#verifyColumnsButton');
    verifyColumnsButton.addEventListener('click', verifyColumns);

    // Função para verificar as colunas selecionadas
    function verifyColumns() {
        $('#verifyColumnsButton').html("<i class='bx bx-loader-alt bx-spin'></i>");
        $('#btn_autoFill').hide();

        var selectedOptionsList = [];
        var duplicatedOptionsList = [];

        const columnMappingTable = document.getElementById('columnMappingTable');
        if (columnMappingTable.tBodies[0].rows.length === 0) {
            tata.error('Arquivo não enviado',
                'A tabela de mapeamento de colunas está vazia. Por favor, carregue um arquivo CSV primeiro.', {
                    duration: 3000
                });

            $('#verifyColumnsButton').html("Processar");
            $('#btn_autoFill').show();
            return;
        }

        const dbColumnSelects = document.querySelectorAll('.dbColumnSelect');

        dbColumnSelects.forEach(function(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex].text;

            if (selectedOption !== '') {
                if (selectedOptionsList.includes(selectedOption)) {
                    duplicatedOptionsList.push(selectedOption);

                } else {
                    selectedOptionsList.push(selectedOption);
                }
            }
        });

        if (duplicatedOptionsList.length > 0) {
            tata.error('Seleção duplicada',
                `As seguintes colunas foram selecionadas mais de uma vez: ${duplicatedOptionsList.join(', ')}`, {
                    duration: 3000
                });
            $('#verifyColumnsButton').html("Processar");
            $('#btn_autoFill').show();

        } else {
            const notSelectedDbColumns = databaseColumns.filter(option => !selectedOptionsList.includes(option));

            if (notSelectedDbColumns.length > 0) {
                tata.error('Seleção faltante',
                    `As seguintes colunas estão faltando seleção: ${notSelectedDbColumns.join(', ')}`, {
                        duration: 3000
                    });
                $('#verifyColumnsButton').html("Processar");
                $('#btn_autoFill').show();

            } else {
                sendJsonToBackend();
            }
        }
    }

    var delayed = false;

    function sendJsonToBackend() {
        if (delayed) {
            tata.error('Envio em processamento',
                'Já existe um envio em processamento.', {
                    duration: 3000
                });
            return;
        }
        delayed = true;
        const columnMappingTable = document.getElementById('columnMappingTable');
        if (columnMappingTable.tBodies[0].rows.length === 0) {
            tata.error('Arquivo não enviado',
                'A tabela de mapeamento de colunas está vazia. Por favor, carregue um arquivo CSV primeiro.', {
                    duration: 3000
                });
            $('#verifyColumnsButton').html("Processar");
            $('#btn_autoFill').show();
            delayed = false;
            return;
        }

        const dbColumnSelects = document.querySelectorAll('.dbColumnSelect');
        const jsonData = {};

        dbColumnSelects.forEach(function(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const dbColumnName = selectedOption.value;
            const csvColumnName = selectedOption.parentNode.parentNode.previousSibling.textContent;
            if (dbColumnName !== "") {
                jsonData[dbColumnName] = csvColumnName;
            }
        });

        const fileInput = document.querySelector('#csvFileInput');
        const file = fileInput.files[0];

        const formData = new FormData();
        formData.append('json', JSON.stringify(jsonData));
        formData.append('csv', file);

        $.ajax({
            url: 'uploadData.php', // Substitua pelo URL do seu backend
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log(response);
                window.location.href = '?success=true';

                $('#verifyColumnsButton').html("Processar");
                $('#btn_autoFill').show();
            },
            error: function(response) {
                console.log(response);
                tata.error('Um erro ocorreu',
                    'Ocorreu um erro ao enviar os dados para o backend. Por favor, tente novamente.', {
                        duration: 3000
                    });
                delayed = false;

                $('#verifyColumnsButton').html("Processar");
                $('#btn_autoFill').show();
            }
        });
    }


    function detectDelimiter(csvData) {
        const delimiters = [',', ';', '\t', ':']; // Adicione outros possíveis delimitadores aqui

        const delimiterCount = {};

        delimiters.forEach(delimiter => {
            const lines = csvData.split('\n');
            let count = 0;

            lines.forEach(line => {
                const fields = line.split(delimiter);

                if (fields.length > 1) {
                    count++;
                }
            });

            delimiterCount[delimiter] = count;
        });

        // Encontre o delimitador com maior contagem
        const maxCount = Math.max(...Object.values(delimiterCount));
        const bestDelimiters = Object.keys(delimiterCount).filter(delimiter => delimiterCount[delimiter] === maxCount);

        // Retorne o primeiro delimitador com a maior contagem
        return bestDelimiters[0];
    }

    $(document).ready(() => {
        $('#card_successMessage').hide();
        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.has('success')) {
            const successValue = urlParams.get('success');

            if (successValue == "true") {
                $('#card_successMessage').show();
            }

            console.log(successValue)
        }
    })
    </script>
</body>

</html>