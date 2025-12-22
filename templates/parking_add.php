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
            <div id="successMessage"
                style="color: green; font-size: 0.9em; margin-top: 10px; display: none; padding: 10px; background: #d4edda; border-radius: 4px;">
            </div>
            <div id="errorMessage" class="error"></div>
        </form>
        <a href="/owner/dashboard" class="back-link">← Retour au tableau de bord</a>
    </div>

    <script>
        document.getElementById('addParkingForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';

            // Récupérer les valeurs des champs
            const street = document.getElementById('street').value;
            const zip = document.getElementById('zipCode').value;
            const city = document.getElementById('city').value;

            // Construire l'objet data
            const data = {
                name: document.getElementById('name').value,
                address: `${street}, ${zip} ${city}`,
                latitude: document.getElementById('latitude').value,
                longitude: document.getElementById('longitude').value,
                totalCapacity: document.getElementById('totalCapacity').value,
                open_24_7: document.getElementById('open_24_7').checked
            };

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
                    successDiv.textContent = '✅ Parking "' + data.name + '" créé avec succès ! Redirection...';
                    successDiv.style.display = 'block';
                    setTimeout(() => {
                        window.location.href = '/owner/dashboard';
                    }, 2000);
                } else {
                    errorDiv.textContent = result.error || 'Erreur lors de la création.';
                    errorDiv.style.display = 'block';
                }
            } catch (error) {
                console.error(error);
                errorDiv.textContent = 'Erreur de connexion: ' + error.message;
                errorDiv.style.display = 'block';
            }
        });
    </script>
</body>

</html>