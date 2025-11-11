<?php
$content = '
<h2>Connexion</h2>
<form method="post" action="/login">
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Mot de passe: <input type="password" name="password" required></label><br>
    <button type="submit">Se connecter</button>
</form>
';
require __DIR__ . '/layout.php';
