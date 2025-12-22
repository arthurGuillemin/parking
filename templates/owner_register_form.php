<?php
$pageTitle = "Inscription Propriétaire";
ob_start();
?>

<div class="container" style="max-width: 600px; margin-top: 40px;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 25px;">Espace Propriétaire</h2>

        <form id="ownerRegisterForm" method="POST" action="/owner/register">
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="firstName">Prénom *</label>
                        <input type="text" id="firstName" name="firstName" class="form-control" required>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="lastName">Nom *</label>
                        <input type="text" id="lastName" name="lastName" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="password">Mot de passe *</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="8"
                            pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$">
                        <small class="text-muted">Au moins 8 caractères, une lettre et un chiffre</small>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="passwordConfirm">Confirmer *</label>
                        <input type="password" id="passwordConfirm" name="passwordConfirm" class="form-control" required
                            minlength="8">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100">Devenir Propriétaire</button>
            <div id="message" style="margin-top: 15px;"></div>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="/login">Déjà propriétaire ? Se connecter</a>
        </div>
    </div>
</div>

<script>
    document.getElementById('ownerRegisterForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        const messageDiv = document.getElementById('message');

        messageDiv.innerHTML = '';
        messageDiv.className = '';

        const password = data.password.trim();
        const passwordConfirm = data.passwordConfirm.trim();

        if (password.length < 8) {
            messageDiv.className = 'alert alert-danger';
            messageDiv.innerHTML = 'Le mot de passe doit contenir au moins 8 caractères';
            return;
        }
        if (!/[A-Za-z]/.test(password) || !/\d/.test(password)) {
            messageDiv.className = 'alert alert-danger';
            messageDiv.innerHTML = 'Le mot de passe doit contenir au moins une lettre et un chiffre';
            return;
        }
        if (password !== passwordConfirm) {
            messageDiv.className = 'alert alert-danger';
            messageDiv.innerHTML = 'Les mots de passe ne correspondent pas';
            return;
        }

        try {
            const response = await fetch('/owner/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                messageDiv.className = 'alert alert-success';
                messageDiv.innerHTML = 'Inscription propriétaire réussie ! Redirection...';
                setTimeout(() => { window.location.href = '/login'; }, 2000);
            } else {
                messageDiv.className = 'alert alert-danger';
                messageDiv.innerHTML = result.error || 'Erreur lors de l\'inscription';
            }
        } catch (error) {
            messageDiv.className = 'alert alert-danger';
            messageDiv.innerHTML = 'Erreur de connexion au serveur';
        }
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>