<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
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
            background-color: #28a745;
            /* Green for owners to distinguish */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #218838;
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
            color: #28a745;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2>Connexion</h2>
        <?php if (isset($error) && $error): ?>
            <div class="error">Identifiants invalides</div>
        <?php endif; ?>
        <form method="post" action="/login">
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Se connecter</button>
            <div id="message"></div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const messageDiv = document.getElementById('message');

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                // Reset message
                messageDiv.className = '';
                messageDiv.innerHTML = '';

                fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(errorData => {
                                throw new Error(errorData.error || 'Erreur de connexion');
                            });
                        }
                        return response.json();
                    })
                    .then(result => {
                        messageDiv.className = 'success';
                        messageDiv.innerHTML = 'Connexion réussie ! Redirection...';

                        if (result.token) {
                            localStorage.setItem('auth_token', result.token);
                        }

                        // Store user info and redirect
                        setTimeout(() => {
                            if (result.role === 'owner') {
                                localStorage.setItem('owner_user', JSON.stringify({
                                    firstName: result.firstName,
                                    lastName: result.lastName
                                }));
                                window.location.href = '/owner/dashboard';
                            } else {
                                window.location.href = '/parkings';
                            }
                        }, 1000);
            })
                .catch(error => {
                    messageDiv.className = 'error';
                    messageDiv.innerHTML = error.message || 'Une erreur inattendue est survenue.';
                    console.error('Error:', error);
                });
        });
        });
    </script>
</body>

</html>