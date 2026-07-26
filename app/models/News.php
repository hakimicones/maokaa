<?php
// app/models/News.php

class News {
    private $pdo;
    private $table = 'actualites';
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'published' 
                ORDER BY published_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getAllAdmin() {
    $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
    return $this->pdo->query($sql)->fetchAll();
}
    



    public function getRecent($limit = 6) {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'published' 
                ORDER BY published_at DESC LIMIT " . (int)$limit;
        
        return $this->pdo->query($sql)->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE id = ? AND status = 'published'"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getBySlug($slug) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE slug = ? AND status = 'published'"
        );
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $slug = $this->generateSlug($data['title']);
        
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (title, slug, content, image, excerpt, status, author_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        
        return $stmt->execute([
            $data['title'],
            $slug,
            $data['content'] ?? '',
            $data['image'] ?? null,
            $data['excerpt'] ?? '',
            $data['status'] ?? 'draft',
            $data['author_id'] ?? null
        ]);
    }
    
    public function update($id, $data) {
        $stmt = $this->pdo->prepare(
            "UPDATE {$this->table} SET title = ?, content = ?, image = ?, excerpt = ?, 
             status = ?, published_at = NOW() WHERE id = ?"
        );
        
        return $stmt->execute([
            $data['title'],
            $data['content'] ?? '',
            $data['image'] ?? null,
            $data['excerpt'] ?? '',
            $data['status'] ?? 'draft',
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function count($published = true) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($published) {
            $sql .= " WHERE status = 'published'";
        }
        $result = $this->pdo->query($sql)->fetch();
        return $result['total'] ?? 0;
    }
    
    private function generateSlug($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = preg_replace('~-+~', '-', $text);
        $text = trim($text, '-');
        return strtolower($text);
    }
}
