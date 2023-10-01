<?php
require($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $token = "asdasdasd";
    $email_html = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/emails/trocaSenha.html');

    $userName = ":)";
    if(isset($_SESSION['USER_NAME'])) {
        $userName = $_SESSION['USER_NAME'];
    }

    $email_html = str_replace('%PASSWORD_TOKEN%', $token, $email_html);
    $email_html = str_replace('%USER_NAME%', $userName, $email_html);

    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'naoresponda@payoo.com.br';
    $mail->Password = 'PASSWORD HERE';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('naoresponda@payoo.com.br', 'Payoo');
    $mail->addAddress('ogustavoantonio07@gmail.com');
    $mail->addReplyTo('contato@payoo.com.br', 'Contato');

    $mail->CharSet = "UTF-8";

    $mail->isHTML(true);
    $mail->Subject = 'Payoo: seu link para trocar de senha!';
    $mail->Body = $email_html;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}