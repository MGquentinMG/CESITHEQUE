<?php
// src/Controller/LoginController.php

class LoginController {
    private $pdo;
    private $twig;

    public function __construct($pdo, $twig) {
        $this->pdo = $pdo;
        $this->twig = $twig;
    }

    // Afficher le formulaire de connexion
    public function showLoginForm() {
        // Si l'utilisateur est déjà connecté, redirige vers /emprunts
        if (isset($_SESSION['user_id'])) {
            header('Location: /home ');
            exit();
        }

        // Affiche le formulaire de connexion
        echo $this->twig->render('login.twig');
    }

    // Traiter le formulaire de connexion
    public function login() {
        // Vérification si le formulaire a été soumis
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            // Requête pour vérifier les informations de l'utilisateur
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username');
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Vérifier si l'utilisateur existe et si le mot de passe est correct
            if ($user && password_verify($password, $user['password'])) {
                // Stocker les informations de l'utilisateur dans la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];  // Ajoute le rôle dans la session

                // Redirection vers la page des emprunts après la connexion
                header('Location: /home');
                exit();
            } else {
                // Si les identifiants sont incorrects, on redirige avec un message d'erreur
                $_SESSION['login_error'] = 'Nom d\'utilisateur ou mot de passe incorrect';
                header('Location: /login');
                exit();
            }
        }
    }
}
