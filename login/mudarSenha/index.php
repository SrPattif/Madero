<?php
    if (!isset($_SESSION)) {
        session_start();
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mudança de Senha | Controladoria Grupo Madero</title>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <!-- Estilos -->
    <link rel="stylesheet" href="./index.css" />

</head>

<body>

    <div class="page-content">
        <div class="top">
            <img class="madero-logo" src="/assets/images/madero-white.png" alt="">
        </div>

        <div class="content">
            <h1>Alterar Senha</h1>
            <?php
                if(isset($_SESSION['MESSAGES_LOGIN_ERROR'])) {
                    $_SESSION['MESSAGES_LOGIN_ERROR'] = null;
            ?>
            <span class="text-error">Não foi possível alterar a senha. É possível que a solicitação já tenha
                expirado.</span>
            <?php
                } else if(isset($_SESSION['MESSAGES_LOGIN_ERROR_RECAPTCHA'])) {
                    $_SESSION['MESSAGES_LOGIN_ERROR_RECAPTCHA'] = null;
            ?>
            <span class="text-error">Não foi possível verificar sua identidade. Verifique se a caixa de "Não sou um
                robô" está marcada.</span>
            <?php
                }
            ?>
            <div class="form-row">
                <label for="input_password">Nova Senha</label>
                <input type="password" name="password" id="input_password" required>
            </div>

            <div class="form-row">
                <label for="input_passwordConfirm">Repetir a Nova Senha</label>
                <input type="password" name="passwordConfirm" id="input_passwordConfirm" required>
            </div>

            <div class="form-row">
                <div class="g-recaptcha" data-sitekey="6Lenv2YmAAAAADf5nvmNCv2HJsWlpvQa9QkH3FYE"></div>
                <input type="hidden" name="g-recaptcha-response" value="" id="g-recaptcha-response">
            </div>

            <div class="form-row">
                <button type="submit" onclick="mudarSenha()">ALTERAR SENHA</button>
            </div>
        </div>

        <div class="footer">
            &copy; 2023 - 2023 GRUPO MADERO - Todos os Direitos Reservados.
        </div>
    </div>

    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="/mobile-navbar.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script>
    function mudarSenha() {
        var url = new URL(window.location.href);
        var searchParams = new URLSearchParams(url.search);

        var token = searchParams.get('token');
        var password = $('#input_password').val();
        var passwordConfirm = $('#input_passwordConfirm').val();
        var gCaptchaToken = $('#g-recaptcha-response').val();

        if(password != passwordConfirm) {
            tata.error('Senhas não conferem',
                    'As senhas informadas não são iguais.', {
                        duration: 6000
                    });

            return;
        }

        $.ajax({
            type: "POST",
            url: "./mudarSenha.php",
            data: {
                "token": token,
                "password": password,
                "g-recaptcha-response": gCaptchaToken
            },
            success: function(result) {
                tata.success('Senha alterada',
                    'Sua senha foi alterada com sucesso.', {
                        duration: 2500
                    });

                setTimeout(() => {
                    window.location.href = '/login/';
                }, 2500);
                return;
            },
            error: function(result) {
                tata.error('Um erro ocorreu',
                    'Ocorreu um erro ao tentar alterar sua senha. (' + result
                    .responseJSON.mensagem + ')', {
                        duration: 6000
                    });
            }
        });
    }
    </script>

</body>


</html>