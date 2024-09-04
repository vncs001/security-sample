<?php
include 'db_connec.php';
require 'vendor/autoload.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

$privateKeyPath = 'OpenSSL/bin/private_key.pem';

try {
    if (!file_exists($privateKeyPath)) {
        throw new Exception('Chave privada não encontrada no caminho especificado: ' . $privateKeyPath);
    }

    $privateKeyContent = file_get_contents($privateKeyPath);

    if ($privateKeyContent === false) {
        throw new Exception('Erro ao ler o conteúdo da chave privada.');
    }

    $privateKey = openssl_pkey_get_private($privateKeyContent);

    if (!$privateKey) {
        $error = openssl_error_string();
        throw new Exception('Falha ao carregar a chave privada. Erro: ' . $error);
    }

    $encryptedFormData = $_POST['formData'];
    $encryptedAesKey = $_POST['aesKey'];
    $encryptedIv = $_POST['iv'];

    // Adicionando logs de depuração
    error_log("encryptedAesKey: " . $encryptedAesKey);
    error_log("encryptedIv: " . $encryptedIv);
    error_log("encryptedFormData: " . $encryptedFormData);

    if (!openssl_private_decrypt(base64_decode($encryptedAesKey), $aesKey, $privateKey)) {
        $error = openssl_error_string();
        throw new Exception('Erro ao decriptar a chave AES. Erro: ' . $error);
    }

    if (!openssl_private_decrypt(base64_decode($encryptedIv), $iv, $privateKey)) {
        $error = openssl_error_string();
        throw new Exception('Erro ao decriptar o IV. Erro: ' . $error);
    }

    // Adicionando logs de depuração para os valores de aesKey e iv
    error_log("aesKey: " . base64_encode($aesKey));
    error_log("iv: " . base64_encode($iv));

    $aesKey = base64_decode($aesKey);
    $iv = base64_decode($iv);

    $decryptedFormData = openssl_decrypt(base64_decode($encryptedFormData), 'aes-256-cbc', $aesKey, OPENSSL_RAW_DATA, $iv);

    if ($decryptedFormData === false) {
        throw new Exception('Erro ao decriptar os dados do formulário com AES.');
    }

    $formData = json_decode($decryptedFormData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Erro ao decodificar os dados do formulário JSON.');
    }

    $email = $formData['email'];
    $senhaHash = $formData['password'];
    $code2FA = $formData['code2FA'];

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

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>