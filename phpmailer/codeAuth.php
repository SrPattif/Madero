<?php
require($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

session_start();

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    // Gere um código de 6 dígitos aleatório
    $codigo = sprintf("%06d", mt_rand(1, 999999));

    // Divida o código em duas partes
    $code_first = substr($codigo, 0, 3);
    $code_second = substr($codigo, 3, 3);

    // Carregue o conteúdo do arquivo HTML
    $email_html = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/emails/codigoConfirmacao.html');

    $userName = ":)";
    if(isset($_SESSION['USER_NAME'])) {
        $userName = $_SESSION['USER_NAME'];
    }

   // Substitua %CODE_FIRST% e %CODE_SECOND% pelo código gerado
    $email_html = str_replace('%CODE_FIRST%', $code_first, $email_html);
    $email_html = str_replace('%CODE_SECOND%', $code_second, $email_html);
    $email_html = str_replace('%USER_NAME%', $userName, $email_html);

    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host = 'smtp.hostinger.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth = true;                                   //Enable SMTP authentication
    $mail->Username = 'naoresponda@payoo.com.br';                     //SMTP username
    $mail->Password = 'PASSWORD HERE';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('naoresponda@payoo.com.br', 'Payoo');
    $mail->addAddress('ogustavoantonio07@gmail.com');     //Add a recipient
    $mail->addReplyTo('contato@payoo.com.br', 'Contato');

    $mail->CharSet = "UTF-8";

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Payoo: seu código de confirmação!';
    $mail->Body = $email_html;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}