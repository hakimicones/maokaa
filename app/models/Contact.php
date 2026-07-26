<?php
// app/models/Contact.php

class Contact {
    private $pdo;
    private $table = 'contacts';
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        
        return $this->pdo->query($sql)->fetchAll();
    }
    
    public function getUnread() {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE lu = 0 ORDER BY created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (nom, email, telephone, sujet, message) 
             VALUES (?, ?, ?, ?, ?)"
        );
        
        return $stmt->execute([
            $data['nom'] ?? '',
            $data['email'] ?? '',
            $data['telephone'] ?? '',
            $data['sujet'] ?? '',
            $data['message'] ?? ''
        ]);
    }
    
    public function markAsRead($id) {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET lu = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function count($unread = false) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($unread) {
            $sql .= " WHERE lu = 0";
        }
        $result = $this->pdo->query($sql)->fetch();
        return $result['total'] ?? 0;
    }
}
