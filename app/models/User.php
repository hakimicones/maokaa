<?php
// app/models/User.php

class User {
    private $pdo;
    private $table = 'admins';
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtenir tous les utilisateurs
     */
    public function getAll($active = false) {
        $sql = "SELECT id, username, fullname, email, active, created_at, updated_at FROM {$this->table}";
        if ($active) {
            $sql .= " WHERE active = 1";
        }
        $sql .= " ORDER BY id ASC";
        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * Obtenir un utilisateur par ID
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT id, username, fullname, email, active, created_at, updated_at FROM {$this->table} WHERE id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Obtenir un utilisateur par username (pour login)
     */
    public function getByUsername($username) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE username = ? AND active = 1"
        );
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    /**
     * Créer un utilisateur
     */
    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (username, password_hash, fullname, email, active, must_change_password) 
             VALUES (?, ?, ?, ?, ?, 1)"
        );
        return $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['fullname'] ?? '',
            $data['email'] ?? '',
            $data['active'] ?? 1
        ]);
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update($id, $data) {
        // Avec nouveau mot de passe
        if (!empty($data['password'])) {
            $stmt = $this->pdo->prepare(
                "UPDATE {$this->table} SET username = ?, password_hash = ?, fullname = ?, email = ?, active = ?, must_change_password = 1, updated_at = NOW() WHERE id = ?"
            );
            return $stmt->execute([
                $data['username'],
                password_hash($data['password'], PASSWORD_BCRYPT),
                $data['fullname'] ?? '',
                $data['email'] ?? '',
                $data['active'] ?? 1,
                $id
            ]);
        }

        // Sans changer le mot de passe
        $stmt = $this->pdo->prepare(
            "UPDATE {$this->table} SET username = ?, fullname = ?, email = ?, active = ?, updated_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([
            $data['username'],
            $data['fullname'] ?? '',
            $data['email'] ?? '',
            $data['active'] ?? 1,
            $id
        ]);
    }

    /**
     * Supprimer un utilisateur
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Compter les utilisateurs
     */
    public function count($active = false) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($active) {
            $sql .= " WHERE active = 1";
        }
        $result = $this->pdo->query($sql)->fetch();
        return $result['total'] ?? 0;
    }
}