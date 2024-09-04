<?php
require_once('src/PHPMailer.php');
require_once('src/SMTP.php');
require_once('src/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Lê a entrada JSON do POST
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['email'])) {
    $email = $data['email'];

    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'forumdeajudaexp@gmail.com';
        $mail->Password = 'eeiy acfc lgdh xoka';
        $mail->Port = 587;

  
        $mail->setFrom('viniciusaugustodemoraes@gmail.com');  
        $mail->addAddress($email); // email passado pelo JavaScript

        $mail->isHTML(true);
        $mail->Subject = 'Troca de senha';
        $mail->Body = 'Foi solicitada a troca de senha para o seu email no forum Hellp, por favor acesse o link para trocar a senha:  <br> <br> <a id="recuperar-senha" href="http://localhost/Hellp-8/Rsenha.html" onclick>Recupere sua senha</a>';
        $mail->AltBody = 'Foi solicitada a troca de senha para o seu email no forum Hellp, por favor acesse o link para trocar a senha:  http://localhost/Hellp-8/Rsenha.html';

        if($mail->send()){
            echo 'email enviado';
            //header('Location: Registo.html');
            exit();
        } else {
            echo 'Houve um problema ao enviar email';
        }
        
    } catch(Exception $e) {
        echo "Erro ao enviar mensagem: {$mail->ErrorInfo}";
    }
} else {
    echo 'Email não fornecido.';
}
?>