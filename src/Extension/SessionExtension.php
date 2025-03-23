<?php
// src/Extension/SessionExtension.php

namespace App\Extension;

/**
 * Extension Twig permettant de gérer les sessions
 */
class SessionExtension extends \Twig\Extension\AbstractExtension
{
    /**
     * Récupère les fonctions de l'extension.
     *
     * @return array Les fonctions de l'extension
     */
    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('clear_session_alert', [$this, 'clearSessionAlert']), // Ajoute une fonction Twig pour effacer les alertes de session
        ];
    }

    /**
     * Efface les alertes de session.
     *
     * @return void
     */
    public function clearSessionAlert()
    {
        $_SESSION['alert'] = null;
    }
}
