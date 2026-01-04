<?php
require_once 'includes/db.php';

$token = $_GET['token'] ?? '';
$message = "";

if ($token) {
    // 1. Vérifier si le token existe et si le RDV n'est pas déjà passé
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE token_annulation = ? AND statut_rdv != 'ANNULÉ'");
    $stmt->execute([$token]);
    $rdv = $stmt->fetch();

    if ($rdv) {
        // 2. Annuler le RDV
        $stmt = $pdo->prepare("UPDATE reservations SET statut_rdv = 'ANNULÉ' WHERE id = ?");
        $stmt->execute([$rdv['id']]);
        
        $message = "✅ Votre rendez-vous a bien été annulé. L'équipe Oz Barber a été prévenue.";
        
        // Optionnel : Envoyer un mail à l'admin pour prévenir
        // mail("admin@ozbarber.com", "ANNULATION CLIENT", "Le RDV de " . $rdv['client_nom'] . " est annulé.");
    } else {
        $message = "❌ Ce lien est invalide ou le rendez-vous est déjà annulé/passé.";
    }
} else {
    $message = "Lien incorrect.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Annulation RDV | Oz Barber</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background: #111; color: white; display: flex; justify-content: center; align-items: center; height: 100vh; text-align: center; }
        .box { background: #222; padding: 40px; border: 1px solid #d4af37; border-radius: 10px; max-width: 500px; }
        h1 { color: #d4af37; }
        a { color: white; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Annulation</h1>
        <p><?php echo $message; ?></p>
        <br>
        <a href="index.php">Retour au site</a>
    </div>
</body>
</html>