<?php
include 'db_connec.php'; 
require_once 'vendor/autoload.php'; 

use Twilio\Rest\Client;

function generateVerificationCode($length = 6) {
    return substr(str_shuffle(str_repeat($x='0123456789', ceil($length/strlen($x)) )),1,$length);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, password, phone_number, verification_code FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_password_hash = $row['password'];
        $phone_number = $row['phone_number'];
        $stored_verification_code = $row['verification_code'];


        if (password_verify($password, $stored_password_hash)) {

            $verification_code = generateVerificationCode();


            $update_sql = "UPDATE usuarios SET verification_code = ? WHERE email = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $verification_code, $email);
            $update_stmt->execute();

            //twilio
            $sid = getenv("ACa3d09858194c1353aa47b6ae837c2362");
            $token = getenv("2564a926d55f1476dbc43c243f0918df");

            $client = new Client($sid, $token);

            $client->messages->create(
                '+'.$phone_number,
                [
                    'from' => '+13185158159',
                    'body' => 'Seu código de verificação é: ' . $verification_code
                ]
            );


            header("Location: Autenticar.html");
            exit();
        } else {
            echo "Senha incorreta. Por favor, tente novamente.";
        }
    } else {
        echo "Nenhum usuário encontrado com este email.";
    }
} else {
    header("Location: login.php");
    exit();
}

$conn->close();
?>
