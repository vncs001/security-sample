<?php
include 'db_connec.php';
require 'vendor/autoload.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Caminho para a chave privada
    $privateKeyPath = 'OpenSSL/bin/private_key.pem';

    // Carrega a chave privada
    $privateKey = file_get_contents($privateKeyPath);
    if ($privateKey === false) {
        die("Erro ao carregar a chave privada.");
    }

    // Recupera os dados enviados no formulário
    $encryptedFormData = $_POST['formData'];
    $encryptedAesKey = $_POST['aesKey'];
    $encryptedIv = $_POST['iv'];

    // Verificações de depuração
    if (empty($encryptedFormData)) {
        die("formData está vazio.");
    }
    if (empty($encryptedAesKey)) {
        die("aesKey está vazio.");
    }
    if (empty($encryptedIv)) {
        die("iv está vazio.");
    }

    // Descriptografa a chave AES
    if (!openssl_private_decrypt(base64_decode($encryptedAesKey), $decryptedAesKey, $privateKey)) {
        die("Erro ao descriptografar a chave AES.");
    }

    // Verificações de depuração adicionais
    if (empty($decryptedAesKey)) {
        die("Chave AES descriptografada está vazia.");
    }

    // Converte a chave AES de base64 para binário
    $decryptedAesKeyBinary = base64_decode($decryptedAesKey);
    
    // Verifica se a chave AES tem o tamanho correto
    if (strlen($decryptedAesKeyBinary) !== 32) {
        die("Chave AES tem um tamanho incorreto: " . strlen($decryptedAesKeyBinary) . " bytes.");
    }

    // Descriptografa o IV
    if (!openssl_private_decrypt(base64_decode($encryptedIv), $decryptedIv, $privateKey)) {
        die("Erro ao descriptografar o IV.");
    }

    // Verificações de depuração adicionais
    if (empty($decryptedIv)) {
        die("IV descriptografado está vazio.");
    }

    // Converte o IV de base64 para binário
    $decryptedIvBinary = base64_decode($decryptedIv);

    // Verifica se o IV tem o tamanho correto
    if (strlen($decryptedIvBinary) !== 16) {
        die("IV tem um tamanho incorreto: " . strlen($decryptedIvBinary) . " bytes.");
    }

    // Descriptografa os dados do formulário
    $decryptedFormData = openssl_decrypt(
        base64_decode($encryptedFormData),
        'aes-256-cbc',
        $decryptedAesKeyBinary,
        OPENSSL_RAW_DATA,
        $decryptedIvBinary
    );

    if ($decryptedFormData === false) {
        $error = openssl_error_string();
        die("Erro ao descriptografar os dados do formulário: " . $error);
    }

    // Converte os dados JSON de volta para um array PHP
    $formDataArray = json_decode($decryptedFormData, true);

    if ($formDataArray === null) {
        die("Erro ao decodificar os dados JSON.");
    }


    $email = htmlspecialchars($formDataArray['email']);
    $senhaHash = htmlspecialchars($formDataArray['password']);
    $code2FA = htmlspecialchars($formDataArray['code2FA']);
    // Exibe os dados descriptografados
    // echo "Email: " . htmlspecialchars($formDataArray['email']) . "<br>";
    // echo "Password Hash: " . htmlspecialchars($formDataArray['password']) . "<br>";
    // echo "2FA Code: " . htmlspecialchars($formDataArray['code2FA']) . "<br>";

    echo "Email: " . $email . "<br>";
    echo "Password Hash: " . $senhaHash . "<br>";
    echo "2FA Code: " . $code2FA . "<br>";



    function authenticateUser($email, $senhaHash, $code2FA) {
        global $conn;
        $g = new GoogleAuthenticator();

        $sql = "SELECT password, google_authenticator_secret FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $senhaDoBanco = $row["password"];
            $secret = $row["google_authenticator_secret"];

            if (password_verify($senhaHash, $senhaDoBanco) && $g->checkCode($secret, $code2FA)) {
                session_start();                    
                $tempoExpiracao = time() + 99999999999; //segundos
                setcookie('user_email', $email, $tempoExpiracao, '/');                

                $stmt->close();
                return true;
            } else {
                $stmt->close();
                return false;
            }
        } else {
            $stmt->close();
            return false;
        }
    }
    if (authenticateUser($email, $senhaHash, $code2FA)) {
        echo json_encode(["message" => "Usuário autenticado com sucesso!"]);
    } else {
        echo json_encode(["message" => "Email, senha ou código de autenticação incorretos."]);
    }
}


$conn->close();
?>


