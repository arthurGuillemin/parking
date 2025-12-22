<?php
$pageTitle = htmlspecialchars($parking->getName());
ob_start();
?>

<div style="margin-bottom: 20px;">
    <a href="/parkings" class="btn btn-outline-secondary">← Retour à la liste</a>
</div>

<!-- Informations Générales -->
<div class="card" style="margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: start;">
        <div>
            <h2 style="margin: 0 0 10px 0;"><?= htmlspecialchars($parking->getName()) ?></h2>
            <p style="margin: 5px 0; color: #666;">📍 <?= htmlspecialchars($parking->getAddress()) ?></p>
        </div>
        <?php if ($parking->isOpen24_7()): ?>
            <span class="badge badge-success" style="font-size: 1.1em;">24/7</span>
        <?php else: ?>
            <span class="badge badge-info" style="font-size: 1.1em;">Horaires</span>
        <?php endif; ?>
    </div>

    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
        <div>
            <div style="font-size: 0.9em; color: #666;">Capacité totale</div>
            <div style="font-size: 1.5em; font-weight: bold;"><?= $parking->getTotalCapacity() ?> places</div>
        </div>
        <div>
            <div style="font-size: 0.9em; color: #666;">Places disponibles</div>
            <div style="font-size: 1.5em; font-weight: bold; color: #28a745;" id="availableSpots">
                <span class="spinner">⏳</span>
            </div>
        </div>
    </div>
</div>

<!-- Tarification -->
<div class="card" style="margin-bottom: 20px;">
    <h3>💰 Tarification</h3>
    <?php if (empty($pricingRules)): ?>
        <p style="color: #666; font-style: italic;">Aucune règle de tarification définie.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Durée</th>
                    <th>Prix par tranche</th>
                    <th>Tranche</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pricingRules as $rule): ?>
                    <tr>
                        <td>
                            <?= $rule->getStartDurationMinute() ?> min
                            <?php if ($rule->getEndDurationMinute()): ?>
                                - <?= $rule->getEndDurationMinute() ?> min
                            <?php else: ?>
                                et plus
                            <?php endif; ?>
                        </td>
                        <td style="font-weight: bold;"><?= number_format($rule->getPricePerSlice(), 2) ?> €</td>
                        <td><?= $rule->getSliceInMinutes() ?> min</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Horaires d'Ouverture -->
<?php if (!$parking->isOpen24_7()): ?>
    <div class="card" style="margin-bottom: 20px;">
        <h3>🕐 Horaires d'Ouverture</h3>
        <?php if (empty($openingHours)): ?>
            <p style="color: #666; font-style: italic;">Horaires non définis.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Jour</th>
                        <th>Ouverture</th>
                        <th>Fermeture</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $daysMap = [
                        1 => 'Lundi',
                        2 => 'Mardi',
                        3 => 'Mercredi',
                        4 => 'Jeudi',
                        5 => 'Vendredi',
                        6 => 'Samedi',
                        7 => 'Dimanche'
                    ];
                    foreach ($openingHours as $hour):
                        $dayStart = $daysMap[$hour->getWeekdayStart()] ?? 'Jour ' . $hour->getWeekdayStart();
                        $dayEnd = $hour->getWeekdayEnd() !== $hour->getWeekdayStart()
                            ? ' - ' . ($daysMap[$hour->getWeekdayEnd()] ?? 'Jour ' . $hour->getWeekdayEnd())
                            : '';
                        ?>
                        <tr>
                            <td><?= $dayStart . $dayEnd ?></td>
                            <td><?= $hour->getOpeningTime()->format('H:i') ?></td>
                            <td><?= $hour->getClosingTime()->format('H:i') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Types d'Abonnements -->
<div class="card" style="margin-bottom: 20px;">
    <h3>📅 Abonnements Disponibles</h3>
    <?php if (empty($subscriptionTypes)): ?>
        <p style="color: #666; font-style: italic;">Aucun abonnement disponible pour ce parking.</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($subscriptionTypes as $type): ?>
                <div class="card" style="background: #f8f9fa;">
                    <h4 style="margin: 0 0 10px 0;"><?= htmlspecialchars($type->getName()) ?></h4>
                    <div style="font-weight:bold; font-size:1.1em; color:#28a745; margin-bottom:8px;">
                        <?= number_format($type->getMonthlyPrice(), 2, ',', ' ') ?> € <span
                            style="font-size:0.8em; color:#666;">/mois</span>
                    </div>
                    <p style="color: #666; margin: 0 0 15px 0;">
                        <?= htmlspecialchars($type->getDescription() ?? 'Abonnement flexible') ?>
                    </p>
                    <?php if (isset($_COOKIE['auth_token'])): ?>
                        <a href="/subscription?parkingId=<?= $parking->getParkingId() ?>&typeId=<?= $type->getSubscriptionTypeId() ?>"
                            class="btn btn-primary btn-sm">S'abonner</a>
                    <?php else: ?>
                        <a href="/login" class="btn btn-secondary btn-sm">Connexion requise</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Actions Rapides -->
<div style="display: flex; gap: 10px; justify-content: center; margin-top: 30px;">
    <?php if (isset($_COOKIE['auth_token'])): ?>
        <a href="/reservation?parkingId=<?= $parking->getParkingId() ?>" class="btn btn-primary"
            style="font-size: 1.1em;">🚗 Réserver maintenant</a>
        <a href="/subscription?parkingId=<?= $parking->getParkingId() ?>" class="btn btn-outline-secondary"
            style="font-size: 1.1em;">📅 S'abonner</a>
    <?php else: ?>
        <a href="/login" class="btn btn-primary" style="font-size: 1.1em;">🔐 Connexion pour réserver</a>
    <?php endif; ?>
</div>

<script>
    // Load available spots
    async function loadAvailableSpots() {
        try {
            const response = await fetch('/parking/available-spots?parkingId=<?= $parking->getParkingId() ?>&at=' + new Date().toISOString());
            const data = await response.json();
            document.getElementById('availableSpots').innerHTML = data.availableSpots + ' <span style="font-size: 0.7em; color: #666;">places</span>';
        } catch (error) {
            console.error('Error loading available spots:', error);
            // DEBUG: Show detailed error in UI to help debugging
            let debugInfo = error.message;
            if (error instanceof SyntaxError) {
                // It means response was not JSON. Usually HTML.
                debugInfo += " (Likely HTML response)";
            }
            document.getElementById('availableSpots').innerHTML =
                '<div style="font-size:0.5em; color:red; line-height:1.2">' +
                '<strong>JS Error:</strong> ' + debugInfo +
                '<br><strong>Check console for details</strong>' +
                '</div>';

            // Fetch again to get text body for debugging
            fetch('/parking/available-spots?parkingId=<?= $parking->getParkingId() ?>&at=' + new Date().toISOString())
                .then(res => res.text())
                .then(text => {
                    console.log("RAW RESPONSE:", text);
                    // Append snippet to UI
                    let snippet = text.substring(0, 100).replace(/</g, '&lt;');
                    document.getElementById('availableSpots').innerHTML +=
                        '<div style="font-size:0.4em; color:#666; margin-top:5px; word-break:break-all">' +
                        '<strong>Response Start:</strong> ' + snippet + '...' +
                        '</div>';
                });
        }
    }

    loadAvailableSpots();
    // Refresh every 30 seconds
    setInterval(loadAvailableSpots, 30000);
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>