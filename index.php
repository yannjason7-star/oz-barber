<?php
require_once 'includes/db.php';

// Récupération des services depuis la base de données
$stmt = $pdo->query("SELECT * FROM services ORDER BY prix ASC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oz Barber | L'Élégance Absolue</title>
    
    <!-- CSS avec versioning pour forcer la mise à jour du cache -->
    <link rel="stylesheet" href="css/style.css?v=16">
    
    <!-- Polices & Icones -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body>

    <!-- HERO SECTION (HEADER) -->
    <header>
        <div class="logo-container" data-aos="zoom-in" data-aos-duration="2000">
            <img src="img/logo.png" alt="Logo Luxe Oz Barber">
        </div>
        <h1 data-aos="fade-up" data-aos-delay="500">Oz Barber</h1>
        <p class="tagline" data-aos="fade-up" data-aos-delay="800">Complexe de Beauté & Esthétique Premium</p>
        <a href="#reservation" class="btn-luxe" data-aos="fade-up" data-aos-delay="1200">Prendre Rendez-vous</a>
    </header>

    <!-- SECTION PRÉSENTATION (FOND BLANC) -->
    <section class="bg-white">
        <div class="container">
            <h2 class="section-title" data-aos="fade-down">L'Art de Recevoir</h2>
            <p class="section-subtitle" data-aos="fade-up">Douala • Yaoundé • Logbessou</p>
            
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 50px;">
                <!-- Image de gauche -->
                <div style="flex: 1;" data-aos="fade-right" data-aos-duration="1500">
                    <img src="img/devanture.jpg" style="width: 100%; border-radius: 20px; box-shadow: 20px 20px 0 rgba(0,0,0,0.1);" alt="Devanture Salon">
                </div>
                
                <!-- Texte de droite -->
                <div style="flex: 1;" data-aos="fade-left" data-aos-duration="1500">
                    <h3 style="font-size: 2rem; margin-bottom: 20px; color: #111;">Une Expérience Unique</h3>
                    <p style="font-size: 1.1rem; line-height: 1.8; color: #555;">
                        Chez <strong>Oz Barber</strong>, nous ne coupons pas simplement des cheveux. Nous sculptons votre image.
                        Plongez dans un univers où le <strong>confort est roi</strong> : fauteuils en cuir, serviettes chaudes, et une équipe aux petits soins.
                        <br><br>
                        Que ce soit pour un <em>dégradé américain</em>, un soin du visage en profondeur ou une mise en beauté pour votre mariage, 
                        chaque geste est maîtrisé pour frôler la perfection.
                    </p>
                    <div style="margin-top: 30px; display: flex; gap: 20px;">
                        <span style="border: 1px solid #ddd; padding: 10px 20px; border-radius: 50px; background: #f9f9f9; color: #333;">❄️ Espace Climatisé</span>
                        <span style="border: 1px solid #ddd; padding: 10px 20px; border-radius: 50px; background: #f9f9f9; color: #333;">🎵 Ambiance Lounge</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION SERVICES & TARIFS (FOND NOIR) -->
    <section id="reservation" class="bg-black">
        <div class="container">
            <h2 class="section-title" data-aos="zoom-in">Carte des Soins</h2>
            <p class="section-subtitle">Tarification Transparente & Prestations Haute Couture</p>
            
            <div class="services-grid">
                <?php foreach($services as $index => $service): ?>
                    
                    <?php 
                    // --- LOGIQUE INTELLIGENTE ---
                    // Si le nom du service contient "Formation" (ex: Formation Pro)
                    $is_formation = (stripos($service['nom'], 'Formation') !== false); 
                    
                    if ($is_formation) {
                        // C'est une formation : Bouton Doré + Lien vers Candidature
                        $btn_text = "CANDIDATER →";
                        $link = "formation.php?service_id=" . $service['id'];
                        $link_style = "color: #d4af37; border-bottom: 1px solid #d4af37; letter-spacing: 2px;";
                    } else {
                        // C'est un soin normal : Bouton Classique + Lien vers Paiement
                        $btn_text = "RÉSERVER CE CRÉNEAU →";
                        $link = "reservation.php?service_id=" . $service['id'];
                        $link_style = "color: #fff; border-bottom: 1px solid #555;";
                    }
                    ?>

                    <!-- Carte Service -->
                    <div class="service-item" data-aos="flip-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="service-top">
                            <span class="service-name"><?php echo htmlspecialchars($service['nom']); ?></span>
                            <span class="service-price"><?php echo number_format($service['prix'], 0, ',', ' '); ?> FCFA</span>
                        </div>
                        
                        <p class="service-desc"><?php echo htmlspecialchars($service['description']); ?></p>
                        
                        <div style="text-align: right; margin-top: 25px;">
                            <a href="<?php echo $link; ?>" class="btn-small-book" style="<?php echo $link_style; ?>">
                                <?php echo $btn_text; ?>
                            </a>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- SECTION GALERIE (FOND BLANC) -->
    <section id="galerie" class="bg-white">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Portfolio</h2>
            <p class="section-subtitle">Notre Savoir-Faire en Images</p>

            <div class="gallery-wrapper">
                
                <!-- Photo 1 -->
                <div class="gallery-card" data-aos="fade-up" data-aos-delay="0">
                    <img src="img/coupe-homme.jpg" alt="Coiffure Homme">
                    <div class="gallery-overlay">
                        <div class="gallery-title">Architecture Capillaire</div>
                        <div class="gallery-text">Des contours tracés au millimètre.</div>
                    </div>
                </div>

                <!-- Photo 2 -->
                <div class="gallery-card" data-aos="fade-up" data-aos-delay="200">
                    <img src="img/soin-vapeur.jpg" alt="Soin Visage Vapeur">
                    <div class="gallery-overlay">
                        <div class="gallery-title">Rituel Vapeur</div>
                        <div class="gallery-text">Nettoyage profond et relaxation (3000 FCFA).</div>
                    </div>
                </div>

                <!-- Photo 3 -->
                <div class="gallery-card" data-aos="fade-up" data-aos-delay="400">
                    <img src="img/degrade-pro.jpg" alt="Dégradé Homme">
                    <div class="gallery-overlay">
                        <div class="gallery-title">Signature Oz</div>
                        <div class="gallery-text">Le dégradé à blanc maîtrisé.</div>
                    </div>
                </div>

                <!-- Photo 4 -->
                <div class="gallery-card" data-aos="fade-up" data-aos-delay="600">
                    <img src="img/produits.jpg" alt="Produits de soin">
                    <div class="gallery-overlay">
                        <div class="gallery-title">Sélection Premium</div>
                        <div class="gallery-text">Les meilleures marques mondiales.</div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- FOOTER LUXE (FOND NOIR) -->
    <footer>
        <div class="footer-content" data-aos="fade-up">
            <img src="img/logo.png" alt="Oz Barber Logo" class="footer-logo">
            
            <h4>OZ BARBER</h4>
            <p style="font-style: italic; color: #888;">Luxury Standard • Cameroun</p>
            
            <div class="footer-divider"></div>
            
            <p>DOUALA (BALI, LOGBESSOU) • YAOUNDÉ (BASTOS)</p>
            <p style="font-size: 1.2rem; margin-top: 15px;">
                Service Client & Réservation : <br>
                <strong style="color: #d4af37;">(+237) 659 259 972</strong>
            </p>

            <p class="copyright">
                © 2026 Oz Barber Group. Tous droits réservés.
            
               <a href="admin/" style="color: #333; text-decoration: none; margin-left: 10px;">🔒</a>
            </p>
        </div>
    </footer>

    <!-- SCRIPTS POUR ANIMATION -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000, /* Durée des animations */
            once: false,    /* L'animation rejoue si on remonte */
            mirror: true
        });
    </script>
</body>
</html>