<?php
$pageTitle = "Réserver - " . htmlspecialchars($parking->getName());
ob_start();
?>

<div class="container" style="max-width: 600px; margin-top: 40px;">
    <div class="card">
        <h2>Réserver une place</h2>
        <div class="mb-3">
            <strong>Parking :</strong> <?= htmlspecialchars($parking->getName()) ?><br>
            <span class="text-muted"><?= htmlspecialchars($parking->getAddress()) ?></span>
        </div>

        <form action="/reservation/create" method="POST">
            <input type="hidden" name="parkingId" value="<?= $parking->getParkingId() ?>">

            <style>
                .custom-date-wrapper {
                    position: relative;
                    flex-grow: 1;
                }

                .custom-date-wrapper input[type="date"] {
                    position: relative;
                    z-index: 2;
                    color: transparent;
                    background: transparent;
                }

                .custom-date-wrapper input[type="date"]:focus+input[type="text"],
                .custom-date-wrapper input[type="date"]:active+input[type="text"] {
                    border-color: #86b7fe;
                    box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .25);
                }

                .custom-date-wrapper input[type="text"] {
                    position: absolute;
                    top: 0;
                    left: 0;
                    bottom: 0;
                    right: 0;
                    z-index: 1;
                    background-color: #fff !important;
                    /* Force white background */
                }
            </style>

            <div class="mb-3">
                <label class="form-label"><strong>Début de stationnement</strong></label>
                <div style="display: flex; gap: 10px;">
                    <div class="custom-date-wrapper">
                        <input type="date" class="form-control" id="start_date" required value="<?= date('Y-m-d') ?>"
                            min="<?= date('Y-m-d') ?>">
                        <input type="text" class="form-control" id="start_date_display" readonly tabindex="-1">
                    </div>
                    <select class="form-control" id="start_time" required style="min-width: 100px;">
                        <?php for ($h = 0; $h < 24; $h++):
                            for ($m = 0; $m < 60; $m += 30):
                                $time = sprintf('%02d:%02d', $h, $m);
                                $selected = ($time === '09:00') ? 'selected' : '';
                                echo "<option value=\"$time\" $selected>$time</option>";
                            endfor;
                        endfor; ?>
                    </select>
                </div>
                <input type="hidden" id="start" name="start">
            </div>

            <div class="mb-3">
                <label class="form-label"><strong>Fin de stationnement</strong></label>
                <div style="display: flex; gap: 10px;">
                    <div class="custom-date-wrapper">
                        <input type="date" class="form-control" id="end_date" required value="<?= date('Y-m-d') ?>"
                            min="<?= date('Y-m-d') ?>">
                        <input type="text" class="form-control" id="end_date_display" readonly tabindex="-1">
                    </div>
                    <select class="form-control" id="end_time" required style="min-width: 100px;">
                        <?php for ($h = 0; $h < 24; $h++):
                            for ($m = 0; $m < 60; $m += 30):
                                $time = sprintf('%02d:%02d', $h, $m);
                                $selected = ($time === '18:00') ? 'selected' : '';
                                echo "<option value=\"$time\" $selected>$time</option>";
                            endfor;
                        endfor; ?>
                    </select>
                </div>
                <input type="hidden" id="end" name="end">
            </div>

            <div class="alert alert-info">
                ℹ️ Le prix sera calculé en fonction de la durée réelle. <br>
                Une estimation sera affichée après confirmation.
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Confirmer la réservation</button>
                <a href="/parkings" class="btn btn-link text-center">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
    function formatDateFR(isoDate) {
        if (!isoDate) return '';
        const parts = isoDate.split('-');
        if (parts.length === 3) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        return isoDate;
    }

    function updateHiddenInputs() {
        const startDate = document.getElementById('start_date').value;
        const startTime = document.getElementById('start_time').value;
        const endDate = document.getElementById('end_date').value;
        const endTime = document.getElementById('end_time').value;

        // Update display inputs
        document.getElementById('start_date_display').value = formatDateFR(startDate);
        document.getElementById('end_date_display').value = formatDateFR(endDate);

        if (startDate && startTime) {
            document.getElementById('start').value = startDate + 'T' + startTime;
        }
        if (endDate && endTime) {
            document.getElementById('end').value = endDate + 'T' + endTime;
        }
    }

    const inputs = ['start_date', 'start_time', 'end_date', 'end_time'];
    inputs.forEach(id => {
        document.getElementById(id).addEventListener('input', updateHiddenInputs); // Use input event for immediate update
        document.getElementById(id).addEventListener('change', updateHiddenInputs);
    });

    updateHiddenInputs();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
