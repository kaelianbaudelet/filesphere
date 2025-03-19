<?php
// Chemin vers le fichier .env
$envFilePath = __DIR__ . '/.env';

try {
    // Vérifier si le fichier .env existe
    if (!file_exists($envFilePath)) {
        throw new Exception('Le fichier .env n\'existe pas, veuillez créer un fichier .env.');
    }

    // Vérifier si le fichier .env est lisible
    if (!is_readable($envFilePath)) {
        throw new Exception('Le fichier .env n\'est pas lisible, veuillez vérifier les autorisations du fichier .env.');
    }

    // Vérifier si le fichier .env est modifiable
    if (!is_writable($envFilePath)) {
        throw new Exception('Le fichier .env n\'est pas modifiable, veuillez vérifier les autorisations du fichier .env.');
    }
} catch (Exception $e) {
    die('Impossible de lire le fichier .env, assurez-vous que le fichier .env existe, qu\'il est lisible et modifiable.');
}

// Lire le contenu du fichier .env
$envContent = file_get_contents($envFilePath);

// Rechercher la ligne contenant APP_MAINTENANCE
if (preg_match('/^APP_MAINTENANCE=(.*)$/m', $envContent, $matches)) {
    // Obtenir la valeur actuelle de APP_MAINTENANCE
    $currentValue = trim($matches[1]);

    // Déterminer la nouvelle valeur (opposée)
    $newValue = ($currentValue === 'true') ? 'false' : 'true';

    // Remplacer la valeur dans le contenu du fichier .env
    $newEnvContent = preg_replace('/^APP_MAINTENANCE=.*$/m', "APP_MAINTENANCE=$newValue", $envContent);

    // Écrire le nouveau contenu dans le fichier .env
    if (file_put_contents($envFilePath, $newEnvContent) === false) {
        die('Erreur lors de l\'écriture du fichier .env');
    }

    // Afficher un message indiquant l'état de l'application
    if ($newValue === 'true') {
        echo "\033[31m [!] L'application a été basculée en mode maintenance.\033[0m"; // Red text
    } else {
        echo "\033[32m [!] L'application n'est plus en mode maintenance.\033[0m"; // Green text
    }
} else {
    die('La clé APP_MAINTENANCE n\'a pas été trouvée dans le fichier .env, veuillez vérifier le contenu du fichier .env et ajouter la clé APP_MAINTENANCE si elle n\'existe pas.');
}
