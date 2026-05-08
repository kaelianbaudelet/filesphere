<?php
// seed.php

require 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "Seeding de la base de données\n";

try {
    $host = env('DATABASE_HOST', 'db');
    $dbname = env('DATABASE_NAME', 'livrable');
    $username = env('DATABASE_USER', 'livrable');
    $password = env('DATABASE_PASSWORD', 'livrable');
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie à la base de données\n";
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
// No TRUNCATE in production to preserve data
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

    // Vérifier si l'utilisateur existe déjà
    $checkStmt = $pdo->prepare("SELECT id FROM User WHERE email = ?");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        continue;
    }

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
    ['teacher@exemple.com', 'Teacher User', 'teacher'],
    ['admin@demo.fr', 'Démonstration Admin', 'admin'],
    ['prof@demo.fr', 'Démonstration Professeur', 'teacher'],
    ['eleve@demo.fr', 'Démonstration Élève', 'student']
];

foreach ($specificUsers as $user) {
    // Vérifier si l'utilisateur spécifique existe déjà
    $checkStmt = $pdo->prepare("SELECT id FROM User WHERE email = ?");
    $checkStmt->execute([$user[0]]);
    if ($checkStmt->fetch()) {
        continue;
    }

    $password = password_hash($user[0] === 'admin@demo.fr' ? 'admin' : ($user[0] === 'prof@demo.fr' ? 'prof' : ($user[0] === 'eleve@demo.fr' ? 'eleve' : 'password123')), PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO User (email, password, name, role, is_active) VALUES (?, ?, ?, ?, 1)");
    $stmt->execute([$user[0], $password, $user[1], $user[2]]);
}

// Récupérer les IDs des comptes de démo
$stmt = $pdo->prepare("SELECT id FROM User WHERE email = ?");
$stmt->execute(['prof@demo.fr']);
$demoTeacherId = $stmt->fetchColumn();

$stmt->execute(['eleve@demo.fr']);
$demoStudentId = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT id FROM User WHERE role = 'student'");
$studentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->query("SELECT id FROM User WHERE role = 'teacher' AND email != 'prof@demo.fr' LIMIT 1");
$defaultTeacherId = $stmt->fetchColumn();

$index = 0;
foreach ($classNames as $className => $sections) {
    // Vérifier si la classe existe déjà
    $checkStmt = $pdo->prepare("SELECT id FROM Class WHERE name = ?");
    $checkStmt->execute([$className]);
    $existingClass = $checkStmt->fetch();
    
    if ($existingClass) {
        $classId = $existingClass['id'];
        
        // Si c'est une démo, on force l'assignation du prof de démo sur certaines classes existantes
        if ($index % 2 === 0 && $demoTeacherId) {
            $stmt = $pdo->prepare("UPDATE Class SET teacher_id = ? WHERE id = ?");
            $stmt->execute([$demoTeacherId, $classId]);
        }
        
        // On s'assure que l'élève de démo est dans la classe
        if ($index % 2 === 0 && $demoStudentId) {
            $checkAssoc = $pdo->prepare("SELECT 1 FROM ClassStudent WHERE user_id = ? AND class_id = ?");
            $checkAssoc->execute([$demoStudentId, $classId]);
            if (!$checkAssoc->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO ClassStudent (user_id, class_id) VALUES (?, ?)");
                $stmt->execute([$demoStudentId, $classId]);
            }
        }
    } else {
        $color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        $classId = uniqid();
        
        // Assigner certaines classes au prof de démo
        $currentTeacherId = ($index % 2 === 0 && $demoTeacherId) ? $demoTeacherId : ($defaultTeacherId ?: $demoTeacherId);
        
        $stmt = $pdo->prepare("INSERT INTO Class (id, name, color, teacher_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$classId, $className, $color, $currentTeacherId]);

        $randomStudents = array_rand($studentIds, min(5, count($studentIds)));
        if (!is_array($randomStudents)) {
            $randomStudents = [$randomStudents];
        }

        // Ajouter l'élève de démo systématiquement à la moitié des classes
        if ($index % 2 === 0 && $demoStudentId && !in_array($demoStudentId, $randomStudents)) {
            $randomStudents[] = array_search($demoStudentId, $studentIds);
        }

        foreach ($randomStudents as $studentKey) {
            $studentId = $studentIds[$studentKey];
            if (!$studentId) continue;
            
            // Vérifier si l'association existe déjà
            $checkAssoc = $pdo->prepare("SELECT 1 FROM ClassStudent WHERE user_id = ? AND class_id = ?");
            $checkAssoc->execute([$studentId, $classId]);
            if (!$checkAssoc->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO ClassStudent (user_id, class_id) VALUES (?, ?)");
                $stmt->execute([$studentId, $classId]);
            }
        }
    }
    $index++;

    foreach ($sections as $sectionName) {
        // Vérifier si la section existe déjà dans cette classe
        $checkStmt = $pdo->prepare("SELECT s.id FROM Section s JOIN ClassSection cs ON s.id = cs.section_id WHERE s.name = ? AND cs.class_id = ?");
        $checkStmt->execute([$sectionName, $classId]);
        $existingSection = $checkStmt->fetch();

        if ($existingSection) {
            $sectionId = $existingSection['id'];
        } else {
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
}

echo "Seeding terminé avec succès!\n";
