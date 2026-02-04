# FileSphere

## Projet Transversal TPWR101 — EPSI SN1 — Développement d'une solution Web

![preview](https://github.com/user-attachments/assets/608d9a57-bc3e-4467-90f7-65e538926b1d)


## Description du projet

Ce projet a pour objectif de concevoir une plateforme web fictive dédiée à la gestion des livrables pour l'EPSI. Les professeurs et intervenants peuvent créer des devoirs, tandis que les élèves peuvent soumettre leurs rendus en ligne. Les administrateurs disposent d’outils pour gérer les utilisateurs, les classes, les fichiers et les devoirs, assurant ainsi une organisation efficace et centralisée.

## Fonctionnalités

- Authentification :
  - Inscription et connexion
  - Récupération de mot de passe
  - Modification du mot de passe utilisateur
- Gestion des utilisateurs :
  - Création, modification et suppression des utilisateurs
  - Attribution des rôles (élève, professeur, administrateur)
- Gestion des classes et des sections :
  - Création, modification et suppression des classes
  - Attribution des élèves et professeurs aux classes
  - Création de sections pour regrouper les devoirs dans une classe
- Gestion des devoirs et livrables :
  - Création et attribution de devoirs par les professeurs
  - Dépôt des fichiers par les élèves
  - Date limite et suivi des soumissions
- Gestion des fichiers :
  - Stockage et gestion des fichiers soumis
  - Téléchargement des livrables

## Technologies utilisées

- Php
- Composer
- Twig
- PhpDotEnv
- PHPMailer
- Symphony ErrorHandler
- Docker

## Prérequis

Avant d'installer le projet, assurez-vous d'avoir :

- PHP 8.x ou supérieur
- Composer
- Docker (optionnel mais recommandé)
- Une base de données MySQL ou MariaDB

## Installation

1. Commencez par cloner le dépôt:

    ```bash
    git clone https://github.com/kaelianbaudelet/filesphere.git
    ```

2. Naviguez jusqu'au répertoire du projet :

    ```bash
    cd filesphere
    ```

3. Installer les dépendances :

    - Commencer par installer les dépendances du projet avec :

        ```bash
        composer prepare-dev
        ```

4. Configurer votre environnement de travail pour développé

    Vous avez plusieurs possibilité :

    - **Recommandé :** Environnement de travail avec [Docker](https://docs.docker.com/engine/install/) :

        - Installez [Docker](https://docs.docker.com/engine/install/) et [Docker Compose](https://docs.docker.com/compose/install/).
        - Après l'installation de [Docker](https://docs.docker.com/engine/install/) et [Docker Compose](https://docs.docker.com/compose/install/), configurez votre environnement en copiant le fichier `.env.exemple`, en le renommant en `.env`, et en le configurant avec les valeurs nécessaires.
        - Enfin, créez l'environnement de travail avec :

            ```bash
            docker compose --profile dev up -d
            ```

            L'environnement de travail pour le développement contient :
                - Une base de données [MariaDB](https://mariadb.org/).
                - Un serveur [Adminer](https://www.adminer.org/) pour administrer votre base de données. (Accesible via `localhost:9999`)
                - L'application web configurée avec [Apache2](https://httpd.apache.org/). (Accesible via `localhost:9999`)

    - Environnement [WAMP](https://www.wampserver.com)/[XAMPP](https://www.apachefriends.org/fr/index.html) :

        - Installer [WAMP](https://www.wampserver.com) ou [XAMPP](https://www.apachefriends.org/fr/index.html)
        - Déplacer le projet dans le repertoire serveur [WAMP](https://www.wampserver.com) ou [XAMPP](https://www.apachefriends.org/fr/index.html)
        - Pour configurer votre environnement, copiez le fichier `.env.exemple`, renommez-le en `.env`, puis renseignez les valeurs nécessaires en fonction de votre configuration.
        - Démarrer [WAMP](https://www.wampserver.com) ou [XAMPP](https://www.apachefriends.org/fr/index.html)

    - Sans environnement de travail :

        - Copier le fichier `.env.exemple`, renommer-le en `.env`, et configurer celui ci avec les valeurs souhaitées.
        - Démarrer un serveur php de développement avec:

            ```bash
            composer start
            ```

5. Migrer la base de données :

> [!IMPORTANT]  
> Après avoir installer l'application, vous devez impérativement migrer la base de données.

Migrée la base de données avec la commande suivante :
```bash
php migration.php
```

**BONUS:** Créer automatiquement des exemples de fausses données en base de données (utilisateurs, classes, devoirs) avec la commande suivante :
```bash
php seed.php
```

6. Création d'un utilisateur administrateur :

> [!IMPORTANT]  
> Par défaut, l'application ne contient aucun utilisateur et l'application n'autorise pas l'inscription d'un utilisateur administrateur par défaut. vous devez donc activer le mode d'inscription d'utilisateur.

Pour activer le mode d'inscription d'utilisateurs vous devez modifier le fichier `.env` et définir la variable `TEMP_REGISTER` à `true`.

Accèder à localhost:80/register pour créer un utilisateur.

> [!CAUTION]
> Pour des raisons évidentes de sécurité, une fois l'utilisateur créé, vous devez désactiver le mode d'inscription d'utilisateur en définissant la variable `TEMP_REGISTER` à `false` dans le fichier `.env`.

## Mise en production

Pour mettre en production l'application, assurez-vous d'utiliser proxy inversé tel que [Apache2](https://httpd.apache.org/), [Nginx](https://www.nginx.com/), [Caddy](https://caddyserver.com/) ou encore [Traefik](https://traefik.io/).

> [!TIP]
> La solution recommandée est d’utiliser [Docker](https://docs.docker.com/engine/install/) et de déployer l’application derrière un proxy inversé comme [Traefik](https://traefik.io/).
>
> Pour plus de détails, consultez la documentation officielle de [Traefik](https://traefik.io/) afin d’apprendre à configurer et déployer une application avec ce proxy.


## Utilisation

1. Ouvrer votre navigateur et naviguer vers `http://localhost:80`.

## Licence

Ce projet est sous licence **[MIT](LICENSE)**.

## Contributeurs

- Kaëlian BAUDELET
- Annalia PRIEUR
- Florian GUILBERT
- Thibault ROYER
