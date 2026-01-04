<?php
require_once '../includes/db.php';

// SÉCURITÉ
$user = 'admin'; $pass = 'ozbarber2026';
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != $user || $_SERVER['PHP_AUTH_PW'] != $pass) {
    header('WWW-Authenticate: Basic realm="Espace Admin Oz Barber"');
    header('HTTP/1.0 401 Unauthorized');
    die('Accès refusé.');
}

// --- STATISTIQUES ---
// 1. Chiffre d'Affaires TOTAL (Basé sur les réservations payées ou terminées)
$sql_ca = "SELECT SUM(s.prix) as total FROM reservations r JOIN services s ON r.service_id = s.id WHERE r.statut_paiement LIKE '%PAYÉ%' OR r.statut_paiement LIKE '%ACOMPTE%'";
$ca_total = $pdo->query($sql_ca)->fetch()['total'] ?? 0;

// 2. RDV Aujourd'hui
$today = date('Y-m-d');
$sql_today = "SELECT COUNT(*) as nb FROM reservations WHERE date_rdv = '$today'";
$nb_today = $pdo->query($sql_today)->fetch()['nb'];

// 3. Récupérer TOUTES les réservations
$sql = "SELECT r.*, s.nom as service_nom, s.prix 
        FROM reservations r 
        JOIN services s ON r.service_id = s.id 
        ORDER BY r.date_rdv DESC";
