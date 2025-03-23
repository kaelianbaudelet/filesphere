<?php
// index.php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Routing\Router;
use App\Extension\SessionExtension;
use App\Service\DependencyContainer;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\ErrorHandler\DebugClassLoader;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../', '.env');
$dotenv->load();

// ======================================================================
//  SESSION
// ======================================================================

// Crée une session si elle n'existe pas

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', $_ENV['SESSION_LIFETIME']); // Durée de vie maximale de la session
    ini_set('session.cookie_lifetime', $_ENV['SESSION_LIFETIME']); // Durée de vie maximale du cookie de session
    ini_set('session.cookie_secure', $_ENV['SESSION_COOKIE_SECURE']); // Le cookie de session doit être transmis via une connexion sécurisée
    session_start();
}

// ======================================================================
//  DEBUG
// ======================================================================

// Debug mode avec Symfony ErrorHandler

if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
    Debug::enable();
    ErrorHandler::register();
    DebugClassLoader::enable();
}

// ======================================================================
//  MODE MAINTENANCE
// ======================================================================

// Mode maintenance de l'application

if (!isset($_ENV['APP_MAINTENANCE'])) {
    die('The APP_MAINTENANCE key was not found in the .env file, please check the content of the .env file and add the APP_MAINTENANCE key if it does not exist.');
}

// ======================================================================
// APPLICATION
// ======================================================================

$container = new DependencyContainer();
$loader = new FilesystemLoader(__DIR__ . '/../views');
$twig = new Environment($loader);
$router = new Router($container);

// ======================================================================
//  EXTENSIONS & FONCTIONS TWIG
// ======================================================================

$twig->addGlobal('session', $_SESSION);
$twig->addExtension(new SessionExtension());

// dump : affiche un dump dans une balise <pre>

$twig->addFunction(new \Twig\TwigFunction('dump', function ($var) {
    dump($var);
}));

// vardump : affiche un var_dump dans une balise <pre>

$twig->addFunction(new \Twig\TwigFunction('vardump', function ($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}));

// env : retourne une variable d'environnement

$twig->addFunction(new \Twig\TwigFunction('env', function ($key) {
    return $_ENV[$key];
}));

// session : retourne une variable de session

$twig->addFunction(new \Twig\TwigFunction('session', function ($key) {
    return $_SESSION[$key];
}));

// asset : retourne le chemin d'un fichier dans le dossier assets

$twig->addFunction(new \Twig\TwigFunction('asset', function ($asset) {
    return sprintf('/assets/%s', ltrim($asset, '/'));
}));

// current_route : retourne la route actuelle

$twig->addFunction(new \Twig\TwigFunction('current_route', function () use ($router) {
    return $router->getCurrentRoute();
}));

// is_current_route : vérifie si la route actuelle est égale à la route passée en paramètre

$twig->addFunction(new \Twig\TwigFunction('is_current_route', function ($route) use ($router) {
    return $router->getCurrentRoute() === $route;
}));

// is_logged_in : vérifie si l'utilisateur est connecté

$twig->addFunction(new \Twig\TwigFunction('is_logged_in', function () {
    return isset($_SESSION['user']);
}));

// is_admin : vérifie si l'utilisateur est un administrateur

$twig->addFunction(new \Twig\TwigFunction('is_admin', function () {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}));

// is_teacher : vérifie si l'utilisateur est un enseignant

$twig->addFunction(new \Twig\TwigFunction('is_teacher', function () {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'teacher';
}));

// is_student : vérifie si l'utilisateur est un étudiant

$twig->addFunction(new \Twig\TwigFunction('is_student', function () {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'student';
}));

// preg_replace : remplace les occurences d'une chaine de caractères par une

$twig->addFilter(new \Twig\TwigFilter('preg_replace', function ($string, $pattern, $replacement) {
    return preg_replace($pattern, $replacement, $string);
}));

// random_string : retourne une chaine de caractères aléatoire

$twig->addFunction(new \Twig\TwigFunction('random_string', function ($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}));

// getTodayDate : retourne la date du jour

$twig->addFunction(new \Twig\TwigFunction('getTodayDate', function () {
    return new \DateTime();
}));

$router->route($twig);
