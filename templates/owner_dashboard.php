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
        const token = localStorage.getItem('owner_token');

        async function loadDashboard() {
            try {
                const ownerUser = JSON.parse(localStorage.getItem('owner_user') || '{}');
                if (ownerUser.firstName) {
                    document.getElementById('ownerName').textContent = `${ownerUser.firstName} ${ownerUser.lastName}`;
                }

                const response = await fetch('/owner/parkings', {
                    headers: {
                        'Accept': 'application/json'
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
                const dateStr = new Date().toISOString();

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
                localStorage.removeItem('owner_token');
                localStorage.removeItem('owner_user');
                window.location.href = '/login';
            });
        }

        loadDashboard();
    </script>
</body>

</html>