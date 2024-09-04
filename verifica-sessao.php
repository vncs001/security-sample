<?php


if (isset($_COOKIE[session_name()])) {
    
    echo "Sessão está ativa. ID da sessão: " . $_COOKIE['user_email'];    
} else {
    header("HTTP/1.1 401 Unauthorized");

}
?>
