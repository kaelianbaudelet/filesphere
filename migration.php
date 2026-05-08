<?php
// migration.php

use Dotenv\Dotenv;

require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "Migration de la base de données\n";

try {

    echo "Connexion à la base de données...\n";
    $host = env('DATABASE_HOST', 'db');
    $port = env('DATABASE_PORT', '3306');
    $dbname = env('DATABASE_NAME');
    $user = env('DATABASE_USER');
    $password = env('DATABASE_PASSWORD');

    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $password);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Récupération du fichier SQL 'database.sql'...\n";

    $sql = file_get_contents('./database.sql');

    if ($sql === false) {
        die("Error reading SQL file");
    }

    echo "Migration de la base de données...\n";
    $conn->exec($sql);
    echo "La migration de la base de données a été effectuée avec succès.\n";
} catch (PDOException $e) {
    echo "La migration de la base de données a échoué.\n";
    echo "Erreur: " . $e->getMessage() . "\n";
}
$conn = null;
