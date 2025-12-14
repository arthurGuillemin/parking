<?php
$pageTitle = "Accueil - Gestion de Parking";
ob_start();
?>

<div class="hero">
    <h1>Trouvez votre place de parking</h1>
    <p>Réservez facilement une place de parking ou souscrivez à un abonnement près de chez vous ou de votre lieu de
        travail.</p>

    <div style="max-width: 500px; margin: 30px auto; text-align: left;">
        <form action="/parkings" method="GET" style="display:flex; gap:10px;">
            <input type="text" class="form-control" name="q" placeholder="Adresse, Ville..."
                style="margin-bottom:0; flex:1;">
            <button class="btn btn-primary" type="submit">Rechercher</button>
        </form>
    </div>
</div>

<div class="grid">
    <div class="card">
        <h3>Pour les Conducteurs</h3>
        <p>Créez un compte pour gérer vos réservations, consulter vos factures et accéder à vos parkings favoris.</p>
        <div class="card-actions">
            <a href="/register" class="btn btn-primary">Créer un compte</a>
        </div>
    </div>
    <div class="card">
        <h3>Pour les Propriétaires</h3>
        <p>Vous possédez un parking ? Inscrivez-vous pour gérer vos disponibilités, définir vos tarifs et suivre vos
            revenus.</p>
        <div class="card-actions">
            <a href="/owner/register" class="btn btn-outline-secondary">Espace Propriétaire</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>