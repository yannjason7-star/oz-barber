<?php
// includes/db.php

$host = "localhost";
$username = "root"; // Par défaut sur XAMPP
$password = "";     // Par défaut vide sur XAMPP
$dbname = "salon_db";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // On veut voir les erreurs SQL si elles arrivent
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("<h3>Erreur de connexion :</h3> " . $e->getMessage());
}
?>