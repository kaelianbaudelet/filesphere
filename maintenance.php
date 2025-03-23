<?php
// maintenance.php

$envFilePath = __DIR__ . '/.env';

try {
    if (!file_exists($envFilePath)) {
        throw new Exception('Le fichier .env n\'existe pas, veuillez créer un fichier .env.');
    }

    if (!is_readable($envFilePath)) {
        throw new Exception('Le fichier .env n\'est pas lisible, veuillez vérifier les autorisations du fichier .env.');
    }

    if (!is_writable($envFilePath)) {
        throw new Exception('Le fichier .env n\'est pas modifiable, veuillez vérifier les autorisations du fichier .env.');
    }
} catch (Exception $e) {
    die('Impossible de lire le fichier .env, assurez-vous que le fichier .env existe, qu\'il est lisible et modifiable.');
}

$envContent = file_get_contents($envFilePath);

if (preg_match('/^APP_MAINTENANCE=(.*)$/m', $envContent, $matches)) {
    $currentValue = trim($matches[1]);
    $newValue = ($currentValue === 'true') ? 'false' : 'true';
    $newEnvContent = preg_replace('/^APP_MAINTENANCE=.*$/m', "APP_MAINTENANCE=$newValue", $envContent);
    if (file_put_contents($envFilePath, $newEnvContent) === false) {
        die('Erreur lors de l\'écriture du fichier .env');
    }
    if ($newValue === 'true') {
        echo "\033[31m [!] L'application a été basculée en mode maintenance.\033[0m";
    } else {
        echo "\033[32m [!] L'application n'est plus en mode maintenance.\033[0m";
    }
} else {
    die('La clé APP_MAINTENANCE n\'a pas été trouvée dans le fichier .env, veuillez vérifier le contenu du fichier .env et ajouter la clé APP_MAINTENANCE si elle n\'existe pas.');
}
