<?php
require_once 'includes/db.php';

// On récupère l'ID de la formation
$service_id = isset($_GET['service_id']) ? $_GET['service_id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

// Si le service n'existe pas ou n'est pas une formation, retour accueil
if (!$service) { header("Location: index.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST['nom']);
    $tel_suffix = htmlspecialchars($_POST['tel']);
    $tel_full = "+237 " . $tel_suffix;
    $ville = htmlspecialchars($_POST['ville']);
    $niveau = htmlspecialchars($_POST['niveau']);
    $motivation = htmlspecialchars($_POST['motivation']);
    
    // On combine les infos dans le champ "message" pour la BDD
    $details_candidat = "Ville: $ville | Niveau: $niveau | Motivation: $motivation";
    $date_demande = date('Y-m-d'); // Date du jour de la demande

    // Validation du numéro
    if (!preg_match("/^6[0-9]{8}$/", $tel_suffix)) {
        die("❌ Numéro invalide. <a href='javascript:history.back()'>Retour</a>");
    }

    $ref_dossier = "ACADEMY-" . rand(100, 999);

    // Enregistrement en BDD (Sans date de RDV précise, on met la date du jour)
    $stmt = $pdo->prepare("INSERT INTO reservations (client_nom, client_tel, service_id, lieu, date_rdv, heure_rdv, message, statut_paiement) VALUES (?, ?, ?, ?, ?, '00:00', ?, 'CANDIDATURE')");
    $stmt->execute([$nom, $tel_full, $service_id, $ville, $date_demande, $details_candidat]);

    // Simulation Email Manager
    $subject = "📄 CANDIDATURE FORMATION ($ref_dossier)";
    $msg = "Candidat: $nom\nTel: $tel_full\nNiveau: $niveau\nVille: $ville\nMotivation: $motivation";
    if ($_SERVER['SERVER_NAME'] == 'localhost') {
        file_put_contents('emails_envoyes.txt', "--- CANDIDATURE ---\n$msg\n\n", FILE_APPEND);
    }

    // Redirection WhatsApp (Message Pro)
    $wa_msg = "Bonjour l'équipe Oz Barber Academy.\nJe souhaite postuler pour la *formation coiffure*.\n\n📄 *Dossier N°:* $ref_dossier\n👤 *Nom:* $nom\n📍 *Ville:* $ville\n✂️ *Niveau:* $niveau\n\n_En attente de votre retour pour les modalités._";
    
    header("Location: https://wa.me/237659259972?text=" . urlencode($wa_msg));
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Académie Oz Barber | Candidature</title>
    <link rel="stylesheet" href="css/style.css?v=15">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #111; color: #fff; font-family: 'Montserrat', sans-serif; }
        .academy-card {
            background: #1a1a1a; max-width: 600px; margin: 40px auto; 
            padding: 40px; border: 1px solid #333; border-top: 5px solid #d4af37;
            box-shadow: 0 0 50px rgba(212, 175, 55, 0.1);
        }
        h2 { color: #d4af37; text-align: center; text-transform: uppercase; letter-spacing: 2px; }
        label { display: block; margin-top: 20px; color: #aaa; font-size: 0.9rem; margin-bottom: 5px; }
        input, select, textarea { 
            width: 100%; padding: 15px; background: #000; border: 1px solid #444; 
            color: #fff; font-family: inherit; box-sizing: border-box; 
        }
        input:focus, textarea:focus { outline: none; border-color: #d4af37; }
        
        /* Groupe téléphone spécifique */
        .phone-group { display: flex; align-items: center; border: 1px solid #444; background: #000; }
        .phone-prefix { background: #222; color: #888; padding: 15px; border-right: 1px solid #444; }
        .phone-group input { border: none; margin: 0; }
        
        .btn-gold { 
            width: 100%; background: #d4af37; color: #000; border: none; 
            padding: 15px; font-weight: bold; text-transform: uppercase; 
            margin-top: 30px; cursor: pointer; transition: 0.3s; 
        }
        .btn-gold:hover { background: #fff; }
    </style>
</head>
<body>

    <div class="academy-card">
        <div style="text-align: center; margin-bottom: 30px;">
            <img src="img/logo.png" width="100" style="border-radius: 50%; border: 2px solid #d4af37; padding: 5px;">
        </div>

        <h2>Dossier d'Admission</h2>
        <p style="text-align: center; color: #888; margin-bottom: 30px;">
            Rejoignez l'élite de la coiffure. Remplissez ce formulaire pour entrer en contact avec le formateur.
        </p>

        <form method="POST" id="academyForm">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label>Nom & Prénom</label>
                    <input type="text" name="nom" required placeholder="Votre nom complet">
                </div>
                <div>
                    <label>Ville de résidence</label>
                    <input type="text" name="ville" required placeholder="Ex: Douala">
                </div>
            </div>

            <label>Téléphone (WhatsApp Actif)</label>
            <div class="phone-group" id="phoneGroup">
                <span class="phone-prefix">+237</span>
                <input type="tel" id="phoneInput" name="tel" required placeholder="699 00 00 00" maxlength="9">
            </div>
            <div id="phoneError" style="color: red; font-size: 0.8rem; display: none; margin-top: 5px;">❌ Numéro invalide (doit commencer par 6)</div>

            <label>Votre niveau actuel en coiffure</label>
            <select name="niveau">
                <option value="Débutant Total">Je n'ai jamais coiffé (Débutant)</option>
                <option value="Intermédiaire">Je coiffe un peu (Intermédiaire)</option>
                <option value="Confirmé">Je suis déjà coiffeur (Perfectionnement)</option>
            </select>

            <label>Pourquoi voulez-vous rejoindre Oz Academy ?</label>
            <textarea name="motivation" rows="4" placeholder="Expliquez brièvement votre projet..."></textarea>

            <button type="submit" class="btn-gold" id="submitBtn">
                ENVOYER MA CANDIDATURE
            </button>
            
            <p style="text-align: center; font-size: 0.8rem; color: #555; margin-top: 20px;">
                * Coût de la formation : 50 000 FCFA (Modalités à voir avec le gérant)
            </p>
        </form>
    </div>

    <script>
        const phoneInput = document.getElementById('phoneInput');
        const phoneError = document.getElementById('phoneError');
        const submitBtn = document.getElementById('submitBtn');
        const cmrPhoneRegex = /^6[0-9]{8}$/;

        phoneInput.addEventListener('input', function() {
            const val = this.value.replace(/\s/g, '');
            if (cmrPhoneRegex.test(val)) {
                phoneError.style.display = 'none';
                this.style.color = '#fff';
                submitBtn.style.opacity = '1';
                submitBtn.disabled = false;
            } else {
                phoneError.style.display = 'block';
                this.style.color = 'red';
                submitBtn.style.opacity = '0.5';
                submitBtn.disabled = true;
            }
        });
    </script>

</body>
</html>