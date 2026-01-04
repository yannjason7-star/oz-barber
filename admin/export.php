<?php
require_once '../includes/db.php';

// Sécurité
$user = 'admin'; $pass = 'ozbarber2026';
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != $user || $_SERVER['PHP_AUTH_PW'] != $pass) {
    exit('Accès refusé');
}

// Nom du fichier
$filename = "oz_compta_" . date('Y-m-d') . ".csv";

// Headers pour forcer le téléchargement
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Création du fichier
$output = fopen('php://output', 'w');

// Entêtes des colonnes (UTF-8 BOM pour qu'Excel lise bien les accents)
fputs($output, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
fputcsv($output, array('ID', 'Date RDV', 'Heure', 'Client', 'Téléphone', 'Service', 'Prix', 'Lieu', 'Statut Paiement', 'Notes Internes'), ';');

// Récupération des données
$sql = "SELECT r.id, r.date_rdv, r.heure_rdv, r.client_nom, r.client_tel, s.nom, s.prix, r.lieu, r.statut_paiement, r.notes_internes 
        FROM reservations r 
        JOIN services s ON r.service_id = s.id 
        ORDER BY r.date_rdv DESC";
$stmt = $pdo->query($sql);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row, ';');
}

fclose($output);
exit();
?>