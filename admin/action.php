<?php
require_once '../includes/db.php';

// Sécurité basique
if (!isset($_POST['action'])) die('Erreur');

if ($_POST['action'] == 'save_note') {
    $id = $_POST['id'];
    $note = htmlspecialchars($_POST['note']);
    
    $stmt = $pdo->prepare("UPDATE reservations SET notes_internes = ? WHERE id = ?");
    $stmt->execute([$note, $id]);
    
    // On redirige vers le dashboard
    header("Location: index.php"); 
}

if ($_POST['action'] == 'change_status') {
    $id = $_POST['id'];
    $status = $_POST['status']; // 'TERMINÉ' ou 'ANNULÉ'
    
    $stmt = $pdo->prepare("UPDATE reservations SET statut_rdv = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    
    header("Location: index.php");
}
?>