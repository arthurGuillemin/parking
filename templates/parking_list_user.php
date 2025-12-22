<?php
$pageTitle = "Rechercher un parking";
ob_start();
?>

<div class="actions" style="display:flex; justify-content:space-between; align-items:center;">
    <h2>Parkings disponibles</h2>
    <form method="GET" action="/parkings" style="display:flex; gap:10px;">
        <input type="text" name="q" class="form-control" placeholder="Ville, adresse..."
            value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="margin-bottom:0; width:250px;">
        <button type="submit" class="btn btn-primary">Rechercher</button>
        <button type="button" class="btn btn-outline-secondary" onclick="geoLocate()">📍 Autour de moi</button>
    </form>
</div>

<script>
    function geoLocate() {
        if (!navigator.geolocation) {
            alert("La géolocalisation n'est pas supportée par votre navigateur.");
            return;
        }

        // Show loading state
        const btn = document.querySelector('button[onclick="geoLocate()"]');
        const originalText = btn.textContent;
        btn.textContent = '📍 Localisation...';
        btn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                window.location.href = `/parkings?lat=${lat}&lng=${lng}`;
            },
            (error) => {
                console.error(error);
                alert("Impossible de vous localiser. Vérifiez vos permissions.");
                btn.textContent = originalText;
                btn.disabled = false;
            }
        );
    }
</script>

<?php if (empty($parkings)): ?>
    <div class="empty-state">
        <p>Aucun parking trouvé pour cette recherche.</p>
        <a href="/parkings" class="btn btn-outline-primary">Voir tous les parkings</a>
    </div>
<?php else: ?>
    <div class="grid">
        <?php foreach ($parkings as $parking): ?>
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:start;">
                    <h3><?= htmlspecialchars($parking->getName()) ?></h3>
                    <?php if ($parking->isOpen24_7()): ?>
                        <span class="badge badge-open">24/7</span>
                    <?php else: ?>
                        <span class="badge badge-closed">Horaires</span>
                    <?php endif; ?>
                </div>

                <p>📍 <?= htmlspecialchars($parking->getAddress()) ?></p>
                <p>🚗 Capacité: <strong><?= $parking->getTotalCapacity() ?></strong> places</p>

                <div class="card-actions">
                    <a href="/parking/details?id=<?= $parking->getParkingId() ?>" class="btn btn-outline-primary btn-sm">Voir
                        détails</a>
                    <?php if (isset($_COOKIE['auth_token'])): ?>
                        <a href="/reservation?parkingId=<?= $parking->getParkingId() ?>" class="btn btn-primary btn-sm">Réserver</a>
                        <a href="/subscription?parkingId=<?= $parking->getParkingId() ?>"
                            class="btn btn-outline-secondary btn-sm">S'abonner</a>
                    <?php else: ?>
                        <a href="/login" class="btn btn-secondary btn-sm">Connexion pour réserver</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
