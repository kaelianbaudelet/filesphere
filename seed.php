<?php

require 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Seeding de la base de données\n";

$host = $_ENV['DATABASE_HOST'] ?? 'db';
$dbname = $_ENV['DATABASE_NAME'] ?? 'livrable';
$username = $_ENV['DATABASE_USER'] ?? 'livrable';
$password = $_ENV['DATABASE_PASSWORD'] ?? 'livrable';

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie à la base de données\n";
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("TRUNCATE TABLE AssignmentInstructionFile");
$pdo->exec("TRUNCATE TABLE AssignmentFile");
$pdo->exec("TRUNCATE TABLE SectionAssignment");
$pdo->exec("TRUNCATE TABLE ClassSection");
$pdo->exec("TRUNCATE TABLE ClassStudent");
$pdo->exec("TRUNCATE TABLE Assignment");
$pdo->exec("TRUNCATE TABLE Section");
$pdo->exec("TRUNCATE TABLE Class");
$pdo->exec("TRUNCATE TABLE User");
$pdo->exec("TRUNCATE TABLE File");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

// Fake data lists
$firstNames = [
    'Jean',
    'Marie',
    'Pierre',
    'Sophie',
    'Thomas',
    'Emma',
    'Lucas',
    'Chloé',
    'Louis',
    'Léa',
    'Hugo',
    'Camille',
    'Maxime',
    'Sarah',
    'Alexandre',
    'Julie',
    'Antoine',
    'Juliette',
    'Nicolas',
    'Manon'
];
$lastNames = [
    'Martin',
    'Bernard',
    'Dubois',
    'Thomas',
    'Robert',
    'Richard',
    'Petit',
    'Durand',
    'Leroy',
    'Moreau',
    'Simon',
    'Laurent',
    'Lefebvre',
    'Michel',
    'Garcia',
    'David',
    'Bertrand',
    'Roux',
    'Vincent',
    'Fournier'
];

$classNames = [
    'Mathématiques' => ['Algèbre', 'Géométrie', 'Statistiques', 'Calcul différentiel', 'Trigonométrie'],
    'Physique' => ['Mécanique', 'Électromagnétisme', 'Thermodynamique', 'Optique', 'Physique quantique'],
    'Chimie' => ['Chimie organique', 'Chimie inorganique', 'Biochimie', 'Chimie analytique', 'Chimie physique'],
    'Biologie' => ['Botanique', 'Zoologie', 'Génétique', 'Écologie', 'Microbiologie'],
    'Informatique' => ['Programmation', 'Algorithmes', 'Base de données', 'Réseaux', 'Intelligence artificielle']
];

$assignmentTitles = [
    'Exercices pratiques',
    'Dissertation',
    'Projet de recherche',
    'Présentation orale',
    'Analyse de documents',
    'Étude de cas',
    'Résumé de lecture',
    'Devoir maison',
    'Contrôle de connaissances',
    'Expérience laboratoire'
];

$assignmentDescriptions = [
    'Résoudre les problèmes suivants et détailler les étapes de résolution.',
    'Rédiger une dissertation structurée sur le sujet donné avec des exemples pertinents.',
    'Mener une recherche approfondie et présenter vos résultats dans un rapport détaillé.',
    'Préparer une présentation de 10 minutes sur le sujet assigné avec support visuel.',
    'Analyser les documents fournis et en extraire les informations principales.'
];

$roles = ['student', 'teacher', 'admin'];
$studentCount = 0;
$totalUsers = 100;

echo "Création de $totalUsers utilisateurs aléatoires...\n";

for ($i = 1; $i <= $totalUsers; $i++) {
    $firstName = $firstNames[array_rand($firstNames)];
    $lastName = $lastNames[array_rand($lastNames)];
    $name = $firstName . ' ' . $lastName;
    $email = strtolower($firstName . '.' . $lastName . $i . '@exemple.com');
    $password = password_hash('password123', PASSWORD_DEFAULT);

    if ($studentCount < 50 && $i > $totalUsers - (50 - $studentCount)) {
        $role = 'student';
        $studentCount++;
    } else {
        $role = ($studentCount < 50) ? $roles[array_rand($roles)] : $roles[array_rand(array_diff($roles, ['student']))];
        if ($role == 'student') {
            $studentCount++;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO User (name, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
    $stmt->execute([$name, $email, $password, $role]);
}

echo "Création des utilisateurs spécifiques...\n";

$specificUsers = [
    ['admin@exemple.com', 'Admin User', 'admin'],
    ['student@exemple.com', 'Student User', 'student'],
    ['teacher@exemple.com', 'Teacher User', 'teacher']
];

foreach ($specificUsers as $user) {
    $password = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO User (email, password, name, role, is_active) VALUES (?, ?, ?, ?, 1)");
    $stmt->execute([$user[0], $password, $user[1], $user[2]]);
}

$stmt = $pdo->query("SELECT id FROM User WHERE role = 'student'");
$studentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->query("SELECT id FROM User WHERE role = 'teacher' LIMIT 1");
$teacherId = $stmt->fetchColumn();

foreach ($classNames as $className => $sections) {
    $color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);

    $classId = uniqid();
    $stmt = $pdo->prepare("INSERT INTO Class (id, name, color, teacher_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$classId, $className, $color, $teacherId]);

    $randomStudents = array_rand($studentIds, min(5, count($studentIds)));
    if (!is_array($randomStudents)) {
        $randomStudents = [$randomStudents];
    }

    foreach ($randomStudents as $studentKey) {
        $studentId = $studentIds[$studentKey];
        $stmt = $pdo->prepare("INSERT INTO ClassStudent (user_id, class_id) VALUES (?, ?)");
        $stmt->execute([$studentId, $classId]);
    }

    foreach ($sections as $sectionName) {
        $sectionId = uniqid();
        $stmt = $pdo->prepare("INSERT INTO Section (id, name) VALUES (?, ?)");
        $stmt->execute([$sectionId, $sectionName]);

        $stmt = $pdo->prepare("INSERT INTO ClassSection (section_id, class_id) VALUES (?, ?)");
        $stmt->execute([$sectionId, $classId]);

        for ($i = 0; $i < 3; $i++) {
            $title = $assignmentTitles[array_rand($assignmentTitles)];
            $description = $assignmentDescriptions[array_rand($assignmentDescriptions)];

            $startDate = date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'));
            $endDate = date('Y-m-d H:i:s', strtotime('+' . rand(1, 30) . ' days'));

            $assignmentId = uniqid();
            $stmt = $pdo->prepare("INSERT INTO Assignment (id, name, description, start_period, end_period, max_files, allow_late_submission) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$assignmentId, $title, $description, $startDate, $endDate, rand(1, 5), rand(0, 1)]);

            $stmt = $pdo->prepare("INSERT INTO SectionAssignment (section_id, assignment_id) VALUES (?, ?)");
            $stmt->execute([$sectionId, $assignmentId]);
        }
    }
}

echo "Seeding terminé avec succès!\n";
