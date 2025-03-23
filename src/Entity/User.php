<?php
// src/Entity/User.php

declare(strict_types=1);

namespace App\Entity;

/**
 * Entité représentant un utilisateur
 */
class User
{
    /**
     * @var string L'identifiant unique de l'utilisateur
     */
    private ?string $id = null;

    /**
     * @var string Le rôle de l'utilisateur
     */
    private string $role;

    /**
     * @var string Le nom de l'utilisateur
     */
    private string $name;

    /**
     * @var string L'adresse email de l'utilisateur
     */
    private string $email;

    /**
     * @var string|null Le mot de passe de l'utilisateur
     */
    private ?string $password;

    /**
     * @var bool|null Si l'utilisateur est actif
     */
    private ?bool $isActive = null;

    /**
     * @var string|null Le token de réinitialisation du mot de passe
     */
    private ?string $reset_token;

    /**
     * @var \DateTime|null La date d'expiration du token de réinitialisation
     */
    private ?\DateTime $reset_token_expires_at;

    /**
     * @var \DateTime La date de création de l'utilisateur
     */
    private ?\DateTime $created_at;

    /**
     * @var \DateTime La date de mise à jour de l'utilisateur
     */
    private ?\DateTime $updated_at;

    public function __construct(
        ?string $id,
        string $role,
        string $name,
        string $email,
        ?string $password,
        ?bool $isActive,
        ?string $reset_token,
        ?\DateTime $reset_token_expires_at,
        ?\DateTime $created_at,
        ?\DateTime $updated_at
    ) {
        $this->id = $id;
        $this->role = $role;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->isActive = $isActive;
        $this->reset_token = $reset_token;
        $this->reset_token_expires_at = $reset_token_expires_at;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    /**
     * Récupère l'identifiant de l'utilisateur.
     *
     * @return string|null L'identifiant de l'utilisateur
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Définit l'identifiant de l'utilisateur.
     *
     * @param string|null $id L'identifiant de l'utilisateur
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Récupère le rôle de l'utilisateur.
     *
     * @return string Le rôle de l'utilisateur
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * Définit le rôle de l'utilisateur.
     *
     * @param string $role Le rôle de l'utilisateur
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * Récupère le nom de l'utilisateur.
     *
     * @return string Le nom de l'utilisateur
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Définit le nom de l'utilisateur.
     *
     * @param string $name Le nom de l'utilisateur
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Récupère l'adresse email de l'utilisateur.
     *
     * @return string L'adresse email de l'utilisateur
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Définit l'adresse email de l'utilisateur.
     *
     * @param string $email L'adresse email de l'utilisateur
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Récupère le mot de passe de l'utilisateur.
     *
     * @return string|null Le mot de passe de l'utilisateur
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Définit le mot de passe de l'utilisateur.
     *
     * @param string|null $password Le mot de passe de l'utilisateur
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Définit si l'utilisateur est actif.
     *
     * @param bool|null $isActive Si l'utilisateur est actif
     */
    public function setIsActive(?bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * Récupère si l'utilisateur est actif.
     *
     * @return bool|null Si l'utilisateur est actif
     */
    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * Récupère le token de réinitialisation du mot de passe.
     *
     * @return string|null Le token de réinitialisation du mot de passe
     */
    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    /**
     * Définit le token de réinitialisation du mot de passe.
     *
     * @param string|null $reset_token Le token de réinitialisation du mot de passe
     */
    public function setResetToken(?string $reset_token): void
    {
        $this->reset_token = $reset_token;
    }

    /**
     * Récupère la date d'expiration du token de réinitialisation.
     *
     * @return \DateTime|null La date d'expiration du token de réinitialisation
     */
    public function getResetTokenExpiresAt(): ?\DateTime
    {
        return $this->reset_token_expires_at;
    }

    /**
     * Définit la date d'expiration du token de réinitialisation.
     *
     * @param \DateTime|null $reset_token_expires_at La date d'expiration du token de réinitialisation
     */
    public function setResetTokenExpiresAt(?\DateTime $reset_token_expires_at): void
    {
        $this->reset_token_expires_at = $reset_token_expires_at;
    }

    /**
     * Récupère la date de création de l'utilisateur.
     *
     * @return \DateTime La date de création de l'utilisateur
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    /**
     * Définit la date de création de l'utilisateur.
     *
     * @param \DateTime $created_at La date de création de l'utilisateur
     */
    public function setCreatedAt(\DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * Récupère la date de mise à jour de l'utilisateur.
     *
     * @return \DateTime La date de mise à jour de l'utilisateur
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updated_at;
    }

    /**
     * Définit la date de mise à jour de l'utilisateur.
     *
     * @param \DateTime $updated_at La date de mise à jour de l'utilisateur
     */
    public function setUpdatedAt(\DateTime $updated_at): void
    {
        $this->updated_at = $updated_at;
    }
}
