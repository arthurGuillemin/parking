<?php
$pageTitle = "Connexion";
ob_start();
?>

<div class="container" style="max-width: 500px; margin-top: 40px;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 25px;">Connexion</h2>

        <form id="loginForm" method="post" action="/login">
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Se connecter</button>
            <div id="message" style="margin-top: 15px;"></div>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="/register">Pas encore de compte ? S'inscrire</a>
        </div>
    </div>
</div>

<script>
    document.getElementById('loginForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        const messageDiv = document.getElementById('message');

        messageDiv.className = '';
        messageDiv.innerHTML = '';

        fetch('/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
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
                messageDiv.className = 'alert alert-success';
                messageDiv.innerHTML = 'Connexion réussie ! Redirection...';

                if (result.token) {
                    localStorage.setItem('auth_token', result.token);
                }

                setTimeout(() => {
                    if (result.role === 'owner') {
                        localStorage.setItem('owner_user', JSON.stringify({
                            firstName: result.firstName,
                            lastName: result.lastName
                        }));
                        window.location.href = '/owner/dashboard';
                    } else {
                        window.location.href = '/dashboard';
                    }
                }, 1000);
            })
            .catch(error => {
                messageDiv.className = 'alert alert-danger';
                messageDiv.innerHTML = error.message || 'Une erreur inattendue est survenue.';
            });
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>