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

        /* Badges & Utilities */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .text-muted {
            color: #6c757d;
            font-size: 0.9em;
        }

        .fw-bold {
            font-weight: bold;
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
            <div class="tab" onclick="switchTab('stats')">Tableau de Bord</div>
        </div>

        <!-- PRICING TAB -->
        <div id="pricing" class="content-section active">
            <h2>Règles de Tarification</h2>
            <p class="text-muted">Définissez comment le prix du stationnement est calculé.</p>
            <form id="pricingForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Début de la période (minutes)</label>
                        <input type="number" name="startDurationMinute" value="0" required placeholder="Ex: 0">
                        <small class="text-muted">Minute de début d'application du tarif.</small>
                    </div>
                    <div class="form-group">
                        <label>Fin de la période (minutes)</label>
                        <input type="number" name="endDurationMinute" placeholder="Laisser vide pour illimité">
                        <small class="text-muted">Minute de fin (ex: 120 pour 2h).</small>
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
                        <small class="text-muted">Fixé à 15 min.</small>
                    </div>
                </div>
                <div class="form-group">
                    <label>Date d'application</label>
                    <input type="date" name="effectiveDate" required>
                </div>
                <button type="submit" class="btn-primary">Enregistrer ce tarif</button>
                <div id="pricingMsg"></div>
            </form>
        </div>

        <!-- HOURS TAB -->
        <div id="hours" class="content-section">
            <h2>Horaires d'ouverture</h2>

            <div
                style="background: #e6fffa; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 5px solid #00b894;">
                <label for="open247Toggle" style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" id="open247Toggle" style="width: 20px; height: 20px; margin-right: 15px;">
                    <div>
                        <strong style="font-size: 1.1em; color: #0056b3;">Ouvert 24h/24 et 7j/7</strong>
                        <small style="display:block; margin-top:5px; color:#666;">
                            Si activé, le parking est accessible en permanence. Les horaires personnalisés seront
                            désactivés.
                        </small>
                    </div>
                </label>
            </div>

            <form id="hoursForm" style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                <h3>Nouvelle plage horaire</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Du</label>
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
                        <label>Au</label>
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
                        <select name="openingTime" required>
                            <?php for ($h = 0; $h < 24; $h++):
                                for ($m = 0; $m < 60; $m += 30): // Simplified steps
                                    $time = sprintf('%02d:%02d', $h, $m);
                                    echo "<option value=\"$time\">$time</option>";
                                endfor;
                            endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Heure de fermeture</label>
                        <select name="closingTime" required>
                            <?php for ($h = 0; $h < 24; $h++):
                                for ($m = 0; $m < 60; $m += 30):
                                    $time = sprintf('%02d:%02d', $h, $m);
                                    $selected = ($time === '19:00') ? 'selected' : '';
                                    echo "<option value=\"$time\" $selected>$time</option>";
                                endfor;
                            endfor; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-success">Ajouter cette plage</button>
                <div id="hoursMsg"></div>
            </form>

            <h3>Horaires définis</h3>
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
                        <th>Détails & Date</th>
                        <th>Durée Estimée</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody id="reservationsTable">
                    <tr>
                        <td colspan="3">Chargement...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- SESSIONS TAB -->
        <div id="sessions" class="content-section">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2>Historique des Stationnements</h2>
                <button onclick="loadSessions()" class="btn-primary">Actualiser</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Session</th>
                        <th>Durée Réelle</th>
                        <th>Coût Final</th>
                    </tr>
                </thead>
                <tbody id="sessionsTable">
                    <tr>
                        <td colspan="3">Chargement...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- SUBSCRIPTIONS TAB -->
        <div id="subscriptions" class="content-section">
            <h2>Gestion des Abonnements</h2>
            <div style="margin-bottom: 20px; padding: 15px; background: #e9ecef; border-radius: 4px;">
                <h3>Créer un type d'abonnement</h3>
                <form id="addSubForm" style="display:flex; gap:10px; align-items:end;">
                    <div style="flex:1">
                        <label>Nom (ex: Mensuel 24/7)</label>
                        <input type="text" name="name" required placeholder="Nom du forfait">
                    </div>
                    <div style="flex:2">
                        <label>Description (Optionnel)</label>
                        <input type="text" name="description" placeholder="Détails (ex: Accès illimité)">
                    </div>
                    <div style="flex:1">
                        <label>Prix (€)</label>
                        <input type="number" step="0.01" name="price" required placeholder="0.00">
                    </div>
                    <button type="submit" class="btn-success">Ajouter</button>
                </form>
                <div id="subMsg"></div>
            </div>

            <h3>Types d'abonnements actifs</h3>
            <ul id="subList" style="list-style:none; padding:0;">
                <li>Chargement...</li>
            </ul>
        </div>

        <!-- ALERTS TAB -->
        <div id="alerts" class="content-section">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2 style="color:#dc3545">🚨 Véhicules en Infraction</h2>
                <button onclick="loadAlerts()" class="btn-primary">Actualiser</button>
            </div>
            <p>Liste des véhicules stationnés en dehors de leur période autorisée (réservation ou abonnement).</p>
            <table>
                <thead>
                    <tr>
                        <th>Identifiant</th>
                        <th>Entrée</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody id="alertsTable">
                    <tr>
                        <td colspan="3">Chargement...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- STATS TAB -->
        <div id="stats" class="content-section">
            <h2>Statistiques & Finance</h2>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:30px;">
                <div class="stat-card">
                    <h3>Places Disponibles</h3>
                    <div id="availSpots" class="stat-value">-</div>
                    <small>À l'instant T</small>
                    <button onclick="checkAvailability()" style="display:block; margin:10px auto;"
                        class="btn-sm">Vérifier maintenant</button>
                </div>

                <div class="stat-card">
                    <h3>Chiffre d'Affaires Mensuel</h3>
                    <div class="form-row"
                        style="justify-content: center; margin-bottom: 10px; align-items: center; gap: 5px;">
                        <select id="revMonth" style="width: auto;">
                            <?php
                            $months = [
                                1 => 'Janvier',
                                2 => 'Février',
                                3 => 'Mars',
                                4 => 'Jeudi',
                                5 => 'Mai',
                                6 => 'Juin',
                                7 => 'Juillet',
                                8 => 'Août',
                                9 => 'Septembre',
                                10 => 'Octobre',
                                11 => 'Novembre',
                                12 => 'Décembre'
                            ];
                            $currentMonth = (int) date('m');
                            foreach ($months as $num => $name): ?>
                                <option value="<?= $num ?>" <?= $num === $currentMonth ? 'selected' : '' ?>><?= $name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" id="revYear" value="<?= date('Y') ?>" style="width:70px" min="2020"
                            max="2100">
                    </div>
                    <div id="monthlyRev" class="stat-value">0.00 €</div>
                    <div id="monthlyBreakdown" style="font-size:0.8em; color:#666; margin-top:5px; height: 40px;"></div>
                    <button onclick="checkRevenue()" class="btn-sm">Calculer</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        const pathParts = window.location.pathname.split('/');
        const parkingId = pathParts[2];

        function switchTab(tabId) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.content-section').forEach(c => c.classList.remove('active'));

            document.querySelector(`.tab[onclick="switchTab('${tabId}')"]`).classList.add('active');
            document.getElementById(tabId).classList.add('active');

            // Chargement différé des données
            if (tabId === 'pricing') loadPricing();
            if (tabId === 'hours') loadHours();
            if (tabId === 'reservations') loadReservations();
            if (tabId === 'sessions') loadSessions();
            if (tabId === 'subscriptions') loadSubscriptions();
            if (tabId === 'alerts') loadAlerts();
            if (tabId === 'stats') { checkAvailability(); }
        }

        loadPricing();


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
                    loadSubscriptions();
                } else {
                    alert('Erreur ajout');
                }
            } catch (e) { console.error(e); }
        });

        async function loadSubscriptions() {
            const el = document.getElementById('subList');
            try {
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
                    li.innerHTML = `<strong>${item.name}</strong> (${formatMoney(item.monthlyPrice)}) - ${item.description || ''}`;
                    el.appendChild(li);
                });
            } catch (e) { el.innerHTML = 'Erreur chargement'; }
        }

        async function loadAlerts() {
            const tbody = document.getElementById('alertsTable');
            try {
                const response = await fetch(`/parking/sessions-out-of-reservation-or-subscription?parkingId=${parkingId}`);
                const list = await response.json();
                tbody.innerHTML = '';
                if (!list || list.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding:20px; color:#666;">Aucune alerte pour le moment.</td></tr>';
                    return;
                }
                list.forEach(s => {
                    tbody.innerHTML += `
                        <tr style="background-color: #fff5f5;">
                            <td><small>${s.userId}</small></td>
                            <td>${formatDate(s.entryDateTime)}</td>
                            <td>
                                <span class="badge badge-danger">Intrus / Hors délai</span>
                            </td>
                        </tr>`;
                });
            } catch (e) { tbody.innerHTML = '<tr><td colspan="3">Erreur</td></tr>'; }
        }

        async function loadPricing() {
            try {
                const res = await fetch(`/pricing-rule/list?parkingId=${parkingId}`);
                const list = await res.json();
                if (list.length > 0) {
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

        updateHoursState(is247);

        toggle247.addEventListener('change', async (e) => {
            const checked = e.target.checked;
            updateHoursState(checked);

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

                // Tri par jour
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

        function formatDate(str) {
            if (!str) return '-';
            const d = new Date(str);
            return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }) +
                ' ' + d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        }

        function formatMoney(amount) {
            return (amount ? parseFloat(amount).toFixed(2) : '0.00') + ' €';
        }

        function getDuration(start, end) {
            const d1 = new Date(start);
            const d2 = end ? new Date(end) : new Date();
            const diffMs = d2 - d1;
            const diffMins = Math.floor(diffMs / 60000);
            const hrs = Math.floor(diffMins / 60);
            const mins = diffMins % 60;
            return `${hrs}h ${mins}m`;
        }

        async function loadReservations() {
            const tbody = document.getElementById('reservationsTable');
            tbody.innerHTML = '<tr><td colspan="3">Chargement...</td></tr>';
            try {
                const res = await fetch(`/reservation/list?parkingId=${parkingId}`);
                if (!res.ok) throw new Error('Network response was not ok');
                const list = await res.json();

                tbody.innerHTML = '';
                if (!list || list.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding:20px; color:#666;">Aucune réservation pour le moment.</td></tr>';
                    return;
                }
                list.forEach(r => {
                    let badgeClass = 'badge-info';
                    let statusLabel = r.status;
                    if (r.status === 'pending') { badgeClass = 'badge-warning'; statusLabel = 'En attente'; }
                    if (r.status === 'confirmed') { badgeClass = 'badge-success'; statusLabel = 'Confirmée'; }
                    if (r.status === 'cancelled') { badgeClass = 'badge-danger'; statusLabel = 'Annulée'; }
                    if (r.status === 'completed') { badgeClass = 'badge-secondary'; statusLabel = 'Terminée'; }

                    tbody.innerHTML += `
                        <tr>
                            <td>
                                <span class="badge ${badgeClass}">${statusLabel}</span><br>
                                <span class="text-muted" style="display:block; margin-top:4px;">${formatDate(r.startDateTime)}</span> 
                                <span class="text-muted" style="display:block;">au ${formatDate(r.endDateTime)}</span>
                            </td>
                            <td>${getDuration(r.startDateTime, r.endDateTime)}</td>
                            <td class="fw-bold">${r.finalAmount ? formatMoney(r.finalAmount) : (r.calculatedAmount ? formatMoney(r.calculatedAmount) : '-')}</td>
                        </tr>`;
                });
            } catch (e) {
                console.error(e);
                tbody.innerHTML = '<tr><td colspan="3" style="color:red">Erreur de chargement.</td></tr>';
            }
        }

        async function loadSessions() {
            const tbody = document.getElementById('sessionsTable');
            try {
                const response = await fetch(`/parking-session/list?parkingId=${parkingId}`);
                const list = await response.json();
                tbody.innerHTML = '';
                if (!list || list.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding:20px; color:#666;">Aucun stationnement pour le moment.</td></tr>';
                    return;
                }
                list.forEach(s => {
                    const isOngoing = !s.exitDateTime;
                    const statusBadge = isOngoing
                        ? '<span class="badge badge-success">En cours</span>'
                        : '<span class="badge badge-secondary">Terminé</span>';

                    const penaltyParams = s.penaltyApplied
                        ? '<span class="badge badge-danger">⚠️ Pénalité incluse</span>'
                        : '<span style="color:green">✔ Tarif normal</span>';

                    tbody.innerHTML += `
                        <tr>
                            <td>
                                ${statusBadge}<br>
                                <span class="text-muted">Entrée: ${formatDate(s.entryDateTime)}</span>
                                ${!isOngoing ? '<br><span class="text-muted">Sortie: ' + formatDate(s.exitDateTime) + '</span>' : ''}
                            </td>
                            <td>${getDuration(s.entryDateTime, s.exitDateTime)}</td>
                            <td>
                                ${penaltyParams}
                                ${!isOngoing && s.finalAmount ? '<div style="margin-top:4px; font-weight:bold; font-size:1.1em;">Total: ' + formatMoney(s.finalAmount) + '</div>' : ''}
                            </td>
                        </tr>`;
                });
            } catch (e) { tbody.innerHTML = '<tr><td colspan="3">Erreur</td></tr>'; }
        }

        async function checkAvailability() {
            const el = document.getElementById('availSpots');
            el.innerHTML = '...';
            const now = new Date().toISOString();
            try {
                const res = await fetch(`/parking/available-spots?parkingId=${parkingId}&at=${now}`);
                const json = await res.json();
                el.innerHTML = json.availableSpots !== undefined ? json.availableSpots : '?';
            } catch (e) { el.innerHTML = 'Err'; }
        }

        async function checkRevenue() {
            const y = document.getElementById('revYear').value;
            const m = document.getElementById('revMonth').value;
            try {
                const res = await fetch(`/monthly-revenue/get?parkingId=${parkingId}&year=${y}&month=${m}`);
                const data = await res.json();
                document.getElementById('monthlyRev').textContent = formatMoney(data.revenue);

                if (data.breakdown) {
                    const r = formatMoney(data.breakdown.reservations);
                    const s = formatMoney(data.breakdown.subscriptions);
                    document.getElementById('monthlyBreakdown').innerHTML =
                        `Réservations: <strong>${r}</strong><br>Abonnements: <strong>${s}</strong>`;
                } else {
                    document.getElementById('monthlyBreakdown').innerHTML = '';
                }

            } catch (e) { console.error(e); }
        }

    </script>
</body>

</html>