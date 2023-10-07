<?php
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

if(!isset($_GET['codigoSolicitacao'])) {
    header('location: /formularios/sindicos/');
    exit();
}
$codigoExterno = mysqli_real_escape_string($mysqli, $_GET['codigoSolicitacao']);

$query = "SELECT ss.*, a.nome, al.endereco FROM solicitacoes_sindicos ss LEFT JOIN ativos a ON ss.id_sindico=a.id LEFT JOIN alojamentos al ON ss.id_alojamento=al.id WHERE ss.codigo_externo='{$codigoExterno}';";
$result = mysqli_query($mysqli, $query);
if(mysqli_num_rows($result) != 1) {
    header('location: /formularios/sindicos/');
    exit();
}
$requestData = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moradias Grupo Madero | Solicitação Finalizada</title>

    <link rel="stylesheet" href="./index.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <div class="page-content">

        <img class="madero-logo" src="/assets/images/madero-white.png" alt="">

        <div class="header">
            <h2>Solicitação Finalizada</h2>
            <p>Recebemos sua solicitação e o síndico da moradia será alterado em breve.</p>
            <br>
            <div class="request-data">
                <h3>Dados da Solicitação</h3>
                <br>
                <table style="width: 100%;">
                    <tr>
                        <th>Código Externo</th>
                        <td><?php echo($requestData['codigo_externo']); ?></td>
                    </tr>
                    <tr>
                        <th>Data</th>
                        <td><?php echo($requestData['criado_em']); ?></td>
                    </tr>
                    <tr>
                        <th>Operação</th>
                        <td><?php echo($requestData['operacao']); ?></td>
                    </tr>
                    <tr>
                        <th>Alojamento</th>
                        <td><?php echo($requestData['endereco']); ?></td>
                    </tr>
                    <tr>
                        <th>Novo síndico</th>
                        <td><?php echo($requestData['nome']); ?></td>
                    </tr>
                </table>
            </div>
            <br>
            Em caso de dúvidas, entre em contato: <span class="bold"><i class='bx bxl-whatsapp'></i> (41) 9
                8894-8303</span>.
        </div>

        <div class="home-btn">
            <a href="/formularios/sindicos/">VOLTAR AO INÍCIO</a>
        </div>

    </div>

    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>

</body>

</html>