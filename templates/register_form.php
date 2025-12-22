<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Parking Partagé</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
            font-size: 2em;
        }

        .form-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-row .form-group {
            flex: 1 1 0;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        .success {
            color: green;
            margin-top: 10px;
        }

        .link {
            text-align: center;
            margin-top: 15px;
        }

        .link a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2 style="text-align: center;">Espace Utilisateur</h2>
        <form id="registerForm" method="POST" action="/user/register">
            <div class="form-row">
                <div class="form-group">
                    <label for="firstName">Prénom *</label>
                    <input type="text" id="firstName" name="firstName" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Nom *</label>
                    <input type="text" id="lastName" name="lastName" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Mot de passe *</label>
                    <input type="password" id="password" name="password" required minlength="8"
                        pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$">
                    <small style="color: #666; font-size: 12px;">Au moins 8 caractères, une lettre et un chiffre</small>
                </div>
                <div class="form-group">
                    <label for="passwordConfirm">Confirmer le mot de passe *</label>
                    <input type="password" id="passwordConfirm" name="passwordConfirm" required minlength="8">
                </div>
            </div>

            <button type="submit">S'inscrire</button>
            <div id="message"></div>
        </form>
        <div class="link">
            <a href="/login">Déjà un compte ? Se connecter</a>
        </div>
        <div class="link" style="margin-top: 10px;">
            <a href="/" style="color: #666;">← Retour à l'accueil</a>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            const messageDiv = document.getElementById('message');
            messageDiv.innerHTML = '';
            messageDiv.className = '';

            // Validation côté client
            const password = data.password.trim();
            const passwordConfirm = data.passwordConfirm.trim();

            // Vérification longueur
            if (password.length < 8) {
                messageDiv.className = 'error';
                messageDiv.innerHTML = 'Le mot de passe doit contenir au moins 8 caractères';
                return;
            }
            // Vérification format
            if (!/[A-Za-z]/.test(password) || !/\d/.test(password)) {
                messageDiv.className = 'error';
                messageDiv.innerHTML = 'Le mot de passe doit contenir au moins une lettre et un chiffre';
                return;
            }
            // Vérification correspondance
            if (password !== passwordConfirm) {
                messageDiv.className = 'error';
                messageDiv.innerHTML = 'Les mots de passe ne correspondent pas';
                return;
            }

            // Retirer passwordConfirm avant l'envoi
            //delete data.passwordConfirm;

            try {
                const response = await fetch('/user/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok) {
                    messageDiv.className = 'success';
                    messageDiv.innerHTML = 'Inscription réussie ! Redirection...';
                    setTimeout(() => {
                        window.location.href = '/login';
                    }, 2000);
                } else {
                    messageDiv.className = 'error';
                    messageDiv.innerHTML = result.error || 'Erreur lors de l\'inscription';
                }
            } catch (error) {
                messageDiv.className = 'error';
                messageDiv.innerHTML = 'Erreur de connexion au serveur';
            }
        });
    </script>
</body>

</html>