<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : 'Parking Partagé' ?></title>
    <link rel="stylesheet" href="/style.css">
</head>

<body>
    <header>
        <h1><a href="/" style="color:white; text-decoration:none;">Parking Partagé</a></h1>
        <nav>
            <a href="/">Accueil</a>
            <?php if (isset($_COOKIE['auth_token']) || isset($_SESSION['user_id'])): ?>
                <?php
                $headerUser = null;
                try {
                    if (isset($_COOKIE['auth_token'])) {
                        $jwtService = new \App\Domain\Service\JwtService();
                        $userId = $jwtService->validateToken($_COOKIE['auth_token']);
                        if ($userId) {
                            $userRepo = new \App\Infrastructure\Persistence\Sql\SqlUserRepository();
                            $headerUser = $userRepo->findById($userId);
                        }
                    }
                } catch (\Exception $e) {
                }
                ?>

                <span style="color: #aaa; margin: 0 10px;">|</span>
                <a href="/parkings">Rechercher</a>
                <span style="color: #aaa; margin: 0 10px;">|</span>
                <a href="/dashboard">Tableau de bord</a>
                <span style="color: #aaa; margin: 0 10px;">|</span>
                <a href="/simulation">Simulation</a>

                <?php if ($headerUser): ?>
                    <span style="color: #aaa; margin: 0 10px;">|</span>
                    <span style="color: #eee; font-weight: bold;">
                        <?= htmlspecialchars($headerUser->getFirstName() . ' ' . $headerUser->getLastName()) ?>
                    </span>
                <?php endif; ?>

                <span style="color: #aaa; margin: 0 10px;">|</span>
                <form action="/logout" method="POST" style="display:inline;">
                    <button type="submit"
                        style="background:none; border:none; color:white; cursor:pointer; text-decoration:underline;">
                        Déconnexion
                    </button>
                </form>
            <?php else: ?>
                <span style="color: #aaa; margin: 0 10px;">|</span>
                <a href="/login">Connexion</a>
                <span style="color: #aaa; margin: 0 10px;">|</span>
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
