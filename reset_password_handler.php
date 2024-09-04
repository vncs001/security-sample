<?php
include 'db_connec.php';


header('Content-Type: application/json'); // Define o cabeçalho como JSON

$data = json_decode(file_get_contents("php://input"));

$response = array();

if ($data) {
    $email = $data->email;
    $senhaNovaH = $data->newPassword; // Corrigido para 'newPassword'
    $senhaOld = $data->oldPassword; // Corrigido para 'oldPassword'

    // Verifica se o e-mail existe na tabela 'usuarios'
    $sql_check_email = "SELECT * FROM usuarios WHERE email = ?";
    $stmt_check_email = $conn->prepare($sql_check_email);
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $result_check_email = $stmt_check_email->get_result();

    if ($result_check_email->num_rows > 0) {
        $usuario = $result_check_email->fetch_assoc();
        
        // Verifica se a senha antiga está correta
        if ($senhaOld == $usuario['password']) {
            // Atualiza a senha antiga para a nova
            $sql_update_senha = "UPDATE usuarios SET password = ? WHERE email = ?";
            $stmt_update_senha = $conn->prepare($sql_update_senha);
            $stmt_update_senha->bind_param("ss", $senhaNovaH, $email);

            if ($stmt_update_senha->execute()) {
                $response['message'] = "Senha atualizada com sucesso.";
            } else {
                $response['error'] = "Erro ao atualizar a senha: " . $stmt_update_senha->error;
            }

            // Fecha a declaração do update
            $stmt_update_senha->close();
        } else {
            $response['error'] = "A senha antiga está incorreta.";
        }
    } else {
        $response['error'] = "E-mail não encontrado.";
    }

    // Fecha a declaração de verificação do e-mail
    $stmt_check_email->close();
}

// Fecha a conexão com o banco de dados
$conn->close();

// Retorna a resposta como JSON
echo json_encode($response);
?>
