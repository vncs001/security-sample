//-------------- EMAIL------------------
function mailsend(){
    var email = document.getElementById("email").value;
    fetch('mail.php', {
        method: "POST",
        body: JSON.stringify({
            email: email,
        }),
        headers: {
            "Content-Type": "application/json"
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro ao carregar o arquivo PHP.');
        }
        return response.text();
    })
    .then(responseText => {
        console.log('Enviado: ' + email);
    })
    .catch(error => {
        console.error(error);
    });
    $('#enviado').text('Confira seu email!')
}

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
//-------------------- REGISTRO ---------------------------------------------------------------------------

function validateRegistration() {
    var nickname = document.getElementById("nickname").value;
    var cpf = document.getElementById("CPF").value;
    var email = document.getElementById("register-email").value;
    var password = document.getElementById("register-password").value;
    var confirmPassword = document.getElementById("confirmpassword").value;
    var dateOfBirth = document.getElementById("dateb").value;
    var phoneNumber = document.getElementById('phone_number').value;

    if (!nickname || !cpf || !email || !password || !confirmPassword || !dateOfBirth || !phoneNumber) {
        document.getElementById("register-message").innerText = "Todos os campos devem ser preenchidos.";
        return false;
    }

    if (password !== confirmPassword) {
        document.getElementById("register-message").innerText = "As senhas não coincidem.";
        return false;
    }

    var passwordPattern = /^(?=.*[A-Z])(?=(?:.*[0-9]){2})(?=.*[@$!%#?&])[A-Za-z0-9@$!%#?&.]{8,}$/;
    if (!passwordPattern.test(password)) {
        document.getElementById("register-message").innerText = "A senha deve conter no mínimo 8 caracteres, 1 letra maiúscula, 2 números e um caractere especial.";
        return false;
    }

    var cpfRgPattern = /\d{9}-\d{2}|\d{8}-\d{1}/;
    if (!cpfRgPattern.test(cpf)) {
        document.getElementById("register-message").innerText = "Formato inválido para CPF ou RG.";
        return false;
    }

    var emailPattern = /^[\w\.-]+@[\w]+\.(com)$/;
    if (!emailPattern.test(email)) {
        document.getElementById("register-message").innerText = "Formato inválido para o e-mail.";
        return false;
    }

    var nicknamePattern = /^[\w]{2,10}$/;
    if (!nicknamePattern.test(nickname)) {
        document.getElementById("register-message").innerText = "O apelido deve conter de 2 a 10 caracteres alfanuméricos.";
        return false;
    }

    var phonePattern = /^\d{11}$/;
    if (!phonePattern.test(phoneNumber)) {
        document.getElementById("register-message").innerText = "Formato inválido para o número de celular (apenas números com DDD).";
        return false;
    }

    document.getElementById("register-message").innerText = "";
    return true;
}

document.getElementById("register-button").addEventListener("click", function(event) {
    event.preventDefault();

    if (validateRegistration()) {
        var nickname = document.getElementById("nickname").value;
        var cpf = document.getElementById("CPF").value;
        var email = document.getElementById("register-email").value;
        var password = document.getElementById("register-password").value;
        var confirmPassword = document.getElementById("confirmpassword").value;
        var dateOfBirth = document.getElementById("dateb").value;
        var phoneNumber = document.getElementById('phone_number').value;

        hasha(password)
            .then(senhaHash => {
                return fetch("register.php", {
                    method: "POST",
                    body: JSON.stringify({
                        nickname: nickname,
                        cpf: cpf,
                        email: email,
                        password: senhaHash,
                        phone_number: phoneNumber,
                        dateb: dateOfBirth
                    }),
                    headers: {
                        "Content-Type": "application/json"
                    }
                });
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("qrCodeImage").src = data.qrCodeUrl;
                    document.getElementById("qrCodeModal").style.display = "block";
                    document.getElementById("modalOverlay").style.display = "block";
                    document.getElementById("register-message").innerText = "Registro bem-sucedido!";
                } else if (data.error) {
                    document.getElementById("register-message").innerText = data.error;
                }
            })
            .catch(error => {
                console.error("Erro ao registrar usuário:", error);
            });
    }
});

document.getElementById("closeModal").addEventListener("click", function() {
    document.getElementById("qrCodeModal").style.display = "none";
    document.getElementById("modalOverlay").style.display = "none";
});

document.getElementById("modalOverlay").addEventListener("click", function() {
    document.getElementById("qrCodeModal").style.display = "none";
    document.getElementById("modalOverlay").style.display = "none";
});

// -------------------- LOGIN ---------------------------------------------------------------------------

//   PASSOS
//Fetch para buscar a chave publica
//criptografar dentro do then com a chave publica
//Usar a chave publica para enviar os dados para o servidor


document.getElementById("login-button").addEventListener("click", function(event) {
    event.preventDefault();

    var email = document.getElementById("login-email").value;
    var senha = document.getElementById("login-password").value;
    var code2FA = document.getElementById("2fa-code").value;

    if (!email || !senha || !code2FA) {
        document.getElementById("login-message").innerText = "Todos os campos devem ser preenchidos.";
        return;
    }

    hasha(senha)
        .then(senhaHash => {
            fetch("OpenSSL/bin/public_key.pem")
                .then((response) => {
                    if (!response.ok) {
                        throw new Error("Erro ao carregar a chave pública.");
                    }
                    return response.text();
                })
                .then((publicKey) => {
                    const aesKey = CryptoJS.lib.WordArray.random(32);
                    const iv = CryptoJS.lib.WordArray.random(16);

                    const formDataObj = {
                        email: email,
                        password: senhaHash,
                        code2FA: code2FA
                    };

                    const formDataJson = JSON.stringify(formDataObj);

                    const encryptedFormData = CryptoJS.AES.encrypt(formDataJson, aesKey, {
                        iv: iv,
                        mode: CryptoJS.mode.CBC,
                        padding: CryptoJS.pad.Pkcs7
                    }).toString();

                    const aesKeyBase64 = CryptoJS.enc.Base64.stringify(aesKey);
                    const ivBase64 = CryptoJS.enc.Base64.stringify(iv);

                    const encrypt = new JSEncrypt();
                    encrypt.setPublicKey(publicKey);

                    const encryptedAesKey = encrypt.encrypt(aesKeyBase64);
                    const encryptedIv = encrypt.encrypt(ivBase64);

                    if (!encryptedFormData || !encryptedAesKey || !encryptedIv) {
                        alert("Erro ao criptografar os dados.");
                        return;
                    }

                    const formData = new FormData();
                    formData.append("formData", encryptedFormData);
                    formData.append("aesKey", encryptedAesKey);
                    formData.append("iv", encryptedIv);

                    fetch("confirma-senha.php", {
                        method: "POST",
                        body: formData
                    })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error("Erro na resposta do servidor.");
                        }
                        return response.json();
                    })
                    .then(data => {
                        document.getElementById("login-message").innerText = data.message;
                    })
                })
        })
        .catch(error => {
            console.error("Erro ao calcular hash:", error);
        });
});


// -------------------- Validar entradas---------------------------------------------------------------------------


// fetch("confirma-senha.php", {
//     method: "POST",
//     body: formData
// })
// .then(response => response.text())
// .then(data => {
//     document.getElementById("login-message").innerText = data;
// })
// .catch(error => {
//     console.error("Erro ao realizar login:", error);
// });
// })

 // var formData = new FormData();
            // formData.append('login-email', email);
            // formData.append('login-password', senhaHash);
            // formData.append('2fa-code', code2FA);