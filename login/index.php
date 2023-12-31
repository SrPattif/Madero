<?php
    if (!isset($_SESSION)) {
        session_start();
    }

    $redirectUrl = "/";
    if (isset($_GET['redirect'])) {
        $redirectUrl = $_GET['redirect'];
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticação | Controladoria Grupo Madero</title>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <!-- Estilos -->
    <link rel="stylesheet" href="./index.css" />

</head>

<body>

    <div class="left-content">
        <div class="top">
            <img class="madero-logo" src="/assets/images/madero-white.png" alt="">
        </div>

        <div class="content">
            <h1>Entrar</h1>
            <?php
                if(isset($_SESSION['MESSAGES_LOGIN_ERROR'])) {
                    $_SESSION['MESSAGES_LOGIN_ERROR'] = null;
            ?>
            <span class="text-error">Não foi possível te autenticar. Verifique o nome de usuário e a senha e
                tente novamente.</span>
            <?php
                } else if(isset($_SESSION['MESSAGES_LOGIN_ERROR_RECAPTCHA'])) {
                    $_SESSION['MESSAGES_LOGIN_ERROR_RECAPTCHA'] = null;
            ?>
            <span class="text-error">Não foi possível verificar sua identidade. Verifique se a caixa de "Não sou um robô" está marcada.</span>
            <?php
                } else if(isset($_SESSION['MESSAGES_LOGIN_CHANGE_PASSWORD'])) {
                    $_SESSION['MESSAGES_LOGIN_CHANGE_PASSWORD'] = null;
            ?>
            <span class="text-info">Um link para troca de senha foi enviado ao e-mail cadastrado.</span>
            <?php
                }
            ?>
            <form id="login-form" action="./user_login_processor.php?redirect=<?php echo($redirectUrl); ?>"
                method="POST">
                <div class="form-row">
                    <label for="input_username">Nome de Usuário</label>
                    <input type="text" name="username" id="input_username" required>
                </div>

                <div class="form-row">
                    <label for="input_password">Senha</label>
                    <input type="password" name="password" id="input_password" required>
                </div>

                <div class="form-row">
                    <div class="g-recaptcha" data-sitekey="6Lenv2YmAAAAADf5nvmNCv2HJsWlpvQa9QkH3FYE"></div>
                </div>

                <div class="form-row">
                    <button type="submit">ENTRAR</button>
                </div>


            </form>
        </div>

        <div class="footer">
            &copy; 2023 - 2023 GRUPO MADERO - Todos os Direitos Reservados.
        </div>
    </div>

    <div class="right-content">
    </div>
</body>

<script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
<script>
var urlArray = [
    '/assets/images/bg_1.jpg',
    '/assets/images/bg_2.jpg',
    '/assets/images/bg_3.jpg',
    '/assets/images/bg_4.jpg',
]
var randomIndex = Math.floor(Math.random() * urlArray.length);

$(".right-content").css(`background-image`, `url("${urlArray[randomIndex]}")`);
</script>

</html>

</html>