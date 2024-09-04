function verificarNovasMensagens(tema) {
    setInterval(function() {
        carregarMensagens(tema);
    }, 1000); 
}

function abrirChat(tema) {
    var modal = document.getElementById("modal");
    var modalTitle = document.getElementById("modal-title");
    modalTitle.innerHTML = tema;
    modal.style.display = "block";

    carregarMensagens(tema); 
    verificarNovasMensagens(tema); 
}



function enviarMensagem() {
    var chatTopic = document.getElementById("modal-title").innerText;
    var mensagem = document.getElementById("message-input").value;
    if (mensagem.trim() !== "") {
        var sender = getCookie('user_email');
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
    }
}

function getCookie(name) {
    var cookies = document.cookie.split(';');
    for (var i = 0; i < cookies.length; i++) {
        var cookie = cookies[i].trim();
        if (cookie.startsWith(name + '=')) {
            return decodeURIComponent(cookie.substring(name.length + 1));
        }
    }
    return '';
}

function carregarMensagens(tema) {
    const chatContainer = document.getElementById('chat-container');
    chatContainer.innerHTML = ''; 

    fetch('receive_msg.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'topic=' + encodeURIComponent(tema)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Ocorreu um erro ao carregar as mensagens.');
        }
        return response.json();
    })
    .then(data => {
        exibirMensagens(data);
    })
    .catch(error => {
        console.error('Erro:', error.message);
    });
}


function exibirMensagens(messages) {
    const chatContainer = document.getElementById('chat-container');
    const formatter = new Intl.DateTimeFormat('pt-BR', { hour: 'numeric', minute: 'numeric', second: 'numeric' });

    messages.forEach(message => {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message');
        const timestamp = formatter.format(new Date(message.timestamp));
        messageElement.innerHTML = `
            <div class="message-info"> (${timestamp}) ${message.sender}: ${message.message} </div>

        `;
        chatContainer.appendChild(messageElement);
    });

    chatContainer.scrollTop = chatContainer.scrollHeight;
}

