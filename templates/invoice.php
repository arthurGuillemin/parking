<?php
/**
 * @var App\Domain\Entity\Invoice $invoice
 * @var string $userFullName
 */
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Facture #<?= $invoice->getInvoiceId() ?></title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            line-height: 1.6;
            color: #333;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 40px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .invoice-details {
            text-align: right;
        }

        .invoice-box {
            border: 1px solid #eee;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
        }

        .total-row {
            font-weight: bold;
            font-size: 1.2em;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .no-print {
                display: none;
            }

            .invoice-box {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>

<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()"
            style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px;">Imprimer
            / PDF</button>
        <a href="/dashboard" style="margin-left: 10px; text-decoration: none; color: #666;">Retour au tableau de
            bord</a>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div class="logo">PARKING PARTAGÉ</div>
            <div class="invoice-details">
                <h1>FACTURE</h1>
                <p>Numéro : #<?= $invoice->getInvoiceId() ?></p>
                <p>Date : <?= $invoice->getIssuedDate()->format('d/m/Y') ?></p>
            </div>
        </div>

        <table>
            <tr>
                <td>De :<br><strong>Parking Partagé</strong><br>123 Rue de la Paix<br>75000 Paris</td>
                <td>À :<br><strong><?= htmlspecialchars($userFullName) ?></strong><br></td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Montant HT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?= $invoice->getInvoiceType() === 'reservation' ? 'Réservation Parking' : 'Abonnement / Session' ?>
                        <?php if ($invoice->getReservationId()): ?>
                            (Réf: #<?= $invoice->getReservationId() ?>)
                        <?php endif; ?>
                    </td>
                    <?php
                    $ttc = $invoice->getAmountTtc();
                    $ht = $ttc / 1.2;
                    $tva = $ttc - $ht;
                    ?>
                    <td style="text-align: right;"><?= number_format($ht, 2) ?> €</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td style="text-align: right; border-top: 2px solid #333;">TVA (20%)</td>
                    <td style="text-align: right; border-top: 2px solid #333;">
                        <?= number_format($tva, 2) ?> €
                    </td>
                </tr>
                <tr class="total-row">
                    <td style="text-align: right;">Total TTC</td>
                    <td style="text-align: right;"><?= number_format($ttc, 2) ?> €</td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top: 40px; font-size: 0.9em; text-align: center; color: #777;">
            <p>Merci de votre confiance.</p>
            <p>&copy; 2025 Parkingtest - Gestion de parking simplifiée</p>
        </div>
    </div>

    <script>
        // Auto-print on load if query param ?print=true exists
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === 'true') {
            window.print();
        }
    </script>
</body>

</html>
