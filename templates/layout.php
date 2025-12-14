<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : 'Parkingtest' ?></title>
    <link rel="stylesheet" href="/style.css">
</head>

<body>
    <header>
        <h1><a href="/" style="color:white; text-decoration:none;">Parkingtest</a></h1>
        <nav>
            <a href="/">Accueil</a>
            <a href="/parkings">Rechercher</a>
            <?php if (isset($_COOKIE['auth_token']) || isset($_SESSION['user_id'])): ?>
                <a href="/dashboard">Mon Tableau de Bord</a>
                <a href="/simulation">Simulation (Entrée/Sortie)</a>
                <a href="/logout">Déconnexion</a>
            <?php else: ?>
                <a href="/login">Connexion</a>
                <a href="/register">Inscription</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="container">
        <?php if (isset($content))
            echo $content; ?>
    </main>
    <footer style="text-align:center; padding: 20px; color:#777;">
        <small>&copy; <?= date('Y') ?> Parkingtest - Gestion de parking simplifiée</small>
    </footer>
</body>

</html>