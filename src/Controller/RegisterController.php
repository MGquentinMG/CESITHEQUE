<?php
// src/Controller/RegisterController.php

require_once __DIR__ . '/../Model/User.php';  // Inclusion du modèle User pour l'accès à la base de données

class RegisterController {
    private $pdo;
    private $userModel;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);  // Création du modèle User
    }

    // Afficher le formulaire d'inscription
    public function showRegisterForm() {
        echo $this->render('register.twig');
    }

    // Traiter le formulaire d'inscription
    public function register() {
        // Récupérer les données du formulaire
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;
        $confirmPassword = $_POST['confirm_password'] ?? null;

        if ($username && $password && $confirmPassword) {
            // Vérifier si les mots de passe correspondent
            if ($password !== $confirmPassword) {
                echo "Les mots de passe ne correspondent pas.";
                return;
            }

            // Vérifier si le nom d'utilisateur existe déjà
            $existingUser = $this->userModel->getUserByUsername($username);
            if ($existingUser) {
                echo "Le nom d'utilisateur est déjà pris.";
                return;
            }

            // Hacher le mot de passe
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Créer le nouvel utilisateur
            $this->userModel->createUser($username, $hashedPassword);

            // Rediriger vers la page de connexion après l'inscription
            header('Location: /login');
            exit();
        } else {
            echo "Veuillez remplir tous les champs.";
        }
    }

    // Rendu de la vue Twig
    private function render($view, $params = []) {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../View');
        $twig = new \Twig\Environment($loader);
        return $twig->render($view, $params);
    }
}
