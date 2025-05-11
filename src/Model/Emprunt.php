<?php

class Emprunt
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Emprunter un média : insère une ligne dans la base de données
    public function create(int $userId, int $mediaId): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO emprunts (user_id, media_id) VALUES (:uid, :mid)"
        );
        $stmt->execute(['uid' => $userId, 'mid' => $mediaId]);
    }

    // Retourner un média : met à jour la date_retour dans la base de données
    public function close(int $empruntId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE emprunts SET date_retour = NOW() WHERE id = :id"
        );
        $stmt->execute(['id' => $empruntId]);
    }

    // Liste des emprunts en cours avec les informations de l'utilisateur et du média
    public function getCurrent(): array
    {
        $stmt = $this->pdo->query(
            "SELECT e.id, u.username AS user, m.title AS media, e.date_emprunt
             FROM emprunts e
             JOIN users u ON e.user_id = u.id
             JOIN medias m ON e.media_id = m.id
             WHERE e.date_retour IS NULL
             ORDER BY e.date_emprunt DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Historique des emprunts d'un utilisateur avec les informations sur les médias et les dates
// Historique des emprunts d’un utilisateur
    public function getByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT e.id, m.title AS media, e.date_emprunt, e.date_retour, u.username AS user_name
         FROM emprunts e
         JOIN medias m ON e.media_id = m.id
         JOIN users u ON e.user_id = u.id
         WHERE e.user_id = :uid
         ORDER BY e.date_emprunt DESC"
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllHistory(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT e.id, m.title AS media, e.date_emprunt, e.date_retour, u.username AS user_name
         FROM emprunts e
         JOIN medias m ON e.media_id = m.id
         JOIN users u ON e.user_id = u.id
         ORDER BY e.date_emprunt DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



}