$reservations = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Fonction Fidélité : Compte les visites terminées d'un numéro
function getVisitesCount($pdo, $tel) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE client_tel = ? AND statut_rdv = 'TERMINÉ'");
    $stmt->execute([$tel]);
    return $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Oz Barber | Manager Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #f0f2f5; padding: 20px; margin: 0; }
        
        /* HEADER DASHBOARD */
        .dash-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .logo-area { display: flex; align-items: center; gap: 15px; }
        .btn-export { background: #155724; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .btn-site { background: #333; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }

        /* CARTES STATS */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 5px solid #d4af37; }
        .stat-val { font-size: 1.8rem; font-weight: bold; color: #111; }
        .stat-label { color: #777; font-size: 0.9rem; }

        /* TABLEAU */
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { background: #111; color: #d4af37; padding: 15px; text-align: left; font-size: 0.9rem; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 0.9rem; vertical-align: top; }
        tr:hover { background: #f9f9f9; }

        /* BADGES */
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 0.75rem; font-weight: bold; }
        .bg-ok { background: #d4edda; color: #155724; }
        .bg-wait { background: #fff3cd; color: #856404; }
        .bg-gold { background: #d4af37; color: #000; } /* Pour la fidélité */
        
        /* FORMS ACTIONS */
        .action-form { display: inline-block; margin-right: 5px; }
        .btn-sm { border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-size: 0.8rem; }
        .btn-check { background: #28a745; color: white; }
        .btn-cancel { background: #dc3545; color: white; }
        
        textarea.note-input { width: 100%; border: 1px solid #ddd; border-radius: 4px; padding: 5px; font-family: inherit; font-size: 0.85rem; }
    </style>
</head>
<body>

    <div class="dash-header">
        <div class="logo-area">
            <h2 style="margin: 0;">🛠️ ADMINISTRATION</h2>
        </div>
        <div>
            <a href="export.php" class="btn-export">📥 Exporter Comptabilité (CSV)</a>
            <a href="../index.php" class="btn-site">Voir le Site</a>
        </div>
    </div>

    <!-- STATISTIQUES -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Chiffre d'Affaires (Est.)</div>
            <div class="stat-val"><?php echo number_format($ca_total, 0, ',', ' '); ?> FCFA</div>
        </div>
        <div class="stat-card" style="border-color: #28a745;">
            <div class="stat-label">RDV Aujourd'hui</div>
            <div class="stat-val"><?php echo $nb_today; ?></div>
        </div>
        <div class="stat-card" style="border-color: #007bff;">
            <div class="stat-label">Total Réservations</div>
            <div class="stat-val"><?php echo count($reservations); ?></div>
        </div>
    </div>

    <!-- TABLEAU DES RDV -->
    <div class="table-container">
        <h3>📅 Planning & Suivi</h3>
        <table>
            <thead>
                <tr>
                    <th>Date / Heure</th>
                    <th>Client / Fidélité</th>
                    <th>Service & Prix</th>
                    <th>Paiement</th>
                    <th>Statut RDV</th>
                    <th width="20%">Notes Internes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reservations as $rsa): ?>
                    <?php 
                        // Calcul Fidélité
                        $nb_visites = getVisitesCount($pdo, $rsa['client_tel']);
                        $is_fidele = ($nb_visites >= 9); // Si c'est sa 10ème visite ou plus
                    ?>
                    <tr style="<?php echo ($rsa['statut_rdv'] == 'ANNULÉ') ? 'opacity:0.5;' : ''; ?>">
                        <td>
                            <strong><?php echo date('d/m', strtotime($rsa['date_rdv'])); ?></strong><br>
                            <?php echo $rsa['heure_rdv']; ?><br>
                            <small><?php echo htmlspecialchars($rsa['lieu']); ?></small>
                        </td>
                        <td>
                            <b><?php echo htmlspecialchars($rsa['client_nom']); ?></b><br>
                            <a href="tel:<?php echo $rsa['client_tel']; ?>" style="color:#555;"><?php echo $rsa['client_tel']; ?></a>
                            
                            <!-- BADGE FIDELITE -->
                            <div style="margin-top: 5px;">
                                <span class="badge" style="background:#eee;">Visite N°<?php echo $nb_visites + 1; ?></span>
                                <?php if($is_fidele): ?>
                                    <div class="badge bg-gold" style="margin-top:2px;">🎁 CLIENT VIP !</div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($rsa['service_nom']); ?><br>
                            <strong><?php echo number_format($rsa['prix'], 0, ',', ' '); ?> FCFA</strong>
                        </td>
                        <td>
                            <?php if(strpos($rsa['statut_paiement'], 'PAYÉ') !== false || strpos($rsa['statut_paiement'], 'ACOMPTE') !== false): ?>
                                <span class="badge bg-ok"><?php echo $rsa['statut_paiement']; ?></span>
                            <?php else: ?>
                                <span class="badge bg-wait"><?php echo $rsa['statut_paiement']; ?></span>
                            <?php endif; ?>
                            <br><small style="font-size:0.7em;"><?php echo $rsa['reference_paiement']; ?></small>
                        </td>
                        <td>
                            <strong><?php echo $rsa['statut_rdv']; ?></strong>
                        </td>
                        <td>
                            <!-- FORMULAIRE NOTE INTERNE -->
                            <form action="action.php" method="POST">
                                <input type="hidden" name="action" value="save_note">
                                <input type="hidden" name="id" value="<?php echo $rsa['id']; ?>">
                                <textarea name="note" class="note-input" rows="2" placeholder="Note secrète..."><?php echo htmlspecialchars($rsa['notes_internes']); ?></textarea>
                                <button type="submit" style="font-size:0.7rem; background:#333; color:white; border:none; border-radius:3px; margin-top:2px; cursor:pointer;">Sauver</button>
                            </form>
                        </td>
                        <td>
                            <!-- BOUTONS ACTIONS (Valider / Annuler) -->
                            <?php if($rsa['statut_rdv'] != 'TERMINÉ'): ?>
                                <form action="action.php" method="POST" class="action-form">
                                    <input type="hidden" name="action" value="change_status">
                                    <input type="hidden" name="id" value="<?php echo $rsa['id']; ?>">
                                    <input type="hidden" name="status" value="TERMINÉ">
                                    <button type="submit" class="btn-sm btn-check" title="Marquer comme Terminé (Client venu)">✅ Venu</button>
                                </form>
                            <?php endif; ?>

                            <?php if($rsa['statut_rdv'] != 'ANNULÉ'): ?>
                                <form action="action.php" method="POST" class="action-form">
                                    <input type="hidden" name="action" value="change_status">
                                    <input type="hidden" name="id" value="<?php echo $rsa['id']; ?>">
                                    <input type="hidden" name="status" value="ANNULÉ">
                                    <button type="submit" class="btn-sm btn-cancel" title="Annuler le RDV">❌ Annuler</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>