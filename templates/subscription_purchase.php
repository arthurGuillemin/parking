<?php
$pageTitle = "S'abonner - " . htmlspecialchars($parking->getName());
ob_start();
?>

<div class="container" style="max-width: 600px; margin-top: 40px;">
    <div class="card">
        <h2>Souscrire un abonnement</h2>
        <div class="mb-3">
            <strong>Parking :</strong> <?= htmlspecialchars($parking->getName()) ?><br>
            <span class="text-muted"><?= htmlspecialchars($parking->getAddress()) ?></span>
        </div>

        <?php if (empty($subscriptionTypes)): ?>
            <div class="alert alert-warning">
                Aucune formule d'abonnement n'est disponible pour ce parking actuellement.
            </div>
            <a href="/parkings" class="btn btn-outline-primary">Retour</a>
        <?php else: ?>
            <form action="/subscription/purchase" method="POST">
                <input type="hidden" name="parkingId" value="<?= $parking->getParkingId() ?>">

                <div class="mb-3">
                    <label class="form-label">Choisir une formule :</label>
                    <div class="grid" style="grid-template-columns: 1fr; gap: 10px;">
                        <?php foreach ($subscriptionTypes as $type): ?>
                            <label class="card"
                                style="flex-direction: row; align-items: center; cursor: pointer; border: 1px solid #ddd; padding: 15px;">
                                <input type="radio" name="typeId" value="<?= $type->id ?>" required style="margin-right: 15px;">
                                <div>
                                    <h4 style="margin:0;"><?= htmlspecialchars($type->name) ?></h4>
                                    <p style="margin:0; color:#666;">
                                        Prix <?= htmlspecialchars($type->name) ?> :
                                        <strong><?= number_format($type->monthlyPrice, 2, ',', ' ') ?> €</strong>
                                    </p>
                                    <?php if (!empty($type->description)): ?>
                                        <small class="text-muted"><?= htmlspecialchars($type->description) ?></small>
                                    <?php endif; ?>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="startDate" class="form-label"><strong>📅 Date de début de l'abonnement</strong></label>
                    <input type="date" class="form-control" id="startDate" name="startDate" value="<?= date('Y-m-d') ?>"
                        min="<?= date('Y-m-d') ?>" required>
                    <small class="text-muted" style="display:block; margin-top:8px;">
                        Choisissez la date à laquelle votre abonnement commencera.
                        Par défaut, c'est aujourd'hui.
                    </small>
                    <div id="datePreview"
                        style="margin-top:10px; padding:10px; background:#e8f4fd; border-radius:6px; display:none;">
                        <strong>Première période :</strong>
                        <span id="periodStart"></span> → <span id="periodEnd"></span>
                    </div>
                </div>

                <script>
                    document.getElementById('startDate').addEventListener('change', function () {
                        const startDate = new Date(this.value);
                        if (isNaN(startDate)) return;

                        const endDate = new Date(startDate);
                        endDate.setMonth(endDate.getMonth() + 1);

                        const formatDate = (d) => d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });

                        document.getElementById('periodStart').textContent = formatDate(startDate);
                        document.getElementById('periodEnd').textContent = formatDate(endDate);
                        document.getElementById('datePreview').style.display = 'block';
                    });
                    // Trigger on load
                    document.getElementById('startDate').dispatchEvent(new Event('change'));
                </script>

                <div class="alert alert-info">
                    ℹ️ L'abonnement est <strong>mensuel</strong> et se renouvelle automatiquement chaque mois (tacite
                    reconduction).
                    Vous pouvez le résilier à tout moment depuis votre espace "Mes Abonnements".
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Confirmer l'abonnement</button>
                    <a href="/parkings" class="btn btn-link text-center">Annuler</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
