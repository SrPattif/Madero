<?php
    if (isset($_SESSION)) {
        unset($_SESSION);
    }

    session_start();

    if(!isset($_POST['password']) || !isset($_POST['token'])) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Campos obrigatórios faltantes."));
        http_response_code(400);
        exit();
    }

    if(!isset($_POST['g-recaptcha-response'])) {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "err_id" => "captcha", "mensagem" =>  "Não foi possível verificar o captcha."));
        http_response_code(400);
        exit();
    }

    include('../../libs/databaseConnection.php');
    require($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $dotenv = Dotenv\Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT']);
    $dotenv->load();

    $recaptchaResponse = $_POST['g-recaptcha-response'];

    $verify_captcha = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $_ENV['GOOGLE_RECAPTCHA_SECRET_KEY'] . '&response=' . $recaptchaResponse); 
             
    $verify_response = json_decode($verify_captcha); 
        
    if($verify_response->success) {

        $password = mysqli_real_escape_string($mysqli, $_POST['password']);
        $token = mysqli_real_escape_string($mysqli, $_POST['token']);

        $query = "SELECT * FROM tokens_senha ts WHERE ts.valido_ate >= NOW() AND ts.token = '{$token}'; ";
        $result = mysqli_query($mysqli, $query);
        $row = mysqli_num_rows($result);

        if ($row == 1) {
            $userIp = $_SERVER['REMOTE_ADDR'];
            $tokenData = mysqli_fetch_assoc($result);
            $userId = $tokenData['id_usuario'];

            $query = "UPDATE `usuarios` SET password=md5('{$password}'), bloqueado=0 WHERE `id`={$userId};";
            $resultUpdate = mysqli_query($mysqli, $query);
            
            if($resultUpdate) {
                header("Content-Type: application/json");
                echo json_encode(array("sucesso" => true, "mensagem" =>  "Senha alterada com sucesso."));
                http_response_code(200);
                exit();

            } else {
                header("Content-Type: application/json");
                echo json_encode(array("sucesso" => false, "mensagem" =>  "Ocorreu um erro de servidor ao atualizar a senha."));
                http_response_code(500);
                exit();
            }
            exit();
        } else {
            header("Content-Type: application/json");
            echo json_encode(array("sucesso" => false, "err_id" => "invalid_token", "mensagem" =>  "O token informado é inválido ou já expirou."));
            http_response_code(400);
            exit();
        }
    } else {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "err_id" => "captcha", "mensagem" =>  "Não foi possível verificar o captcha."));
        http_response_code(400);
        exit();
    }

    function getRandomStringRandomInt($length = 16) {
        $stringSpace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pieces = [];
        $max = mb_strlen($stringSpace, '8bit') - 1;
        for ($i = 0; $i < $length; ++ $i) {
            $pieces[] = $stringSpace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
?>