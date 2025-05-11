<?php

require_once __DIR__ . '/../Model/Emprunt.php';  // Inclusion du modèle Emprunt
require_once __DIR__ . '/../Model/User.php';    // Inclusion du modèle User si nécessaire

class EmpruntController {
    private PDO $pdo;
    private Emprunt $empruntModel;
    private \Twig\Environment $twig;

    // ✅ Constructeur avec injection directe de Twig
    public function __construct(PDO $pdo, \Twig\Environment $twig) {
        $this->pdo = $pdo;
        $this->twig = $twig;
        $this->empruntModel = new Emprunt($pdo);
    }

    // Affiche la liste des emprunts en cours
    public function listCurrent() {
        $emprunts = $this->empruntModel->getCurrent();
        echo $this->twig->render('emprunts/list.twig', ['emprunts' => $emprunts]);
    }

    // Emprunter un média
    public function borrow(int $mediaId) {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            header('Location: /login');
            exit;
        }

        // Récupération du rôle depuis la session ou une méthode
        $role = $_SESSION['role'] ?? null;

        // Si le rôle n'est pas en session, le récupérer depuis la base de données (exemple)
        if (!$role) {
            $user = $this->userModel->findById($userId); // Assure-toi que userModel est bien chargé
            $role = $user['role'] ?? 'user';
        }

        // Traitement d'emprunt
        $this->empruntModel->create($userId, $mediaId);

        // Redirection en fonction du rôle
        switch ($role) {
            case 'Admin':
                header('Location: /emprunts');
                break;
            default:
                header('Location: /emprunts/historyPersonnal');
                break;
        }

        exit;
    }


    // Retourner un média
    public function return(int $empruntId) {
        $this->empruntModel->close($empruntId);
        header('Location: /emprunts');
        exit;
    }

    // Affiche l'historique des emprunts d'un utilisateur
    public function history() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: /login');
            exit;
        }


        // Utiliser la méthode correcte : getByUser() au lieu de getHistoryWithUser()
        $history = $this->empruntModel->getByUser($userId);

        // Afficher l'historique avec les données récupérées
        echo $this->twig->render('emprunts/historyPersonal.twig', ['history' => $history]);
    }

    public function Allhistory() {
        $history = $this->empruntModel->getAllHistory();  // On récupère tous les emprunts
        echo $this->twig->render('emprunts/history.twig', ['history' => $history]);
    }

}
