<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Routing\Router;
use App\Extension\SessionExtension;
use App\Service\DependencyContainer;


require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\ErrorHandler\DebugClassLoader;

Debug::enable();
ErrorHandler::register();
DebugClassLoader::enable();

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../', '.env');
$dotenv->load();

// verify if the APP_MAINTENANCE key exists in the .env file
if (!isset($_ENV['APP_MAINTENANCE'])) {
    die('The APP_MAINTENANCE key was not found in the .env file, please check the content of the .env file and add the APP_MAINTENANCE key if it does not exist.');
}

$container = new DependencyContainer();
$loader = new FilesystemLoader(__DIR__ . '/../views');

$twig = new Environment($loader);
$router = new Router($container);
$twig->addGlobal('session', $_SESSION);
$twig->addExtension(new SessionExtension());

$twig->addFunction(new \Twig\TwigFunction('dump', function ($var) {
    dump($var);
}));

$twig->addFunction(new \Twig\TwigFunction('vardump', function ($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}));


$twig->addFunction(new \Twig\TwigFunction('env', function ($key) {
    return $_ENV[$key];
}));

$twig->addFunction(new \Twig\TwigFunction('session', function ($key) {
    return $_SESSION[$key];
}));

$twig->addFunction(new \Twig\TwigFunction('asset', function ($asset) {
    return sprintf('/assets/%s', ltrim($asset, '/'));
}));

$twig->addFunction(new \Twig\TwigFunction('current_route', function () use ($router) {
    return $router->getCurrentRoute();
}));

$twig->addFunction(new \Twig\TwigFunction('is_current_route', function ($route) use ($router) {
    return $router->getCurrentRoute() === $route;
}));

$twig->addFunction(new \Twig\TwigFunction('is_logged_in', function () {
    return isset($_SESSION['user']);
}));

$twig->addFunction(new \Twig\TwigFunction('is_admin', function () {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}));

$twig->addFunction(new \Twig\TwigFunction('is_teacher', function () {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'teacher';
}));

$twig->addFunction(new \Twig\TwigFunction('is_student', function () {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'student';
}));

//preg_replace
$twig->addFilter(new \Twig\TwigFilter('preg_replace', function ($string, $pattern, $replacement) {
    return preg_replace($pattern, $replacement, $string);
}));

$twig->addFunction(new \Twig\TwigFunction('random_string', function ($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}));

$twig->addFunction(new \Twig\TwigFunction('getTodayDate', function () {
    return new \DateTime();
}));

$router->route($twig);
