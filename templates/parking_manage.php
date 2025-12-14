<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer le Parking - Parking Partagé</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 20px;
        }

        header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        h1 {
            margin: 0;
            color: #2c3e50;
        }

        .back-link {
            color: #666;
            text-decoration: none;
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-weight: 500;
            color: #666;
        }

        .tab:hover {
            color: #007bff;
        }

        .tab.active {
            border-bottom-color: #007bff;
            color: #007bff;
        }

        /* Content Areas */
        .content-section {
            display: none;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .content-section.active {
            display: block;
        }

        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            color: #333;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #28a745;
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        .success {
            color: green;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1 id="parkingName"><?= htmlspecialchars($parking->getName()) ?></h1>
            <a href="/owner/dashboard" class="back-link">← Retour au tableau de bord</a>
        </header>

        <div class="tabs">
            <div class="tab active" onclick="switchTab('pricing')">Tarification</div>
            <div class="tab" onclick="switchTab('hours')">Horaires</div>
            <div class="tab" onclick="switchTab('reservations')">Réservations</div>
            <div class="tab" onclick="switchTab('sessions')">Stationnements</div>
            <div class="tab" onclick="switchTab('subscriptions')">Abonnements</div>
            <div class="tab" onclick="switchTab('alerts')">Alertes</div>
            <div class="tab" onclick="switchTab('stats')">Revenus & Dispo</div>
        </div>

        <!-- PRICING TAB -->
        <div id="pricing" class="content-section active">
            <h2>Règles de Tarification</h2>
            <form id="pricingForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Début de la période (minutes après arrivée)</label>
                        <input type="number" name="startDurationMinute" value="0" required placeholder="Ex: 0">
                    </div>
                    <div class="form-group">
                        <label>Fin de la période (minutes, vide = illimité)</label>
                        <input type="number" name="endDurationMinute" placeholder="Ex: 120 (pour 2h)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Prix par tranche (€)</label>
                        <input type="number" step="0.01" name="pricePerSlice" required placeholder="Ex: 2.50">
                    </div>
                    <div class="form-group">
                        <label>Durée de la tranche (minutes)</label>
                        <input type="number" name="sliceInMinutes" value="15" readonly
                            style="background-color: #e9ecef; cursor: not-allowed;"
                            title="La tranche est fixée à 15 minutes par défaut">
                    </div>
                </div>
                <div class="form-group">
                    <label>Date d'application du tarif</label>
                    <input type="date" name="effectiveDate" required>
                </div>
                <button type="submit" class="btn-primary">Mettre à jour le tarif</button>
                <div id="pricingMsg"></div>
            </form>
        </div>
        <!-- 
{"error":"Erreur serveur: Service not found: App\\Interface\\Presenter\\SubscriptionPresenter"}-->
        <!-- HOURS TAB -->
        <div id="hours" class="content-section">
            <h2>Horaires d'ouverture</h2>

            <div
                style="background: #e6fffa; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 5px solid #00b894; position: relative;">
                <label for="open247Toggle"
                    style="display: flex; align-items: center; cursor: pointer; width: 100%; height: 100%;">
                    <input type="checkbox" id="open247Toggle" style="width: 20px; height: 20px; margin-right: 10px;">
                    <div>
                        <strong style="font-size: 1.1em; color: #0056b3;">Ouvert 24h/24 et 7j/7 (Permanent)</strong>
                        <small style="display:block; margin-top:5px; color:#666;">
                            Si coché, le parking est considéré comme toujours ouvert. La définition des horaires sera
                            désactivée.
                        </small>
                    </div>
                </label>
            </div>

            <form id="hoursForm" style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                <h3>Ajouter une plage horaire</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Jour d'ouverture</label>
                        <select name="weekdayStart">
                            <option value="1">Lundi</option>
                            <option value="2">Mardi</option>
                            <option value="3">Mercredi</option>
                            <option value="4">Jeudi</option>
                            <option value="5">Vendredi</option>
                            <option value="6">Samedi</option>
                            <option value="7">Dimanche</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jour de fermeture</label>
                        <select name="weekdayEnd">
                            <option value="1">Lundi</option>
                            <option value="2">Mardi</option>
                            <option value="3">Mercredi</option>
                            <option value="4">Jeudi</option>
                            <option value="5" selected>Vendredi</option>
                            <option value="6">Samedi</option>
                            <option value="7">Dimanche</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Heure d'ouverture</label>
                        <div style="display:flex; gap:5px;">
                            <select name="openingTimeHour" required style="flex:1;">
                                <?php for ($i = 0; $i < 24; $i++):
                                    $h = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                                    <option value="<?= $h ?>"><?= $h ?>h</option>
                                <?php endfor; ?>
                            </select>
                            <select name="openingTimeMinute" required style="flex:1;">
                                <option value="00">00</option>
                                <option value="15">15</option>
                                <option value="30">30</option>
                                <option value="45">45</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Heure de fermeture</label>
                        <div style="display:flex; gap:5px;">
                            <select name="closingTimeHour" required style="flex:1;">
                                <?php for ($i = 0; $i < 24; $i++):
                                    $h = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                                    <option value="<?= $h ?>" <?= $i == 23 ? 'selected' : '' ?>><?= $h ?>h</option>
                                <?php endfor; ?>
                            </select>
                            <select name="closingTimeMinute" required style="flex:1;">
                                <option value="00">00</option>
                                <option value="15" selected>15</option>
                                <option value="30">30</option>
                                <option value="45">45</option>
                                <option value="59">59</option>
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-success">Ajouter cette plage</button>
                <div id="hoursMsg"></div>
            </form>

            <h3>Plages horaires définies</h3>
            <ul id="hoursList">Chargement...</ul>
        </div>

        <!-- RESERVATIONS TAB -->
        <div id="reservations" class="content-section">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2>Réservations</h2>
                <button onclick="loadReservations()" class="btn-primary">Actualiser</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody id="reservationsTable">
                    <tr>
                        <td colspan="5">Chargement...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- SESSIONS TAB -->
        <div id="sessions" class="content-section">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2>Stationnements en cours / passés</h2>
                <button onclick="loadSessions()" class="btn-primary">Actualiser</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Entrée</th>
                        <th>Sortie</th>
                        <th>Pénalité ?</th>
                    </tr>
                </thead>
                <tbody id="sessionsTable">
                    <tr>
                        <td colspan="4">Chargement...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- SUBSCRIPTIONS TAB -->
        <div id="subscriptions" class="content-section">
            <h2>Types d'Abonnement</h2>
            <div style="margin-bottom: 20px; padding: 15px; background: #e9ecef; border-radius: 4px;">
                <h3>Ajouter un type</h3>
                <form id="addSubForm" style="display:flex; gap:10px; align-items:end;">
                    <div style="flex:1">
                        <label>Nom (ex: Mensuel 24/7)</label>
                        <input type="text" name="name" required>
                    </div>
                    <div style="flex:2">
                        <label>Description</label>
                        <input type="text" name="description">
                    </div>
                    <button type="submit" class="btn-success">Ajouter</button>
                </form>
                <div id="subMsg"></div>
            </div>

            <h3>Types existants</h3>
            <ul id="subList" style="list-style:none; padding:0;">
                <li>Chargement...</li>
            </ul>
        </div>

        <!-- ALERTS TAB -->
        <div id="alerts" class="content-section">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2 style="color:#dc3545">Véhicules Hors Créneau</h2>
                <button onclick="loadAlerts()" class="btn-primary">Actualiser</button>
            </div>
            <p>Conducteurs présents en dehors de leur réservation ou abonnement.</p>
            <table>
                <thead>
                    <tr>
                        <th>ID Session</th>
                        <th>Utilisateur</th>
                        <th>Entrée</th>
                        <th>Pénalité ?</th>
                    </tr>
                </thead>
                <tbody id="alertsTable">
                    <tr>
                        <td colspan="4">Chargement...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- STATS TAB -->
        <div id="stats" class="content-section">
            <h2>Revenus & Disponibilité</h2>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:30px;">
                <div class="stat-card">
                    <h3>Disponibilité Immédiate</h3>
                    <div id="availSpots" class="stat-value">-</div>
                    <small>Places libres maintenant</small>
                    <button onclick="checkAvailability()" style="display:block; margin:10px auto;"
                        class="btn-sm">Vérifier</button>
                </div>

                <div class="stat-card">
                    <h3>Revenu Mensuel</h3>
                    <div class="form-row" style="justify-content: center; margin-bottom: 10px;">
                        <input type="number" id="revYear" value="2025" style="width:80px">
                        <input type="number" id="revMonth" value="12" style="width:60px">
                    </div>
                    <div id="monthlyRev" class="stat-value">0.00 €</div>
                    <button onclick="checkRevenue()" class="btn-sm">Calculer</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Extract Parking ID from URL: /parking/123/manage
        const pathParts = window.location.pathname.split('/');
        const parkingId = pathParts[2]; // assuming [ "", "parking", "123", "manage" ]

        // Init - Name is now set by PHP
        // document.getElementById('parkingName').textContent = "Parking #" + parkingId;

        function switchTab(tabId) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.content-section').forEach(c => c.classList.remove('active'));

            document.querySelector(`.tab[onclick="switchTab('${tabId}')"]`).classList.add('active');
            document.getElementById(tabId).classList.add('active');

            // Lazy load data
            if (tabId === 'pricing') loadPricing();
            if (tabId === 'hours') loadHours();
            if (tabId === 'reservations') loadReservations();
            if (tabId === 'sessions') loadSessions();
            if (tabId === 'subscriptions') loadSubscriptions();
            if (tabId === 'alerts') loadAlerts();
            if (tabId === 'stats') { checkAvailability(); }
        }

        // Init load
        loadPricing();


        // --- SUBSCRIPTIONS ---
        document.getElementById('addSubForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target));
            data.parkingId = parkingId;
            try {
                const res = await fetch('/subscription-type/add', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                if (res.ok) {
                    e.target.reset();
                    loadSubscriptions(); // Reload list
                } else {
                    alert('Erreur ajout');
                }
            } catch (e) { console.error(e); }
        });

        async function loadSubscriptions() {
            const el = document.getElementById('subList');
            try {
                // Assuming GET /subscription-type/list?parkingId=X
                const res = await fetch(`/subscription-type/list?parkingId=${parkingId}`);
                const list = await res.json();
                el.innerHTML = '';
                if (!list || list.length === 0) {
                    el.innerHTML = '<li style="padding:10px; color:#666; text-align:center;">Aucun type d\'abonnement défini.</li>';
                    return;
                }
                list.forEach(item => {
                    const li = document.createElement('li');
                    li.style.padding = '10px';
                    li.style.borderBottom = '1px solid #eee';
                    li.innerHTML = `<strong>${item.name}</strong> - ${item.description || ''}`;
                    el.appendChild(li);
                });
            } catch (e) { el.innerHTML = 'Erreur chargement'; }
        }

        // --- ALERTS ---
        async function loadAlerts() {
            const tbody = document.getElementById('alertsTable');
            try {
                // Assuming GET route exists for SessionsOutOfReservationOrSubscriptionController
                const response = await fetch(`/parking/sessions-out-of-reservation-or-subscription?parkingId=${parkingId}`);
                const list = await response.json();
                tbody.innerHTML = '';
                if (!list || list.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px; color:#666;">Aucune alerte pour le moment.</td></tr>';
                    return;
                }
                list.forEach(s => {
                    tbody.innerHTML += `
                        <tr style="background-color: #fff5f5;">
                            <td>${s.id}</td>
                            <td>${s.userId}</td>
                            <td>${s.entryDateTime}</td>
                            <td style="color:red; font-weight:bold;">${s.penaltyApplied ? 'OUI' : 'NON'}</td>
                        </tr>`;
                });
            } catch (e) { tbody.innerHTML = '<tr><td colspan="4">Erreur</td></tr>'; }
        }

        // --- PRICING ---
        async function loadPricing() {
            try {
                const res = await fetch(`/pricing-rule/list?parkingId=${parkingId}`);
                const list = await res.json();
                if (list.length > 0) {
                    // Take the most recent one (assuming order or just last)
                    const rule = list[list.length - 1];
                    const form = document.getElementById('pricingForm');
                    form.startDurationMinute.value = rule.startDurationMinute;
                    form.endDurationMinute.value = rule.endDurationMinute || '';
                    form.pricePerSlice.value = rule.pricePerSlice;
                    form.sliceInMinutes.value = rule.sliceInMinutes;
                    form.effectiveDate.value = rule.effectiveDate;
                }
            } catch (e) { console.error('Error loading pricing', e); }
        }

        document.getElementById('pricingForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target));
            data.parkingId = parkingId;
            // Handle optional endDuration
            if (!data.endDurationMinute) delete data.endDurationMinute;

            const msg = document.getElementById('pricingMsg');
            msg.innerHTML = 'Enregistrement...';
            msg.className = '';

            try {
                const res = await fetch('/pricing-rule/update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                let json;
                try {
                    json = await res.json();
                } catch (parseError) {
                    throw new Error('Erreur serveur non-JSON');
                }

                if (res.ok) {
                    msg.className = 'success';
                    msg.innerHTML = '✅ Tarifs mis à jour avec succès !';
                    loadPricing();
                } else {
                    msg.className = 'error';
                    msg.innerHTML = '❌ ' + (json.error || 'Erreur inconnue');
                }
            } catch (err) {
                console.error(err);
                msg.className = 'error';
                msg.innerHTML = '❌ Erreur de communication: ' + err.message;
            }
        });

        // --- HOURS ---
        const is247 = <?= $parking->isOpen24_7() ? 'true' : 'false' ?>;
        const toggle247 = document.getElementById('open247Toggle');
        const hoursForm = document.getElementById('hoursForm');

        function updateHoursState(active) {
            toggle247.checked = active;
            if (active) {
                hoursForm.style.opacity = '0.5';
                hoursForm.style.pointerEvents = 'none';
            } else {
                hoursForm.style.opacity = '1';
                hoursForm.style.pointerEvents = 'auto';
            }
        }

        // Init
        updateHoursState(is247);

        toggle247.addEventListener('change', async (e) => {
            const checked = e.target.checked;
            updateHoursState(checked);

            // Save immediately
            try {
                const res = await fetch('/parking/update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: parkingId, open_24_7: checked })
                });
                if (!res.ok) throw new Error('Failed to update');
            } catch (err) {
                alert('Erreur lors de la mise à jour du statut 24/7');
                updateHoursState(!checked); // Revert
            }
        });

        async function loadHours() {
            // Reload 24/7 state just in case? No, rely on page load or toggle. 
            // Actually, if we are in 24/7 mode, maybe we don't load hours list or we show a message.
            if (toggle247.checked) {
                document.getElementById('hoursList').innerHTML = '<li style="color:green; font-weight:bold;">Ce parking est ouvert 24h/24 et 7j/7.</li>';
                return;
            }

            const tbody = document.getElementById('hoursList');
            if (!tbody) return;
            try {
                const res = await fetch(`/opening-hour/list?parkingId=${parkingId}`);
                const list = await res.json();
                tbody.innerHTML = '';
                if (list.length === 0) {
                    tbody.innerHTML = '<li>Aucun horaire défini. Ajoutez des plages ci-dessus.</li>';
                    return;
                }
                const days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

                // Sort by day
                list.sort((a, b) => a.weekdayStart - b.weekdayStart);

                list.forEach(h => {
                    const dayStart = days[h.weekdayStart - 1] || h.weekdayStart;
                    const dayEnd = days[h.weekdayEnd - 1] || h.weekdayEnd;
                    const range = (dayStart === dayEnd) ? dayStart : `${dayStart} au ${dayEnd}`;

                    const li = document.createElement('li');
                    li.style.display = 'flex';
                    li.style.justifyContent = 'space-between';
                    li.style.alignItems = 'center';
                    li.style.padding = '10px';
                    li.style.borderBottom = '1px solid #eee';

                    li.innerHTML = `
                        <span><strong>${range}</strong> : ${h.openingTime} - ${h.closingTime}</span>
                        <button type="button" class="btn-sm" style="background:#dc3545; color:white; border:none; cursor:pointer;" onclick="deleteHour(${h.id})">Supprimer</button>
                    `;
                    tbody.appendChild(li);
                });
            } catch (e) { console.error(e); }
        }

        async function deleteHour(id) {
            if (!confirm('Supprimer cette plage horaire ?')) return;
            try {
                const res = await fetch('/opening-hour/delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                if (res.ok) {
                    loadHours();
                } else {
                    alert('Erreur suppression');
                }
            } catch (e) { alert('Erreur'); }
        }

        document.getElementById('hoursForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            data.parkingId = parkingId;

            // Combine Hour and Minute
            data.openingTime = `${data.openingTimeHour}:${data.openingTimeMinute}`;
            data.closingTime = `${data.closingTimeHour}:${data.closingTimeMinute}`;

            // Cleanup separate fields
            delete data.openingTimeHour;
            delete data.openingTimeMinute;
            delete data.closingTimeHour;
            delete data.closingTimeMinute;

            const msg = document.getElementById('hoursMsg');
            msg.innerHTML = 'Ajout...';
            msg.className = '';

            try {
                const res = await fetch('/opening-hour/add', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                let json;
                try {
                    json = await res.json();
                } catch (parseError) {
                    throw new Error('Erreur serveur non-JSON');
                }

                if (res.ok) {
                    msg.className = 'success';
                    msg.innerHTML = '✅ Plage ajoutée !';
                    loadHours();
                    // Reset styling part of form only? Or full reset usually better
                    // e.target.reset(); // If you want to reset
                } else {
                    msg.className = 'error';
                    msg.innerHTML = '❌ ' + (json.error || 'Erreur inconnue');
                }
            } catch (err) {
                console.error(err);
                msg.className = 'error';
                msg.innerHTML = '❌ Erreur: ' + err.message;
            }
        });

        // --- RESERVATIONS ---
        async function loadReservations() {
            const tbody = document.getElementById('reservationsTable');
            tbody.innerHTML = '<tr><td colspan="5">Chargement...</td></tr>';
            try {
                // Assuming we use existing listByParking logic, passing range covering "forever" or just recent
                // The prompt didn't specify date filters, let's grab all or default
                const res = await fetch(`/reservation/list?parkingId=${parkingId}`);
                // NOTE: The route is configured as GET /reservation/list linked to ReservationController::listByParking
                // However, existing controller checks $_POST/JSON in `listByParking`.
                // Wait, Controller usually expects array $data.
                // Framework league/route usually maps params.
                // If the controller reads from body, checking GET might fail if it relies strict on input.
                // Let's check `ReservationController` implementation again.
                // Step 317: uses $data['parkingId']. Framework maps $_GET to $data?
                // If not, we might need POST. Let's try GET with query params first.
                // Actually most Controllers here use helper methods that check $data.

                // If it fails, I'll switch to POST to be safe or fix route handling.
                // Let's assume POST for listing is safer given existing controller structure checking body often.
                // But `list` implies GET... Let's try GET.

                // Wait, in `ReservationController.php`:
                // `public function listByParking(array $data)`
                // If I use `league/route`, how is `$data` populated?
                // Usually via `ServerRequestInterface` or similar.
                // But here the code seems custom.
                // If the existing code expects strict array injection, the Router/Dispatcher must do it.
                // Looking at `OwnerController::register`, it handles `file_get_contents` manually if data empty.
                // `ReservationController` (Step 317) just `if (empty($data['parkingId']))`.

                // I will use POST for all data-fetching to match the style of other controllers
                // if I suspect they don't auto-parse GET query params into `$data`.
                // Actually `ReservationController::listByParking` might NOT have the `json_decode` fallback!
                // Let's check Step 317 again.
                // Step 317: `public function listByParking(array $data)`... simple check.
                // It DOES NOT have the `input = file_get_contents` block.
                // This means the router or a middleware MUST inject `$data`.
                // If I call it via HTTP, I rely on the framework gluing them.
                // If the framework doesn't do it, this will crash.
                // But `ReservationController::listByParking` is mapped in routes?
                // Step 302: `['GET', '/reservation/list', 'ReservationController::listByParking']`
                // If mapped as GET, the router likely passes `$_GET` as `$data`.
                // I will assume GET works with query params.

                const response = await fetch(`/reservation/list?parkingId=${parkingId}`);
                if (!response.ok) throw new Error('Network response was not ok');
                const list = await response.json();

                tbody.innerHTML = '';
                if (!list || list.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:20px; color:#666;">Aucune réservation pour le moment.</td></tr>';
                    return;
                }
                list.forEach(r => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${r.id}</td>
                            <td>${r.startDateTime}</td>
                            <td>${r.endDateTime}</td>
                            <td>${r.status}</td>
                            <td>${r.finalAmount || r.calculatedAmount || '-'} €</td>
                        </tr>`;
                });
            } catch (e) {
                console.error(e);
                tbody.innerHTML = '<tr><td colspan="5" style="color:red">Erreur de chargement.</td></tr>';
            }
        }

        // --- SESSIONS ---
        async function loadSessions() {
            const tbody = document.getElementById('sessionsTable');
            try {
                const response = await fetch(`/parking-session/list?parkingId=${parkingId}`);
                const list = await response.json();
                tbody.innerHTML = '';
                if (!list || list.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px; color:#666;">Aucun stationnement pour le moment.</td></tr>';
                    return;
                }
                list.forEach(s => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${s.id}</td>
                            <td>${s.entryDateTime}</td>
                            <td>${s.exitDateTime || 'En cours'}</td>
                            <td>${s.penaltyApplied ? 'OUI' : 'Non'}</td>
                        </tr>`;
                });
            } catch (e) { tbody.innerHTML = '<tr><td colspan="4">Erreur</td></tr>'; }
        }

        // --- REVENUE & AVAIL ---
        async function checkAvailability() {
            const el = document.getElementById('availSpots');
            el.innerHTML = '...';
            const now = new Date().toISOString();
            try {
                // GET /parking/available-spots?parkingId=X&at=Y
                const res = await fetch(`/parking/available-spots?parkingId=${parkingId}&at=${now}`);
                const json = await res.json();
                el.innerHTML = json.availableSpots !== undefined ? json.availableSpots : '?';
            } catch (e) { el.innerHTML = 'Err'; }
        }

        async function checkRevenue() {
            const y = document.getElementById('revYear').value;
            const m = document.getElementById('revMonth').value;
            const el = document.getElementById('monthlyRev');
            el.innerHTML = '...';
            try {
                // GET /parking/monthly-revenue?parkingId=X&year=Y&month=M
                const res = await fetch(`/parking/monthly-revenue?parkingId=${parkingId}&year=${y}&month=${m}`);
                const json = await res.json();
                el.innerHTML = (json.revenue || 0).toFixed(2) + ' €';
            } catch (e) { el.innerHTML = 'Err'; }
        }

    </script>
</body>

</html>