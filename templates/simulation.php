<?php
$pageTitle = "Simulation Entrée/Sortie";
ob_start();
?>

<div class="container" style="margin-top: 40px;">
    <h2>Simulation d'Accès Parking</h2>
    <p>Simulez votre arrivée ou votre départ d'un parking.</p>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- ENTRY COLUMN -->
        <div class="col-md-6">
            <div class="card">
                <h3>Entrer dans un Parking</h3>
                <p>Sélectionnez une réservation active pour aujourd'hui.</p>

                <?php if (empty($reservations)): ?>
                    <p class="text-muted">Aucune réservation trouvée pour entrer.</p>
                <?php else: ?>
                    <form action="/parking/enter" method="POST">
                        <div class="form-group">
                            <label>Réservation :</label>
                            <select name="reservation_id" class="form-control" required>
                                <?php foreach ($reservations as $res): ?>
                                    <?php
                                    $date = new DateTime($res->startDateTime);
                                    $formattedDate = $date->format('d/m à H:i');
                                    ?>
                                    <option value="<?= $res->id ?>">
                                        <?= htmlspecialchars($res->parkingName ?? 'Parking #' . $res->parkingId) ?> (Le
                                        <?= $formattedDate ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Hidden fields if needed, but ID is enough to find parking/user -->
                        <button type="submit" class="btn btn-success mt-2">Simuler l'entrée</button>
                    </form>
                <?php endif; ?>

                <hr>
                <h4>Ou entrée abonné</h4>
                <?php if (empty($subscriptions)): ?>
                    <p class="text-muted">Aucun abonnement actif.</p>
                <?php else: ?>
                    <form action="/parking/enter" method="POST">
                        <div class="form-group">
                            <label>Parking abonné :</label>
                            <select name="parking_id" class="form-control" required>
                                <?php foreach ($subscriptions as $sub): ?>
                                    <option value="<?= $sub->parkingId ?>">
                                        <?= htmlspecialchars($sub->parkingName ?? 'Parking #' . $sub->parkingId) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Entrer (abonné)</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- EXIT COLUMN -->
        <div class="col-md-6">
            <div class="card" style="background-color: #f8f9fa;">
                <h3>Sortir d'un parking</h3>
                <?php if (empty($activeSession)): ?>
                    <p class="text-muted">Vous n'êtes pas stationné actuellement.</p>
                <?php else: ?>
                    <div class="alert alert-info">
                        <strong>En cours :</strong>
                        <?= htmlspecialchars($activeSession->parkingName ?? 'Parking #' . $activeSession->getParkingId()) ?><br>
                        Entrée : Le <?= $activeSession->getEntryDateTime()->format('d/m à H:i') ?>
                    </div>
                    <form action="/parking/exit" method="POST">
                        <input type="hidden" name="parking_id" value="<?= $activeSession->getParkingId() ?>">
                        <button type="submit" class="btn btn-warning w-100">Simuler la sortie et payer</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>