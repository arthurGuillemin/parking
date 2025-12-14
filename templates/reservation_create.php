<?php
$pageTitle = "Réserver - " . htmlspecialchars($parking->getName());
ob_start();
?>

<div class="container" style="max-width: 600px; margin-top: 40px;">
    <div class="card">
        <h2>Réserver une place</h2>
        <div class="mb-3">
            <strong>Parking :</strong> <?= htmlspecialchars($parking->getName()) ?><br>
            <span class="text-muted"><?= htmlspecialchars($parking->getAddress()) ?></span>
        </div>

        <form action="/reservation/create" method="POST">
            <input type="hidden" name="parkingId" value="<?= $parking->getParkingId() ?>">

            <div class="mb-3">
                <label for="start" class="form-label">Début de stationnement</label>
                <input type="datetime-local" class="form-control" id="start" name="start" required>
            </div>

            <div class="mb-3">
                <label for="end" class="form-label">Fin de stationnement</label>
                <input type="datetime-local" class="form-control" id="end" name="end" required>
            </div>

            <div class="alert alert-info">
                ℹ️ Le prix sera calculé en fonction de la durée réelle. <br>
                Une estimation sera affichée après confirmation.
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Confirmer la réservation</button>
                <a href="/parkings" class="btn btn-link text-center">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>