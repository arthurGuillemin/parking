<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Parking - Parking Partagé</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        input[type="checkbox"] {
            width: auto;
            transform: scale(1.2);
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
        }

        .error {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
            display: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Ajouter un Nouveau Parking</h2>
        <form id="addParkingForm">
            <div class="form-group">
                <label for="name">Nom du parking</label>
                <input type="text" id="name" name="name" required placeholder="Ex: Parking Centre-Ville">
            </div>

            <div class="form-group">
                <label for="street">Numéro et Rue</label>
                <input type="text" id="street" required placeholder="123 rue de la Paix" style="margin-bottom: 15px;">

                <div style="display: flex; gap: 20px;">
                    <div style="flex:1">
                        <label for="zipCode">Code Postal</label>
                        <input type="text" id="zipCode" required placeholder="75000">
                    </div>
                    <div style="flex:2">
                        <label for="city">Ville</label>
                        <input type="text" id="city" required placeholder="Paris">
                    </div>
                </div>
            </div>

            <div class="form-group" style="display: flex; gap: 20px;">
                <div style="flex:1">
                    <label for="latitude">Latitude</label>
                    <input type="text" id="latitude" name="latitude" required placeholder="48.8566">
                </div>
                <div style="flex:1">
                    <label for="longitude">Longitude</label>
                    <input type="text" id="longitude" name="longitude" required placeholder="2.3522">
                </div>
            </div>

            <div class="form-group">
                <label for="totalCapacity">Capacité totale (places)</label>
                <input type="number" id="totalCapacity" name="totalCapacity" required min="1">
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="open_24_7" name="open_24_7">
                <label for="open_24_7" style="margin:0; font-weight:normal;">Ouvert 24h/24 et 7j/7</label>
            </div>

            <button type="submit">Créer le parking</button>
            <div id="errorMessage" class="error"></div>
        </form>
        <a href="/owner/dashboard" class="back-link">← Retour au tableau de bord</a>
    </div>

    <script>
        document.getElementById('addParkingForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            // Convert checkbox to boolean/int manually or let backend handle simple presence check
            const data = Object.fromEntries(formData);

            // Fix checkbox handling: checkbox value is 'on' if checked, missing if not
            // Concatenate address fields
            const street = document.getElementById('street').value;
            const zip = document.getElementById('zipCode').value;
            const city = document.getElementById('city').value;

            // Build the final data object manually to ensure structure
            const data = {
                name: formData.get('name'),
                address: `${street}, ${zip} ${city}`,
                latitude: formData.get('latitude'),
                longitude: formData.get('longitude'),
                totalCapacity: formData.get('totalCapacity'),
                open_24_7: document.getElementById('open_24_7').checked
            };

            // We need ownerId. 
            // APPROACH: The backend should ideally infer ownerId from the session/token.
            // If the backend 'add' method strictly requires ownerId in the body, we must provide it.
            // Let's assume we can get it from localStorage or the backend handles it.
            // CAUTION: Passing ownerId from client is insecure if not validated. 
            // For now, let's try to grab it from localStorage if available, otherwise rely on backend session.
            const ownerUser = JSON.parse(localStorage.getItem('owner_user') || '{}');
            if (ownerUser.id) {
                data.ownerId = ownerUser.id;
            } else {
                // If we don't have it, we might fail validation if backend doesn't extract it from token.
                // But let's try.
            }

            try {
                const response = await fetch('/parking/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Parking ajouté avec succès !');
                    window.location.href = '/owner/dashboard';
                } else {
                    const errorDiv = document.getElementById('errorMessage');
                    errorDiv.textContent = result.error || 'Erreur lors de la création.';
                    errorDiv.style.display = 'block';
                }
            } catch (error) {
                console.error(error);
                alert('Erreur: ' + error.message);
            }
        });
    </script>
</body>

</html>