<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Propriétaire - Parking Partagé</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #666;
        }

        input {
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
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background-color: #218838;
        }

        .error {
            color: red;
            margin-top: 10px;
            text-align: center;
            display: none;
        }

        .links {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9em;
        }

        a {
            color: #28a745;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Espace Propriétaire</h2>
        <form id="loginForm">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Se connecter</button>
            <div id="errorMsg" class="error"></div>
        </form>
        <div class="links">
            <a href="/owner/register">Pas encore de compte ? S'inscrire</a>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target));
            const err = document.getElementById('errorMsg');
            err.style.display = 'none';

            try {
                const res = await fetch('/owner/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const json = await res.json();

                if (res.ok) {
                    // Save limited info if needed, but rely on cookies for auth
                    localStorage.setItem('owner_user', JSON.stringify({
                        id: json.id,
                        firstName: json.firstName,
                        lastName: json.lastName,
                        email: json.email
                    }));

                    // REDIRECTION to Dashboard
                    window.location.href = '/owner/dashboard';
                } else {
                    err.textContent = json.error || 'Erreur de connexion';
                    err.style.display = 'block';
                }
            } catch (e) {
                err.textContent = 'Erreur réseau';
                err.style.display = 'block';
            }
        });
    </script>
</body>

</html>