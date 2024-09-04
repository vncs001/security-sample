<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum</title>
    <link rel="stylesheet" href="styles.css">
    <?php
       if (!isset($_COOKIE['user_email'])) {
        header('location: Registo.html');
    }
    ?>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="Registo.html">Home</a></li>
                <li><a href="Forum.php">Forum</a></li>
                <li><a href="logout.php">Logout</a></li>
                <li><div id="session-status"></div></li>
            </ul>
        </nav>        
    </header>
    
    <div id="content" class="container" style="display: block;">
        <div class="form-box">
            <div class="forum-links">
                <h2 style="text-align: center;">Conteúdo do Fórum</h2>
                <ul>
                    <li><a href="#" class="forum-link-btn" onclick="abrirChat('Mindfulness e Meditação')">Mindfulness e Meditação</a></li>
                    <li><a href="#" class="forum-link-btn" onclick="abrirChat('Superando Traumas')">Superando Traumas</a></li>
                    <li><a href="#" class="forum-link-btn" onclick="abrirChat('Saúde Mental')">Saúde Mental</a></li>
                    <li><a href="#" class="forum-link-btn" onclick="abrirChat('Autoestima e Autoconfiança')">Autoestima e Autoconfiança</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <div id="modal" class="modal">
        <div class="modal-content">
            <span id="modal-close" onclick="fecharChat()" style="float: right; cursor: pointer;">&times;</span>
            <h2 id="modal-title" style="text-align: center;"></h2>
            <div id="chat-container" class="chat-box"></div>
            <textarea id="message-input" class="message-input" placeholder="Digite sua mensagem"></textarea> <!-- Adicionado a classe message-input -->
            <button onclick="enviarMensagem()">Enviar</button>
        </div>
    </div>
    
    <footer>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="scriptChat.js"></script>
        <script>

            function abrirChat(tema) {
                var modal = document.getElementById("modal");
                var modalTitle = document.getElementById("modal-title");
                modalTitle.innerHTML = tema;
                modal.style.display = "block";
                carregarMensagens(tema);
            }

            function fecharChat() { 
                var modal = document.getElementById("modal");
                modal.style.display = "none";
            }

            function enviarMensagem() {
                var chatTopic = document.getElementById("modal-title").innerText;
                var mensagem = document.getElementById("message-input").value;
                if (mensagem.trim() !== "") {
                    var sender = getCookie('user_email');
                    if (sender) {
                        var data = new FormData();
                        data.append('sender', sender);
                        data.append('message', mensagem);
                        data.append('topic', chatTopic);

                        fetch('send_msg.php', {
                            method: 'POST',
                            body: data
                        })
                        .then(response => response.text())
                        .then(data => {
                            console.log(data);
                            carregarMensagens(chatTopic);
                            document.getElementById("message-input").value = ""; 
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                        });
                    } else {
                        console.error('Erro ao obter o email do usuário.');
                    }
                }
            }

            window.onload = function() {
                verificaSessao();
            };
        </script>
    </footer>
</body>
</html>
