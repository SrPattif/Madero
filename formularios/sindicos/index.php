<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizar Troca de Síndico | Controladoria Grupo Madero</title>

    <link rel="stylesheet" href="./index.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <div class="page-content">

        <img class="madero-logo" src="/assets/images/madero-white.png" alt="">

        <div class="header">
            <h2>Solicitar Troca de Síndico</h2>
            <p>Formulário para realizar a alteração de síndicos de moradias do Grupo Madero.
                <br>
                Em caso de dúvidas, entre em contato: <span class="bold"><i class='bx bxl-whatsapp'></i> (41) 9
                    8894-8303</span>.
            </p>
        </div>

        <div id="continue-btn" class="continue-btn" onclick="startForm()">
            INICIAR <i class='bx bx-right-arrow-alt'></i>
        </div>
    </div>

    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script>
    function startForm() {
        localStorage.removeItem("form_telephone");
        localStorage.removeItem("form_shop");
        localStorage.removeItem("form_accommodation");
        localStorage.removeItem("form_step");

        window.location.href = './solicitacao/'
    }
    </script>
</body>

</html>