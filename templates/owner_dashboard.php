<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Propriétaire - Parking Partagé</title>
    <link rel="stylesheet" href="/style.css">
</head>

<body>
    <header>
        <h1>Espace Propriétaire</h1>
        <nav>
            <span>Bienvenue, <span id="ownerName">Chargement...</span></span>
            <a href="#" onclick="logout()">Déconnexion</a>
        </nav>
    </header>

    <div class="container">
        <div class="actions">
            <a href="/parking/add" class="btn btn-primary">+ Ajouter un parking</a>
        </div>

        <h2 style="margin-bottom: 1rem;">Vos Parkings</h2>
        <div id="parkingList" class="grid">
            <div class="empty-state">Chargement de vos parkings...</div>
        </div>
    </div>

    <script>
        // Check authentication
        const token = localStorage.getItem('owner_token'); // Assuming you store token here
        /*
           NOTE: Since the current login implementation uses HttpOnly cookies for security (good!), 
           client-side JS might not be able to read the token directly if you switched to cookies only.
           However, the previous `LoginUseCase` returned a token in the body, and `AuthController` 
           sets a cookie.
           If we are relying on cookies for API calls, `fetch` will send them automatically with `credentials: 'include'`.
           For this template, we'll assume we fetch data and the controller auth middleware handles it.
        */

        async function loadDashboard() {
            try {
                // Fetch owner info (we might need a /owner/me endpoint, or use data from localStorage if saved during login)
                const ownerUser = JSON.parse(localStorage.getItem('owner_user') || '{}');
                if (ownerUser.firstName) {
                    document.getElementById('ownerName').textContent = `${ownerUser.firstName} ${ownerUser.lastName}`;
                }

                // Fetch parkings
                // We need an endpoint that returns parkings for the logged-in owner.
                // Assuming GET /owner/parkings returns JSON
                const response = await fetch('/owner/parkings', {
                    headers: {
                        'Accept': 'application/json'
                        // 'Authorization': 'Bearer ' + token // If using Bearer header
                    }
                });

                if (response.status === 401) {
                    window.location.href = '/login';
                    return;
                }

                const parkings = await response.json();
                const list = document.getElementById('parkingList');
                list.innerHTML = '';

                if (parkings.length === 0) {
                    list.innerHTML = '<div class="empty-state">Vous n\'avez pas encore ajouté de parking.<br><br><a href="/parking/add" class="btn btn-primary">Ajouter mon premier parking</a></div>';
                    return;
                }

                parkings.forEach(parking => {
                    const card = document.createElement('div');
                    card.className = 'card';
                    const statusBadge = parking.open_24_7
                        ? '<span class="badge badge-open">24/7</span>'
                        : '<span class="badge badge-closed">Horaires définis</span>';

                    card.innerHTML = `
                        <div style="display:flex; justify-content:space-between; align-items:start;">
                            <h3>${escapeHtml(parking.name)}</h3>
                            ${statusBadge}
                        </div>
                        <p>📍 ${escapeHtml(parking.address)}</p>

                        <p>🚗 Capacité: ${parking.totalCapacity} places 
                           <span id="avail-container-${parking.id}" style="display:none; margin-left:10px;">
                               (<span id="avail-${parking.id}" style="font-weight:bold;">...</span> disponibles)
                           </span>
                           <div style="margin-top:5px; font-size:0.9em; display:flex; gap:5px; align-items:center;">
                               <label>Voir dispo pour :</label>
                               <input type="datetime-local" id="date-${parking.id}" 
                                      value="${new Date().toISOString().slice(0, 16)}" 
                                      style="padding:2px; border:1px solid #ccc; border-radius:3px;"
                                      onchange="fetchAvailability(${parking.id})">
                           </div>
                        </p>
                        <div class="card-actions">
                            <a href="/parking/${parking.id}/manage" class="btn btn-success btn-sm">Gérer</a>
                        </div>
                    `;
                    list.appendChild(card);
                    fetchAvailability(parking.id);
                });
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('parkingList').innerHTML = '<div class="empty-state" style="color:red">Erreur lors du chargement des parkings.</div>';
            }
        }

        async function fetchAvailability(parkingId) {
            try {
                // Get date from input or default to now
                const input = document.getElementById(`date-${parkingId}`);
                let dateStr = input ? input.value : new Date().toISOString(); // local time from input

                // If input is datetime-local, it doesn't have timezone. 
                // We should append ':00Z' or handle it. toISOString() uses UTC.
                // Input value example: "2023-12-15T14:30"
                // Let's ensure full ISO format.
                if (input && input.value) {
                    dateStr = new Date(input.value).toISOString();
                }

                const res = await fetch(`/parking/available-spots?parkingId=${parkingId}&at=${dateStr}`);
                if (res.ok) {
                    const data = await res.json();
                    if (data.availableSpots !== undefined) {
                        document.getElementById(`avail-${parkingId}`).innerText = data.availableSpots;
                        document.getElementById(`avail-container-${parkingId}`).style.display = 'inline';
                    }
                }
            } catch (e) {
                console.error("Failed to load availability for " + parkingId, e);
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function logout() {
            fetch('/logout', { method: 'POST' }).then(() => {
                localStorage.removeItem('owner_token'); // Clear if used
                localStorage.removeItem('owner_user');
                window.location.href = '/login';
            });
        }

        loadDashboard();
    </script>
</body>

</html>