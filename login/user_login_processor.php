<?php
    $redirectUrl = "/";
    if (isset($_GET['redirect'])) {
        $redirectUrl = $_GET['redirect'];
    }

    if (isset($_SESSION)) {
        unset($_SESSION);
    }

    session_start();

    if(!isset($_POST['username']) || !isset($_POST['password'])) {
        $_SESSION['MESSAGES_LOGIN_ERROR'] = true;

        header('location: /login/?redirect=' . $redirectUrl);
        exit();
    }

    if(!isset($_POST['g-recaptcha-response'])) {
        $_SESSION['MESSAGES_LOGIN_ERROR'] = null;
        $_SESSION['MESSAGES_LOGIN_ERROR_RECAPTCHA'] = true;

        header('location: /login/?redirect=' . $redirectUrl);
        exit();
    }

    include('../libs/databaseConnection.php');
    require($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $dotenv = Dotenv\Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT']);
    $dotenv->load();

    $recaptchaResponse = $_POST['g-recaptcha-response'];

    $verify_captcha = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $_ENV['GOOGLE_RECAPTCHA_SECRET_KEY'] . '&response=' . $recaptchaResponse); 
             
    $verify_response = json_decode($verify_captcha); 
        
    if($verify_response->success) {

        $username = mysqli_real_escape_string($mysqli, $_POST['username']);
        $password = mysqli_real_escape_string($mysqli, $_POST['password']);

        $query = "SELECT * FROM usuarios WHERE username='{$username}' AND password=md5('{$password}');";
        $result = mysqli_query($mysqli, $query);
        $row = mysqli_num_rows($result);

        if ($row == 1) {
            $userIp = $_SERVER['REMOTE_ADDR'];
            $userData = mysqli_fetch_assoc($result);
            $userId = $userData['id'];

            if(boolval($userData['troca_senha']) == true) {
                $tokenMudarSenha = getRandomStringRandomInt(60);
                $tokenQuery = "INSERT INTO tokens_senha (`token`, `id_usuario`, `valido_ate`) VALUES ('{$tokenMudarSenha}', '{$userId}', DATE_ADD(NOW(), INTERVAL 10 MINUTE));";
                $resultToken = mysqli_query($mysqli, $tokenQuery);

                $email = $userData['email'];
            
                if($resultToken) {

                    // --------------------------------------


                    $mail = new PHPMailer(true);

                    try {
                        $email_html = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/emails/trocaSenha.html');

                        $userName = $userData['nome'];

                        $email_html = str_replace('%PASSWORD_TOKEN%', $tokenMudarSenha, $email_html);
                        $email_html = str_replace('%USER_NAME%', $userName, $email_html);

                        $mail->isSMTP();
                        $mail->Host = 'smtp.hostinger.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'naoresponda@payoo.com.br';
                        $mail->Password = $_ENV['EMAIL_PASSWORD'];
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        $mail->Port = 465;

                        $mail->setFrom('naoresponda@payoo.com.br', 'Payoo: não responda');
                        $mail->addAddress($email);
                        $mail->addReplyTo('contato@payoo.com.br', 'Payoo: contato');

                        $mail->CharSet = "UTF-8";

                        $mail->isHTML(true);
                        $mail->Subject = 'Payoo: seu link para trocar de senha!';
                        $mail->Body = $email_html;
                        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                        $mail->send();
                        
                        $_SESSION['MESSAGES_LOGIN_ERROR'] = null;
                        $_SESSION['MESSAGES_LOGIN_CHANGE_PASSWORD'] = true;
                        header('location: /login/?redirect=' . $redirectUrl);
                        exit();

                    } catch (Exception $e) {
                        error_log($e);

                        $_SESSION['MESSAGES_LOGIN_ERROR'] = true;
                        header('location: /login/?redirect=' . $redirectUrl);
                        exit();
                    }

                    // --------------------------------------
                } else {
                    $_SESSION['MESSAGES_LOGIN_ERROR'] = true;
                    header('location: /login/?redirect=' . $redirectUrl);
                    exit();
                }

            } else {
                $query = "UPDATE `usuarios` SET first_login_ip = COALESCE(first_login_ip, '{$userIp}'), last_login_ip='{$userIp}', last_login_at=now(), first_login_at = COALESCE(first_login_at, now()) WHERE  `id`={$userId};";
                $resultUpdate = mysqli_query($mysqli, $query);
                
                $queryHistorico = "INSERT INTO historico_contas (id_usuario, tipo_evento, endereco_ip) VALUES ({$userId}, 'conta.autenticar', '{$userIp}');";
                $resultHistorico  = mysqli_query($mysqli, $queryHistorico);

                if($resultUpdate && $resultHistorico) {

                    $_SESSION['USER_ID'] = $userData['id'];
                    $_SESSION['USER_USERNAME'] = $userData['username'];
                    $_SESSION['USER_NAME'] = $userData['nome'];
                    $_SESSION['USER_ACCESS_LEVEL'] = $userData['nivel_acesso'];
    
                    $_SESSION['MESSAGES_LOGIN_ERROR'] = null;
    
                    header('location: ' . $redirectUrl);
    
                } else {
                    $_SESSION['MESSAGES_LOGIN_ERROR'] = true;
                    header('location: /login/?redirect=' . $redirectUrl);
                    exit();
                }
                exit();
            }
        } else {
            $_SESSION['MESSAGES_LOGIN_ERROR'] = true;
            header('location: /login/?redirect=' . $redirectUrl);
            exit();
        }
    } else {
        $_SESSION['MESSAGES_LOGIN_ERROR'] = null;
        $_SESSION['MESSAGES_LOGIN_ERROR_RECAPTCHA'] = true;

        header('location: /login/?redirect=' . $redirectUrl);
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