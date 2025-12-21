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
            <span style="color: #aaa; margin: 0 10px;">|</span>
            <?php if (isset($_COOKIE['auth_token']) || isset($_SESSION['user_id'])): ?>
                <?php
                // Fetch user logic for header display
                $headerUser = null;
                try {
                    // Quick dependency instantiation for layout (Vanilla PHP style)
                    if (isset($_COOKIE['auth_token'])) {
                        $jwtService = new \App\Domain\Service\JwtService();
                        $userId = $jwtService->validateToken($_COOKIE['auth_token']);
                        if ($userId) {
                            $userRepo = new \App\Infrastructure\Persistence\Sql\SqlUserRepository();
                            $headerUser = $userRepo->findById($userId);
                        }
                    }
                } catch (\Exception $e) {
                    // Silent fail for header display
                }
                ?>

                <?php if ($headerUser): ?>
                    <span style="color: #eee; margin-right: 15px; font-weight: bold;">
                        Bonjour, <?= htmlspecialchars($headerUser->getFirstName() . ' ' . $headerUser->getLastName()) ?>
                    </span>
                    <span style="color: #aaa; margin-right: 15px;">|</span>
                <?php endif; ?>

                <a href="/dashboard">Tableau de bord</a>
                <span style="color: #aaa; margin: 0 10px;">|</span>
                <a href="/simulation">Simulation</a>
                <span style="color: #aaa; margin: 0 10px;">|</span>
                <form action="/logout" method="POST" style="display:inline;">
                    <button type="submit"
                        style="background:none; border:none; color:white; cursor:pointer; text-decoration:underline;">
                        Déconnexion
                    </button>
                </form>
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