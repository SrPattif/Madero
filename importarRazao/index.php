<!DOCTYPE html>
<html>

<head>
    <title>Importar CSV</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <input type="file" id="csvFileInput">
    <table id="columnMappingTable">
        <th>
            <tr>
                <th>Coluna CSV</th>
                <th>Coluna Banco de Dados</th>
            </tr>
        </th>
    </table>

    <button id="verifyColumnsButton">Verificar Colunas</button>

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
        var selectedOptionsList = [];
        var duplicatedOptionsList = [];

        const columnMappingTable = document.getElementById('columnMappingTable');
        if (columnMappingTable.tBodies[0].rows.length === 0) {
            alert('A tabela de mapeamento de colunas está vazia. Por favor, carregue um arquivo CSV primeiro.');
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
            alert('As seguintes colunas foram selecionadas mais de uma vez: ' + duplicatedOptionsList.join(', '));

        } else {
            const notSelectedDbColumns = databaseColumns.filter(option => !selectedOptionsList.includes(option));

            if (notSelectedDbColumns.length > 0) {
                alert('As seguintes colunas estão faltando seleção: ' + notSelectedDbColumns.join(', '));

            } else {
                sendJsonToBackend();
            }
        }
    }

    function sendJsonToBackend() {
        const columnMappingTable = document.getElementById('columnMappingTable');
        if (columnMappingTable.tBodies[0].rows.length === 0) {
            alert('A tabela de mapeamento de colunas está vazia. Por favor, carregue um arquivo CSV primeiro.');
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
                alert('JSON e arquivo CSV enviados com sucesso para o backend!');
            },
            error: function() {
                alert('Ocorreu um erro ao enviar os dados para o backend. Por favor, tente novamente.');
            }
        });

        console.log(jsonData)
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
    </script>
</body>

</html>