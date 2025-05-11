<?php
// public/index.php

// Affichage des erreurs PHP pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Autoload de Composer et inclusion des contrôleurs
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Controller/MediaController.php';
require_once __DIR__ . '/../src/Controller/EmpruntController.php';
require_once __DIR__ . '/../src/Controller/LoginController.php';
require_once __DIR__ . '/../src/Controller/RegisterController.php';
require_once __DIR__ . '/../src/Controller/HomeController.php';

session_start();  // Démarrer la session

// 2. Connexion à la base de données
$host = 'localhost';
$db   = 'projet_mediatheque';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo 'Erreur de connexion : ' . $e->getMessage();
    exit();
}

// 3. Configuration de Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../src/View');
$twig = new \Twig\Environment($loader, [
    'debug' => true,
]);

// Rendre la session disponible dans tous les templates
$twig->addGlobal('session', $_SESSION);
$twig->addExtension(new \Twig\Extension\DebugExtension());

// 4. Instanciation des contrôleurs
$controller = new MediaController($pdo, $twig);
$empruntCtrl = new EmpruntController($pdo, $twig);
$loginCtrl = new LoginController($pdo, $twig);
$registerCtrl = new RegisterController($pdo, $twig);
$homeCtrl = new HomeController($twig);

// 5. Normalisation du chemin (URI)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($uri !== '/' && substr($uri, -1) === '/') {
    $uri = rtrim($uri, '/');
}

// 6. Routage
$method = $_SERVER['REQUEST_METHOD'];

switch ($uri) {

    case '/':
        $homeCtrl->index();
        break;

    case '/media':
        $controller->listMedias();
        break;

    case '/media/add':
        if ($method === 'GET') {
            $controller->showAddForm();
        } elseif ($method === 'POST') {
            // ✅ Correction ici : ajout de la description dans l'appel
            $controller->addMedia(
                $_POST['title'] ?? '',
                $_POST['type'] ?? '',
                $_POST['description'] ?? ''
            );
        }
        break;

    case (preg_match('#^/media/edit/(\d+)$#', $uri, $matches) ? true : false):
        $id = (int)$matches[1];
        if ($method === 'GET') {
            $controller->showEditForm($id);
        } elseif ($method === 'POST') {
            $controller->updateMedia(
                $id,
                $_POST['title'] ?? '',
                $_POST['type'] ?? '',
                $_POST['description'] ?? '',
                $_POST['existingPhoto'] ?? ''
            );
        }
        break;

    case (preg_match('#^/media/delete/(\d+)$#', $uri, $matches) ? true : false):
        $id = (int)$matches[1];
        $controller->deleteMedia($id);
        break;

    case '/emprunts':
        $empruntCtrl->listCurrent();
        break;

    case (preg_match('#^/emprunts/borrow/(\d+)$#', $uri, $matches) ? true : false):
        $id = (int)$matches[1];
        $empruntCtrl->borrow($id);
        break;

    case (preg_match('#^/emprunts/return/(\d+)$#', $uri, $matches) ? true : false):
        $id = (int)$matches[1];
        $empruntCtrl->return($id);
        break;

    case '/emprunts/history':
        $empruntCtrl->Allhistory();
        break;

    case '/emprunts/historyPersonnal':
        $empruntCtrl->history();
        break;

    case '/home':
        $homeCtrl->index();
        break;

    case '/Propos':
        $homeCtrl->A_Propos();
        break;

    case '/Contact':
        $homeCtrl->showContactForm();
        break;

    case '/login':
        if ($method === 'GET') {
            // Si l'utilisateur est déjà connecté, redirige vers la page des emprunts
            if (isset($_SESSION['user_id'])) {
                header('Location: /emprunts');
                exit();
            }
            // Affiche le formulaire de connexion
            $loginCtrl->showLoginForm();
        } elseif ($method === 'POST') {
            // Traite la soumission du formulaire de connexion
            $loginCtrl->login();
        }
        break;

    case '/register':
        if ($method === 'GET') {
            // Affiche le formulaire d'inscription
            $registerCtrl->showRegisterForm();
        } elseif ($method === 'POST') {
            // Traite la soumission du formulaire d'inscription
            $registerCtrl->register();
        }
        break;

    case '/logout':
        // Détruit la session et redirige vers la page de connexion
        session_destroy();
        header('Location: /login');
        exit();

    case '/media/list_user':
        $controller->listUser();
        break;

    default:
        // Si la route n'existe pas, affiche une page d'erreur 404
        header("HTTP/1.0 404 Not Found");
        echo $twig->render('errors/404.twig', ['uri' => $uri]);
        break;
}
