<?php


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "db_connec.php";


    error_log("POST data: " . print_r($_POST, true));
    error_log("SESSION data: " . print_r($_SESSION, true));

    if (isset($_POST['sender']) && isset($_POST['message']) && isset($_POST['topic'])) {
        $sender = $_POST['sender'];
        $message = $_POST['message'];
        $topic = $_POST['topic'];

        $stmt = $conn->prepare("INSERT INTO messages (sender, message, topic) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $sender, $message, $topic);

        if ($stmt->execute()) {
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro ao enviar a mensagem.";
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "Parâmetros ausentes.";
    }
} else {
    echo "Método não permitido.";
}
?>
