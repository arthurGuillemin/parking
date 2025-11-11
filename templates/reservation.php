<?php
$content = '
<h2>Réservation</h2>
<form method="post" action="/reservation">
    <label>Parking: <input type="text" name="parking" required></label><br>
    <label>Début: <input type="datetime-local" name="starts_at" required></label><br>
    <label>Fin: <input type="datetime-local" name="ends_at" required></label><br>
    <button type="submit">Réserver</button>
</form>
';
require __DIR__ . '/layout.php';
