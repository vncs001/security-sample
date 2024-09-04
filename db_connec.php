<?php
$servername = "localhost"; 

function get_string_between($string, $start, $end) {
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

$username_file = 'TextoReserva.txt';
$password_file = 'index_html.txt';
$dbname_file = 'estilos.txt';

$username_content = file_get_contents($username_file);
$password_content = file_get_contents($password_file);
$dbname_content = file_get_contents($dbname_file);

if ($username_content === false || $password_content === false || $dbname_content === false) {
    die("Não foi possível ler um ou mais arquivos.");
}

$username = get_string_between($username_content, 'username: ', '.');
$password = get_string_between($password_content, 'password: ', '.');
$dbname = get_string_between($dbname_content, 'dbname: ', '.');

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

?>
