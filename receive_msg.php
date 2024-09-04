<?php


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "db_connec.php";

    if (isset($_POST['topic'])) {
        $topic = $_POST['topic'];

        $stmt = $conn->prepare("SELECT sender, message, timestamp FROM messages WHERE topic = ?");
        $stmt->bind_param("s", $topic);
        $stmt->execute();
        $result = $stmt->get_result();

        $messages = array();
        while ($row = $result->fetch_assoc()) {
            $messages[] = array(
                'sender' => $row['sender'],
                'message' => $row['message'],
                'timestamp' => $row['timestamp']
            );
        }

        $stmt->close();
        $conn->close();

        header('Content-Type: application/json');
        echo json_encode($messages);
    } else {
        echo "Parâmetro 'topic' ausente.";
    }
} else {
    echo "Método não permitido.";
}
