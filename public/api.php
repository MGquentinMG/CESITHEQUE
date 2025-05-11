<?php
// public/api.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Controller/MediaController.php';

session_start();

// Connexion à la base de données
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
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion : ' . $e->getMessage()]);
    exit();
}

// Pas besoin de Twig ici
header('Content-Type: application/json');

// Contrôleurs API
$mediaCtrl = new MediaController($pdo, null);

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/api', '', $uri);

// Routage REST
switch (true) {

    // GET /media
    case $uri === '/media' && $method === 'GET':
        $mediaCtrl->apiListMedias();
        break;

    // GET /media/{id}
    case preg_match('#^/media/(\d+)$#', $uri, $matches) && $method === 'GET':
        $mediaCtrl->apiGetMedia((int)$matches[1]);
        break;


// POST /api/media
    case $uri === '/media' && $method === 'POST':
        $mediaCtrl->apiAddMedia();
        break;



// POST /api/media/{id}?_method=PUT
    case preg_match('#^/media/(\d+)$#', $uri, $m) && $_SERVER['REQUEST_METHOD']==='POST' && ($_GET['_method']??'')==='PUT':
        $mediaCtrl->apiUpdateMedia((int)$m[1]);
        break;


    // DELETE /media/{id}
    case preg_match('#^/media/(\d+)$#', $uri, $matches) && $method === 'DELETE':
        $mediaCtrl->apiDeleteMedia((int)$matches[1]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Route API non trouvée']);
        break;
}
