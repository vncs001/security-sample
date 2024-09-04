<?php
include 'db_connec.php'; 
require 'vendor/autoload.php';

use Twilio\Rest\Client;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sid = getenv("ACa3d09858194c1353aa47b6ae837c2362");
    $token = getenv("2564a926d55f1476dbc43c243f0918df");

    $client = new Client($sid, $token);

    $smscode = $_POST['smscode'];

    $sql = "SELECT phone_number, verification_code FROM usuarios WHERE verification_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $smscode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Código SMS correto. Autenticação bem-sucedida!";
    } else {
        echo "Código SMS incorreto. Por favor, tente novamente.";
    }
} 

$conn->close();
?>
