# Projet Transversal Web — EPSI SN1 — Hotel Neptune

## Description du projet

Ce projet consiste en la création d'un site d'hotel fictif

## Fonctionnalités

- Inscription
- Connexion
- Systeme de réservation
- Paiement
- Administration de réservation

## Technologies utilisées

- PHP
- COMPOSER
- TWIG
- PHPDOTENV
- PHPMailer
- TailwindCSS v4

## Installation

1. Commencez par cloner le dépôt:

    ```bash
    git clone https://github.com/kaelianbaudelet/hotelneptune.git
    ```

2. Naviguez jusqu'au répertoire du projet :

    ```bash
    cd hotelneptune
    ```

3. Installer les dépendances :

    - Commencer par installer [Composer](https://getcomposer.org/download/)

    - Après avoir installé [Composer](https://getcomposer.org/download/), installer les dépendances du projet avec :

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
            docker compose --profile dev up
            ```

            L'environnement de travail pour le développement contient :
                - Une base de données [MariaDB](https://mariadb.org/).
                - Un serveur [Adminer](https://www.adminer.org/) pour administrer votre base de données. (Accesible via `localhost:8080`)
                - L'application web configurée avec [Apache2](https://httpd.apache.org/). (Accesible via `localhost:8080`)

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

## Mise en production

Pour mettre en production l'application installer les dépendences nécessaires avec

## Utilisation

1. Ouvrer votre navigateur et naviguer vers `http://localhost:80`.

## Licence

Ce projet est sous licence **MIT**.

## Contact

Pour toute question ou demande de renseignements, veuillez contacter [contact@kaelian.dev].
