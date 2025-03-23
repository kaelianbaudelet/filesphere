<?php
// src/Controller/DefaultController.php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;

use Twig\Environment;

class DefaultController
{
    /**
     * @var Twig Instance de la classe Twig
     */
    private \Twig\Environment $twig;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
    }

    public function home()
    {
        header('Location: /login');
    }

    public function error404()
    {
        echo $this->twig->render('defaultController/404.html.twig', []);
    }

    public function error403()
    {
        echo $this->twig->render('defaultController/403.html.twig', []);
    }

    public function error500()
    {
        echo $this->twig->render('defaultController/500.html.twig', []);
    }

    public function search()
    {
        echo $this->twig->render('defaultController/search.html.twig', [
            'arrival_date' => $_POST['arrival_date'] ?? '',
            'departure_date' => $_POST['departure_date'] ?? '',
            'number_of_guests' => $_POST['number_of_guests'] ?? '',
        ]);
    }
}
