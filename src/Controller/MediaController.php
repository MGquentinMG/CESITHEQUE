<?php
// src/Controller/MediaController.php

require_once __DIR__ . '/../Model/Media.php';

class MediaController
{
    private PDO $pdo;
    private Media $mediaModel;
    private ?\Twig\Environment $twig;

    public function __construct(PDO $pdo, ?\Twig\Environment $twig = null)
    {
        $this->pdo = $pdo;
        $this->twig = $twig;
        $this->mediaModel = new Media($pdo);
    }

    private function requireAdmin(): void
    {
        if (empty($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
            header('Location: /home');
            exit();
        }
    }

    // ========== VUES WEB ==========

    public function listMedias(): void
    {
        $this->requireAdmin();
        $medias = $this->mediaModel->getAllMedias();
        echo $this->twig->render('media/list.twig', ['medias' => $medias]);
    }

    public function showAddForm(): void
    {
        $this->requireAdmin();
        echo $this->twig->render('media/add.twig');
    }

    public function addMedia(string $title, string $type, string $description = ''): void
    {
        $this->requireAdmin();

        $photo = '';
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
            $filePath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
                $photo = '/uploads/' . $fileName;
            }
        }

        $this->mediaModel->insertMedia($title, $type, $description, $photo);
        header('Location: /media');
        exit();
    }

    public function showEditForm(int $id): void
    {
        $this->requireAdmin();
        $media = $this->mediaModel->getMediaById($id);
        echo $this->twig->render('media/edit.twig', ['media' => $media]);
    }

    public function updateMedia(int $id, string $title, string $type, string $description = '', string $existingPhoto = ''): void
    {
        $this->requireAdmin();

        $photo = $existingPhoto;
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
            $filePath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
                $photo = '/uploads/' . $fileName;
            }
        }

        $this->mediaModel->updateMedia($id, $title, $type, $description, $photo);
        header('Location: /media');
        exit();
    }

    public function deleteMedia(int $id): void
    {
        $this->requireAdmin();
        $this->mediaModel->deleteMedia($id);
        header('Location: /media');
        exit();
    }

    public function listUser(): void
    {
        $medias = $this->mediaModel->getAllMedias();
        echo $this->twig->render('media/popup_list_user.twig', [
            'medias' => $medias,
        ]);
    }

    // ========== API REST ==========

    public function apiListMedias(): void
    {
        $medias = $this->mediaModel->getAllMedias();
        echo json_encode($medias);
    }

    public function apiGetMedia(int $id): void
    {
        $media = $this->mediaModel->getMediaById($id);

        if ($media) {
            echo json_encode($media);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Media non trouvé']);
        }
    }

    public function apiAddMedia(): void
    {
        // 1) Lecture des champs textuels
        $title       = trim($_POST['title'] ?? '');
        $type        = trim($_POST['type'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '' || $type === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Champs requis : title, type']);
            return;
        }

        // 2) Gestion de l'upload de la photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
            $filePath = $uploadDir . $fileName;
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de l\'upload de l\'image']);
                return;
            }
            $photoPath = '/uploads/' . $fileName;
        } else {
            $photoPath = '';  // pas d'image fournie
        }

        // 3) Insertion en base
        $this->mediaModel->insertMedia($title, $type, $description, $photoPath);

        http_response_code(201);
        echo json_encode(['message' => 'Media ajouté avec succès']);
    }


    public function apiUpdateMedia(int $id): void
    {
        // 1) Override method
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'POST' && ($_GET['_method'] ?? '') === 'PUT') {
            // 2) Handle upload
            if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $fileName = uniqid().'_'.basename($_FILES['photo']['name']);
                $filePath = $uploadDir . $fileName;
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
                    http_response_code(500);
                    echo json_encode(['error'=>'Erreur upload image']);
                    return;
                }
                $photoPath = '/uploads/'.$fileName;
            } else {
                $photoPath = '';
            }

            // 3) Read text fields
            $title = trim($_POST['title'] ?? '');
            $type  = trim($_POST['type']  ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($title === '' || $type === '') {
                http_response_code(400);
                echo json_encode(['error'=>'title et type requis']);
                return;
            }

            // 4) Check existing
            $existing = $this->mediaModel->getMediaById($id);
            if (!$existing) {
                http_response_code(404);
                echo json_encode(['error'=>'Media non trouvé']);
                return;
            }

            // 5) Update (keep old photo if none uploaded)
            $this->mediaModel->updateMedia(
                $id,
                $title,
                $type,
                $description,
                $photoPath ?: $existing['photo']
            );

            http_response_code(200);
            echo json_encode(['message'=>'Média mis à jour avec succès']);
            return;
        }

        // If wrong method
        http_response_code(405);
        echo json_encode(['error'=>'Méthode non autorisée']);
    }





}
