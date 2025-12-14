<?php
$pageTitle = "Mes Abonnements";
ob_start();
?>

<div class="container" style="margin-top: 40px;">
    <h2>Mes Abonnements</h2>

    <?php if (empty($subscriptions)): ?>
        <div class="alert alert-info">
            Vous n'avez aucun abonnement actif.
        </div>
        <a href="/parkings" class="btn btn-primary">Trouver un parking</a>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($subscriptions as $sub): ?>
                <div class="card">
                    <h3>Abonnement Parking #<?= htmlspecialchars($sub->parkingId) ?></h3>
                    <p>
                        <strong>Début :</strong> <?= htmlspecialchars($sub->startDate) ?><br>
                        <strong>Fin :</strong> <?= htmlspecialchars($sub->endDate ?? 'Indéfini') ?><br>
                        <strong>Prix :</strong> <?= htmlspecialchars($sub->monthlyPrice) ?> € / mois<br>
                        <strong>Statut :</strong>
                        <span class="badge <?= $sub->status === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= htmlspecialchars($sub->status) ?>
                        </span>
                    </p>
                    <?php if ($sub->status === 'active'): ?>
                        <button class="btn btn-danger btn-sm" onclick="cancelSubscription(<?= $sub->id ?>)">Résilier</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function cancelSubscription(id) {
        if (confirm('Voulez-vous vraiment résilier cet abonnement ?')) {
            fetch('/subscription/' + id, { method: 'DELETE' })
                .then(response => {
                    if (response.ok) location.reload();
                    else alert('Erreur lors de la résiliation');
                });
        }
    }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>