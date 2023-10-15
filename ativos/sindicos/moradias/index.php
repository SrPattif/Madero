<?php
    include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');

    $month = date('n');
    $year = date('Y');

    $query = "SELECT a.*, at.nome, at.chapa FROM alojamentos a LEFT JOIN ativos at ON a.id_sindico=at.id;";
    $result = mysqli_query($mysqli, $query);
    $rows = array();
    while($row = mysqli_fetch_array($result)){
        array_push($rows, $row);
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moradias Grupo Madero | Síndicos das Moradias</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="/moradias/reembolsos/defaultStyle.css" />
    <link rel="stylesheet" href="./index.css" />
    <link rel="stylesheet" href="/assets/styles/tooltips.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php
    require($_SERVER['DOCUMENT_ROOT'] . '/ativos/sindicos/header.php');
    ?>

    <main>
        <div class="page-content">
            <div class="card">
                <div class="card-header">
                    <h3>Todas os Síndicos Cadastrados</h3>
                    <div class="option-list">
                        <div id="export-table-btn" class="option">
                            <i class='bx bxs-file-export'></i> EXPORTAR TABELA
                        </div>
                    </div>
                </div>

                <input type="text" id="filterInput" placeholder="Digite o texto para filtrar">

                <table class="ranking-table" id="dataTable">
                    <tr>
                        <th>Operação</th>
                        <th>Dig Financeiro</th>
                        <th>Contrato</th>
                        <th>Endereço</th>
                        <th>Síndico</th>
                    </tr>
                    <?php
                        foreach($rows as $row) {
                    ?>

                    <tr>
                        <td style="text-align: center;"><?php echo($row['operacao']); ?></td>
                        <td style="text-align: center;"><?php echo($row['digito_financeiro']); ?></td>
                        <td style="text-align: center;"><?php echo($row['contrato_totvs']); ?></td>
                        <td class="bold"><?php echo($row['endereco']); ?></td>
                        <td><?php echo($row['nome']); ?></td>
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
    const filterInput = document.getElementById('filterInput');
    const dataTable = document.getElementById('dataTable');

    filterInput.addEventListener('input', function() {
        const filterText = filterInput.value.toLowerCase();
        const rows = dataTable.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                const cellText = cell.textContent.toLowerCase();

                if (cellText.includes(filterText)) {
                    found = true;
                    break;
                }
            }

            if (found) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });

    function aprovarSolicitacao(idSolicitacao) {
        $.ajax({
            type: "POST",
            url: "/ativos/sindicos/backend/revisarSolicitacao.php",
            data: {
                id_solicitacao: idSolicitacao,
                aprovado: true,
            },
            success: function(result) {
                console.log(result)
                tata.success('Solicitação aprovada',
                    'A solicitação foi aprovada e o síndico foi alterado.', {
                        duration: 2500
                    });

                setTimeout(() => {
                    window.location.reload();
                }, 2500);
                return;
            },
            error: function(result) {
                tata.error('Um erro ocorreu',
                    'Ocorreu um erro ao tentar aprovar a solicitação. (' + result
                    .responseJSON.mensagem + ')', {
                        duration: 6000
                    });
            }
        });
    }

    function recusarSolicitacao(idSolicitacao) {
        $.ajax({
            type: "POST",
            url: "/ativos/sindicos/backend/revisarSolicitacao.php",
            data: {
                id_solicitacao: idSolicitacao,
                aprovado: false,
            },
            success: function(result) {
                console.log(result)
                tata.success('Solicitação aprovada',
                    'A solicitação foi recusada.', {
                        duration: 2500
                    });

                setTimeout(() => {
                    window.location.reload();
                }, 2500);
                return;
            },
            error: function(result) {
                console.log(result);
                tata.error('Um erro ocorreu',
                    'Ocorreu um erro ao tentar recusar a solicitação. (' + result
                    .responseJSON.mensagem + ')', {
                        duration: 6000
                    });
            }
        });
    }

    $(document).ready(() => {
        $("#export-table-btn").click(function() {
            window.location.href = "/ativos/sindicos/exportador/tabelaMoradias.php";
        });
    })
    </script>
</body>

</html>