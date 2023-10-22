<?php
    include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');

    $query = "SELECT * FROM formularios WHERE nome='sindicos';";
    $result = mysqli_query($mysqli, $query);
    if(mysqli_num_rows($result) != 1) {
        header('location: ../');
        exit();
    }
    $formData = mysqli_fetch_assoc($result);

    $statusColor = 'red';
    if($formData['status'] == "ativo") {
        $statusColor = 'green';
    }
    
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Formulário | Controladoria Grupo Madero</title>

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
                    <h3>Editar Formulário</h3>
                </div>
                <div class="double-inputs">
                    <div class="input-group" style="width: fit-content;">
                        <label for="input_id">Identificador</label>
                        <input type="text" id="input_id" value="<?php echo((int) $formData['id']); ?>"
                            style="text-align: center; cursor: not-allowed;" readonly>
                    </div>
                    <div class="input-group" style="width: 100%;">
                        <label for="input_titulo">Título</label>
                        <input type="text" id="input_titulo" value="<?php echo($formData['titulo']); ?>">
                    </div>
                    <div class="input-group" style="width: 25%;">
                        <label for="input_status">Status</label>
                        <div class="status-i">
                            <div class='circle circle-<?php echo($statusColor); ?>' id="circle_statusIndicator"></div>

                            <input type="text" id="input_status" value="<?php echo(ucwords($formData['status'])); ?>" readonly onclick="changeStatus()">
                        </div>
                    </div>
                </div>

                <button class="button" id="btn_salvarFormulario" style="margin-bottom: 2em;">SALVAR</button>
            </div>
        </div>
        </div>
    </main>

    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="/mobile-navbar.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#btn_salvarFormulario').click(() => {
            var idFormulario = $('#input_id').val();
            var data_titulo = $('#input_titulo').val();
            var data_status = $('#input_status').val().toLowerCase();

            $.ajax({
                url: '/ativos/sindicos/backend/editarFormulario.php',
                type: 'POST',
                data: {
                    id_formulario: idFormulario,
                    titulo: data_titulo,
                    status: data_status,
                },
                success: function(response) {
                    console.log(response);
                    tata.success('Formulário editado',
                        'Os dados do formulário foram atualizados com sucesso.', {
                            duration: 2500
                        });

                    setTimeout(() => {
                        window.location.reload();
                    }, 2500);
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);

                    tata.error('Um erro ocorreu',
                        'Ocorreu um erro ao editar os dados do formulário. (' +
                        xhr.responseText + ')', {
                            duration: 6000
                        });
                }
            });
        });
    });

    function changeStatus() {
        var data_status = $('#input_status').val();

        if(data_status == "Ativo") {
            $('#input_status').val("Desativado");
            $('#circle_statusIndicator').removeClass("circle-green");
            $('#circle_statusIndicator').addClass("circle-red");

        } else if(data_status == "Desativado") {
            $('#input_status').val("Ativo");
            $('#circle_statusIndicator').addClass("circle-green");
            $('#circle_statusIndicator').removeClass("circle-red");
        }
    }
    </script>
</body>

</html>