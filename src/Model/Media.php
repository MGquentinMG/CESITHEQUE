<?php

class Media {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Récupère tous les médias
    public function getAllMedias() {
        $stmt = $this->pdo->prepare("SELECT * FROM medias ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère un média par son ID
    public function getMediaById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM medias WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Ajoute un nouveau média avec description, photo et date automatique
    public function insertMedia($title, $type, $description, $photo) {
        $stmt = $this->pdo->prepare("
            INSERT INTO medias (title, type, description, photo)
            VALUES (:title, :type, :description, :photo)
        ");

        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':photo', $photo, PDO::PARAM_STR);
        $stmt->execute();
    }

    // Met à jour un média existant
    public function updateMedia($id, $title, $type, $description, $photo) {
        $stmt = $this->pdo->prepare("
            UPDATE medias
            SET title = :title,
                type = :type,
                description = :description,
                photo = :photo
            WHERE id = :id
        ");
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':photo', $photo, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Supprime un média
    public function deleteMedia($id) {
        $stmt = $this->pdo->prepare("DELETE FROM medias WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
