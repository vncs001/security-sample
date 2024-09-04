<?php
include 'db_connec.php';
require 'vendor/autoload.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

$data = json_decode(file_get_contents("php://input"));

if ($data !== null) {
    $nickname = isset($data->nickname) ? $data->nickname : '';
    $cpf_or_rg = isset($data->cpf) ? $data->cpf : '';
    $email = isset($data->email) ? $data->email : '';
    $password = isset($data->password) ? $data->password : '';
    $phone_number = isset($data->phone_number) ? $data->phone_number : '';
    $date_of_birth = isset($data->dateb) ? $data->dateb : '';

    if (empty($nickname) || empty($cpf_or_rg) || empty($email) || empty($password) || empty($phone_number) || empty($date_of_birth)) {
        echo json_encode(['error' => 'Todos os campos são obrigatórios.']);
        exit();
    } else {
        $sql_check_email = "SELECT * FROM usuarios WHERE email = ?";
        $stmt_check_email = $conn->prepare($sql_check_email);
        $stmt_check_email->bind_param("s", $email);
        $stmt_check_email->execute();
        $result_check_email = $stmt_check_email->get_result();

        if ($result_check_email->num_rows > 0) {
            echo json_encode(['error' => 'Este email já está cadastrado. Por favor, escolha outro.']);
            exit();
        } else {
            // Gerar a chave secreta do Google Authenticator
            $g = new GoogleAuthenticator();
            $secret = $g->generateSecret();

            $sql = "INSERT INTO usuarios (nickname, cpf_or_rg, email, password, phone_number, date_of_birth, google_authenticator_secret) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $nickname, $cpf_or_rg, $email, $password, $phone_number, $date_of_birth, $secret);

            if ($stmt->execute()) {
                // Retornar o URL do QR code para o usuário configurar 2FA
                $qrCodeUrl = \Sonata\GoogleAuthenticator\GoogleQrUrl::generate($email, $secret, 'SeuApp');
                echo json_encode(['success' => true, 'qrCodeUrl' => $qrCodeUrl]);
            } else {
                echo json_encode(['error' => 'Erro ao registrar usuário.']);
            }

            $stmt->close();
        }
    }
} else {
    echo json_encode(['error' => 'Nenhum dado recebido.']);
}

$conn->close();
?>
