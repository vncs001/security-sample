//-------------- HASH -------------------------------------------------------------------------------------
// Função para calcular o hash SHA-256 de uma string de forma assíncrona
function sha256(str) {
    return crypto.subtle.digest('SHA-256', new TextEncoder().encode(str))
        .then(hashBuffer => {
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            return hashArray.map(byte => ('00' + byte.toString(16)).slice(-2)).join('');
        });
}

// Função hasha() que retorna uma promessa que resolve com o hash
function hasha(valor) {
    return sha256(valor)
        .then(hash => {
            console.log(hash);
            return hash;
        })
        .catch(error => {
            console.error('Erro:', error);
            throw error;
        });
}

// -------------------- REDEFINE SENHA ---------------------------------------------------------------------------
document.getElementById("redefsenha").addEventListener("click", function(event) {
    event.preventDefault();

    var email = document.getElementById("email-NS").value;
    var oldPassword = document.getElementById("old-password").value;
    var newPassword = document.getElementById("new-password").value;
    var confirmPassword = document.getElementById("confirm-password").value;

    if (newPassword !== confirmPassword) {
        document.getElementById("RedSenha").innerText = "As senhas não correspondem.";
        return;
    }
    
    hasha(newPassword)
        .then(newPasswordHash => {
            newPassword = newPasswordHash;
            return newPassword;
        })
    

    // Gerar hash do oldPassword
    hasha(oldPassword).then(hashedOldPassword => {
        let data = {
            email: email,
            oldPassword: hashedOldPassword, // Usar a senha antiga com hash
            newPassword: newPassword
        };

        fetch('reset_password_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            return response.text().then(text => {
                console.log('Resposta do servidor:', text); // Log da resposta
                if (!response.ok) {
                    throw new Error(text);
                }
                try {
                    return JSON.parse(text); // Tentativa de parse do JSON
                } catch (e) {
                    throw new Error('Resposta não é um JSON válido: ' + text);
                }
            });
        })
        .then(data => {
            if (data.error) {
                document.getElementById("RedSenha").innerText = data.error;
            } else {
                document.getElementById("RedSenha").innerText = data.message;
            }
        })
        .catch(error => {
            console.error('Erro ao redefinir senha:', error);
            document.getElementById("RedSenha").innerText = "Erro ao redefinir senha: " + error.message;
        });
    }).catch(error => {
        console.error('Erro ao gerar hash da senha antiga:', error);
        document.getElementById("RedSenha").innerText = "Erro ao gerar hash da senha antiga.";
    });
});
