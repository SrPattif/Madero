<?php
    include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');

    $month = date('n');
    $year = date('Y');

    $query = "SELECT ss.*, a.endereco, atv.nome FROM solicitacoes_sindicos ss LEFT JOIN alojamentos a ON ss.id_alojamento=a.id LEFT JOIN ativos atv ON ss.id_sindico=atv.id ORDER BY ss.id DESC;";
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
    <title>Moradias Grupo Madero | Todas as Solicitações</title>

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
                    <h3>Todas as Solicitações</h3>
                </div>

                <input type="text" id="filterInput" placeholder="Digite o texto para filtrar">

                <table class="ranking-table" id="dataTable">
                    <tr>
                        <th></th>
                        <th>Código Externo</th>
                        <th>Data</th>
                        <th>Operação</th>
                        <th>Endereço</th>
                        <th>Novo Síndico</th>
                        <th></th>
                    </tr>
                    <?php
                        foreach($rows as $row) {
                            $statusColor = "status-gray";
                            $statusCode = "?";
                            $statusDatabase = $row['situacao'];

                            if(!empty($statusDatabase)) {
                                switch ($statusDatabase) {
                                    case 'pendente':
                                        $statusColor = "status-orange";
                                        $statusCode = "PENDENTE";
                                        break;

                                    case 'aprovado':
                                        $statusColor = "status-green";
                                        $statusCode = "APROVADO";
                                        break;

                                    case 'rejeitado':
                                        $statusColor = "status-red";
                                        $statusCode = "RECUSADO";
                                        break;

                                    default:
                                        $statusColor = "status-gray";
                                        $statusCode = "?";
                                        break;
                                }
                            }
                    ?>

                    <tr>
                        <td>
                            <div class="status <?php echo($statusColor); ?>"><?php echo($statusCode); ?></div>
                        </td>
                        <td style="text-align: center;"><?php echo($row['codigo_externo']); ?></td>
                        <td style="text-align: center;"><?php echo(date_format(date_timezone_set(date_create($row['criado_em']), timezone_open('America/Sao_Paulo')), "d/m/Y H:i:s")); ?></td>
                        <td><?php echo($row['operacao']); ?></td>
                        <td><?php echo($row['endereco']); ?></td>
                        <td><?php echo($row['nome']); ?></td>
                        <td>
                            <?php
                                if($statusDatabase == "pendente") {
                            ?>
                            <div class="review-buttons">
                                <div class="approve-button" onclick="aprovarSolicitacao('<?php echo($row['id']); ?>')"><i class='bx bx-check'></i></div>
                                <div class="refuse-button" onclick="recusarSolicitacao('<?php echo($row['id']); ?>')"><i class='bx bx-x'></i></div>
                            </div>
                            <?php
                                }
                            ?>
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
        window.location.href = "/moradias/reembolsos/exporter/tableHabitations.php";
    });

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
    </script>
</body>

</html>