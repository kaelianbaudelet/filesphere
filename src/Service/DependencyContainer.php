<?php

namespace App\Service;

use App\Model\UserModel;
use App\Model\FileModel;
use App\Model\SchoolClassModel;
use App\Model\SectionModel;
use App\Model\AssignmentModel;

use App\Service\Mailer;

use Twig\Environment;

use PDO;
use PDOException;

class DependencyContainer
{
    private $instances = [];
    private $twig;

    public function __construct() {}

    public function get($key)
    {
        if (!isset($this->instances[$key])) {
            $this->instances[$key] = $this->createInstance($key);
        }

        return $this->instances[$key];
    }

    private function createInstance($key)
    {
        switch ($key) {
            case 'PDO':
                return $this->createPDOInstance();
            case 'Twig':
                if (!$this->twig) {
                    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../templates');
                    $this->twig = new Environment($loader);
                }
                return $this->twig;
            case 'EmailService':
                return new Mailer($this->get('Twig'));
            case 'UserModel':
                $pdo = $this->get('PDO');
                return new UserModel($pdo);
            case 'SectionModel':
                $pdo = $this->get('PDO');
                return new SectionModel($pdo);
            case 'SchoolClassModel':
                $pdo = $this->get('PDO');
                return new SchoolClassModel($pdo);
            case 'AssignmentModel':
                $pdo = $this->get('PDO');
                return new AssignmentModel($pdo);
            case 'FileModel':
                $pdo = $this->get('PDO');
                return new FileModel($pdo);
            default:
                throw new \Exception("No service found for key: " . $key);
        }
    }

    private function createPDOInstance()
    {
        try {
            $pdo = new PDO('mysql:host=' . $_ENV['DATABASE_HOST'] . ';port=' . $_ENV['DATABASE_PORT'] . ';dbname=' .
                $_ENV['DATABASE_NAME'] . ';charset=utf8', $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            throw new \Exception("PDO connection error: " . $e->getMessage());
        }
    }
}
