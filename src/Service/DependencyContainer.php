<?php
// src/Service/DependencyContainer.php

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

/**
 * Container d'injection de dépendances.
 */
class DependencyContainer
{
    /**
     * @var array<string, mixed> Tableau des instances des services
     */
    private array $instances = [];

    /**
     * @var Environment|null Instance de la classe Twig
     */
    private ?Environment $twig = null;

    public function __construct() {}

    /**
     * Récupère une instance du service demandé.
     *
     * @param string $key Clé du service
     * @return mixed Instance du service
     */
    public function get(string $key)
    {
        if (!isset($this->instances[$key])) {
            $this->instances[$key] = $this->createInstance($key);
        }

        return $this->instances[$key];
    }

    /**
     * Crée une instance du service demandé.
     *
     * @param string $key Clé du service
     * @return mixed Instance du service
     */
    private function createInstance(string $key)
    {
        switch ($key) {
            case 'PDO':
                return $this->createPDOInstance();
            case 'Twig':
                if ($this->twig === null) {
                    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../templates');
                    $this->twig = new Environment($loader);
                }
                return $this->twig;
            case 'EmailService':
                $twig = $this->get('Twig');
                assert($twig instanceof Environment);
                return new Mailer($twig);
            case 'UserModel':
                return new UserModel($this->getPDOInstance());
            case 'SectionModel':
                return new SectionModel($this->getPDOInstance());
            case 'SchoolClassModel':
                return new SchoolClassModel($this->getPDOInstance());
            case 'AssignmentModel':
                return new AssignmentModel($this->getPDOInstance());
            case 'FileModel':
                return new FileModel($this->getPDOInstance());
            default:
                throw new \Exception("No service found for key: " . $key);
        }
    }

    private function createPDOInstance(): PDO
    {
        try {
            $host = $_ENV['DATABASE_HOST'] ?? '';
            $port = $_ENV['DATABASE_PORT'] ?? '';
            $name = $_ENV['DATABASE_NAME'] ?? '';
            $user = $_ENV['DATABASE_USER'] ?? '';
            $password = $_ENV['DATABASE_PASSWORD'] ?? '';

            if (!is_string($host) || !is_string($port) || !is_string($name) || !is_string($user) || !is_string($password)) {
                throw new \InvalidArgumentException("Database environment variables must be strings.");
            }

            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8', $host, $port, $name);

            $pdo = new PDO($dsn, $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            throw new \Exception("PDO connection error: " . $e->getMessage());
        }
    }

    private function getPDOInstance(): PDO
    {
        $pdo = $this->get('PDO');
        assert($pdo instanceof PDO);
        return $pdo;
    }
}
