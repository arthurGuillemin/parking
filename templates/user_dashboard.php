<?php
$pageTitle = "Mon Tableau de Bord";
ob_start();
?>

<div class="container" style="margin-top: 40px;">
    <h2>Mon Tableau de Bord</h2>

    <ul class="nav nav-tabs" id="dashboardTabs" role="tablist"
        style="margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-left: 0; list-style: none; display: flex;">
        <li class="nav-item" style="margin-right: 10px;">
            <button class="nav-link active btn btn-link" onclick="openTab(event, 'reservations')"
                style="text-decoration:none; font-weight:bold;">Réservations</button>
        </li>
        <li class="nav-item" style="margin-right: 10px;">
            <button class="nav-link btn btn-link" onclick="openTab(event, 'sessions')"
                style="text-decoration:none;">Historique Stationnement</button>
        </li>
        <li class="nav-item" style="margin-right: 10px;">
            <button class="nav-link btn btn-link" onclick="openTab(event, 'subscriptions')"
                style="text-decoration:none;">Abonnements</button>
        </li>
        <li class="nav-item">
            <button class="nav-link btn btn-link" onclick="openTab(event, 'invoices')"
                style="text-decoration:none;">Factures</button>
        </li>
    </ul>

    <!-- Reservations Tab -->
    <div id="reservations" class="tab-content" style="display: block;">
        <h3>Mes Réservations</h3>
        <?php if (empty($reservations)): ?>
            <p class="text-muted">Aucune réservation.</p>
            <a href="/parkings" class="btn btn-primary btn-sm">Réserver</a>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #ddd; text-align: left;">
                            <th style="padding: 10px;">Parking (ID)</th>
                            <th style="padding: 10px;">Début</th>
                            <th style="padding: 10px;">Fin</th>
                            <th style="padding: 10px;">Statut</th>
                            <th style="padding: 10px;">Prix Est.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $res): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px;">#<?= htmlspecialchars($res->parkingId) ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($res->start) ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($res->end) ?></td>
                                <td style="padding: 10px;">
                                    <span class="badge <?= $res->status === 'confirmed' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= htmlspecialchars($res->status) ?>
                                    </span>
                                </td>
                                <td style="padding: 10px;"><?= number_format($res->price, 2) ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sessions Tab -->
    <div id="sessions" class="tab-content" style="display: none;">
        <h3>Historique de Stationnement</h3>
        <?php if (empty($sessions)): ?>
            <p class="text-muted">Aucune session enregistrée.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #ddd; text-align: left;">
                            <th style="padding: 10px;">Parking (ID)</th>
                            <th style="padding: 10px;">Entrée</th>
                            <th style="padding: 10px;">Sortie</th>
                            <th style="padding: 10px;">Plaque</th>
                            <th style="padding: 10px;">Prix Payé</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px;">#<?= htmlspecialchars($session->parkingId) ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($session->entryTime) ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($session->exitTime ?? 'En cours') ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($session->vehiclePlate) ?></td>
                                <td style="padding: 10px;">
                                    <?php if ($session->pricePaid !== null): ?>
                                        <?= number_format($session->pricePaid, 2) ?> €
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Subscriptions Tab -->
    <div id="subscriptions" class="tab-content" style="display: none;">
        <h3>Mes Abonnements</h3>
        <?php if (empty($subscriptions)): ?>
            <p class="text-muted">Aucun abonnement actif.</p>
            <a href="/parkings" class="btn btn-primary btn-sm">S'abonner</a>
        <?php else: ?>
            <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                <?php foreach ($subscriptions as $sub): ?>
                    <div class="card">
                        <h4>Parking #<?= htmlspecialchars($sub->parkingId) ?></h4>
                        <p class="mb-2"><strong>Statut :</strong> <?= htmlspecialchars($sub->status) ?></p>
                        <p class="mb-2"><strong>Validité :</strong> <?= htmlspecialchars($sub->startDate) ?> ->
                            <?= htmlspecialchars($sub->endDate ?? 'Indéfini') ?>
                        </p>
                        <a href="/subscription" class="btn btn-outline-secondary btn-sm">Gérer</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Invoices Tab -->
    <div id="invoices" class="tab-content" style="display: none;">
        <h3>Mes Factures</h3>
        <?php if (empty($invoices)): ?>
            <p class="text-muted">Aucune facture disponible.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #ddd; text-align: left;">
                            <th style="padding: 10px;">Numéro</th>
                            <th style="padding: 10px;">Date</th>
                            <th style="padding: 10px;">Type</th>
                            <th style="padding: 10px;">Montant TTC</th>
                            <th style="padding: 10px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px;"><?= htmlspecialchars($invoice->id) ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($invoice->issueDate) ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($invoice->type) ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($invoice->amountTtc) ?> €</td>
                                <td style="padding: 10px;">
                                    <button class="btn btn-sm btn-secondary" disabled>Télécharger (WIP)</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("nav-link");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
            tablinks[i].style.fontWeight = "normal";
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
        evt.currentTarget.style.fontWeight = "bold";
    }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>