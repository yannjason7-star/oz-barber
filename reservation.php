<?php
require_once 'includes/db.php';

// 1. Récupération des infos du service
$service_id = isset($_GET['service_id']) ? $_GET['service_id'] : 1;
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

// Si le service n'existe pas, retour accueil
if (!$service) { header("Location: index.php"); exit(); }

$lieux = $pdo->query("SELECT * FROM lieux")->fetchAll();

// Calcul de l'acompte (50%)
$acompte = $service['prix'] / 2;

// 2. Traitement du Formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST['nom']);
    $tel_suffix = htmlspecialchars($_POST['tel']); // Les 9 chiffres saisis
    $tel_full = "+237 " . $tel_suffix; // Format BDD
    $lieu = htmlspecialchars($_POST['lieu']);
    $date = $_POST['date'];
    $heure = $_POST['heure'];

    // --- VALIDATION STRICTE (Côté Serveur) ---
    // Doit commencer par 6 et avoir 8 autres chiffres (Total 9)
    if (!preg_match("/^6[0-9]{8}$/", $tel_suffix)) {
        die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
                <h1 style='color:red'>❌ Numéro Invalide</h1>
                <p>Le numéro doit comporter 9 chiffres et commencer par 6.</p>
                <a href='javascript:history.back()'>Retourner au formulaire</a>
             </div>");
    }

    // Génération ID unique + Token Annulation Sécurisé
    $booking_id = "OZ-" . rand(1000, 9999);
    $token = bin2hex(random_bytes(16)); // Génère un code unique (ex: 8a4f9c...)

    // --- INSERTION BDD ---
    // On insère le token_annulation ici
    $sql = "INSERT INTO reservations (client_nom, client_tel, service_id, lieu, date_rdv, heure_rdv, statut_paiement, reference_paiement, token_annulation) VALUES (?, ?, ?, ?, ?, ?, 'ATTENTE WHATSAPP', ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $tel_full, $service_id, $lieu, $date, $heure, $booking_id, $token]);

    // --- GÉNÉRATION DU LIEN D'ANNULATION ---
    // Détecte automatiquement si on est en http ou https et le nom du site
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $base_url = "$protocol://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']);
    // Lien final : http://monsite.com/annulation.php?token=xyz...
    $link_cancel = "$base_url/annulation.php?token=$token";

    // --- EMAILS ---
    $to = "william.ndjike3@yahoo.com, donaldfotso00@icloud.com";
    $subject = "🔴 RÉSERVATION N°$booking_id (En attente)";
    $message = "
    NOUVELLE RÉSERVATION OZ BARBER
    =================================
    ID      : $booking_id
    CLIENT  : $nom
    TEL     : $tel_full
    SERVICE : " . $service['nom'] . "
    LIEU    : $lieu
    DATE    : Le $date à $heure
    
    ACOMPTE : " . number_format($acompte, 0, ',', ' ') . " FCFA (À régler)
    
    LIEN D'ANNULATION (Pour le client) :
    $link_cancel
    =================================
    ";
    $headers = "From: no-reply@ozbarber.com";

    // Simulation Localhost vs Envoi Réel
    if ($_SERVER['SERVER_NAME'] == 'localhost') {
        $log_content = "--- EMAIL ENVOYÉ LE " . date('Y-m-d H:i:s') . " ---\nPOUR : $to\nSUJET : $subject\nMESSAGE :\n$message\n\n";
        file_put_contents('emails_envoyes.txt', $log_content, FILE_APPEND);
    } else {
        mail($to, $subject, $message, $headers);
    }

    // --- REDIRECTION WHATSAPP ---
    $wa_msg = "Bonjour Oz Barber.\n\nJe confirme ma réservation.\n\n*CODE:* $booking_id\n*Nom:* $nom\n*Service:* " . $service['nom'] . "\n*Date:* Le $date à $heure\n*Lieu:* $lieu\n\n👉 *Je souhaite payer l'acompte de " . number_format($acompte, 0, ',', ' ') . " FCFA.*";
    
    header("Location: https://wa.me/237659259972?text=" . urlencode($wa_msg));
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Sécurisé | Oz Barber</title>
    <link rel="stylesheet" href="css/style.css?v=17">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f4f4f4; color: #333; font-family: 'Montserrat', sans-serif; }
        .payment-card {
            background: white; max-width: 450px; margin: 40px auto; 
            padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border-top: 5px solid #d4af37;
            border-radius: 8px;
        }
        label { font-weight: bold; font-size: 0.9rem; display: block; margin-top: 15px; margin-bottom: 5px; }
        input, select { width: 100%; padding: 15px; border: 2px solid #ddd; background: #fff; font-size: 1rem; box-sizing: border-box; border-radius: 5px; }
        
        /* Style spécial pour le groupe téléphone */
        .phone-group { display: flex; align-items: center; border: 2px solid #ddd; background: #fff; border-radius: 5px; }
        .phone-prefix { background-color: #eee; color: #555; padding: 15px; font-weight: bold; border-right: 1px solid #ddd; user-select: none; border-top-left-radius: 5px; border-bottom-left-radius: 5px; }
        .phone-group input { border: none !important; margin: 0 !important; flex: 1; }
        .phone-group input:focus { outline: none; }
        
        /* Validation visuelle */
        .phone-group.invalid-group { border-color: red; background-color: #ffe6e6; }
        .phone-group.valid-group { border-color: green; background-color: #e6ffeb; }
        
        .error-msg { color: red; font-size: 0.8rem; margin-top: 5px; display: none; }
        
        .btn-luxe { 
            cursor: pointer; text-align: center; width: 100%; 
            background: #d4af37; color: #000; border: none; padding: 15px; 
            font-weight: bold; text-transform: uppercase; margin-top: 20px; 
            transition: 0.3s;
        }
        .btn-luxe:hover { background: #000; color: #d4af37; }
        .btn-luxe:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
</head>
<body>

    <div class="payment-card">
        <div style="text-align: center;">
            <img src="img/logo.png" width="80">
            <h2 style="margin: 10px 0;">Finalisation</h2>
            <p style="color: #666;">Service : <strong><?php echo htmlspecialchars($service['nom']); ?></strong></p>
        </div>

        <div style="background: #000; color: #d4af37; padding: 15px; border-radius: 5px; text-align: center; margin: 20px 0;">
            <span style="font-size: 0.9rem; color: #fff;">Acompte à régler :</span><br>
            <span style="font-size: 1.8rem; font-weight: bold;"><?php echo number_format($acompte, 0, ',', ' '); ?> FCFA</span>
        </div>

        <form method="POST" id="bookingForm" onsubmit="return validateForm()">
            
            <label>📍 Lieu du Rendez-vous</label>
            <select name="lieu">
                <?php foreach($lieux as $l): ?>
                    <option value="<?php echo htmlspecialchars($l['nom']); ?>"><?php echo htmlspecialchars($l['nom']); ?></option>
                <?php endforeach; ?>
            </select>

            <label>👤 Nom Complet</label>
            <input type="text" name="nom" required placeholder="Ex: Donald">

            <label>📞 Numéro (MTN ou Orange)</label>
            <!-- GROUPE TELEPHONE FIXE +237 -->
            <div class="phone-group" id="phoneGroup">
                <span class="phone-prefix">+237</span>
                <input type="tel" id="phoneInput" name="tel" required placeholder="699 00 00 00" maxlength="9">
            </div>
            <div id="phoneError" class="error-msg">❌ Le numéro doit commencer par 6 (9 chiffres).</div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div>
                    <label>📅 Date</label>
                    <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div>
                    <label>⏰ Heure</label>
                    <input type="time" name="heure" required min="09:00" max="20:00">
                </div>
            </div>

            <button type="submit" class="btn-luxe">
                CONTINUER SUR WHATSAPP
            </button>
            <p style="text-align: center; margin-top: 15px; font-size: 0.8rem; color: #888;">🔒 Paiement finalisé avec le gérant</p>
        </form>
    </div>

    <!-- SCRIPT DE VALIDATION JS -->
    <script>
        const phoneInput = document.getElementById('phoneInput');
        const phoneGroup = document.getElementById('phoneGroup');
        const phoneError = document.getElementById('phoneError');
        const submitBtn = document.querySelector('button[type="submit"]');

        // Regex stricte Cameroun : commence par 6, suivi de 8 chiffres
        const cmrPhoneRegex = /^6[0-9]{8}$/;

        phoneInput.addEventListener('input', function() {
            // On enlève les espaces pour tester
            const val = this.value.replace(/\s/g, ''); 
            
            if (cmrPhoneRegex.test(val)) {
                // C'est valide
                phoneGroup.classList.remove('invalid-group');
                phoneGroup.classList.add('valid-group');
                phoneError.style.display = 'none';
                submitBtn.style.opacity = '1';
                submitBtn.disabled = false;
            } else {
                // Invalide
                phoneGroup.classList.remove('valid-group');
                phoneGroup.classList.add('invalid-group');
                phoneError.style.display = 'block';
                submitBtn.style.opacity = '0.5';
                submitBtn.disabled = true;
            }
        });

        function validateForm() {
            const val = phoneInput.value.replace(/\s/g, '');
            if (!cmrPhoneRegex.test(val)) {
                alert("Numéro incorrect. Vérifiez qu'il commence par 6 et contient 9 chiffres.");
                return false;
            }
            return true;
        }
    </script>

</body>
</html>