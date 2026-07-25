<?php
// app/models/Menu.php

class Menu {
    private $pdo;
    private $table = 'menus';
    private $itemsTable = 'menu_items';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getByName($name) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }

    public function getItems($menuId, $parentId = null, bool $onlyActive = true) {
        $sql = "SELECT * FROM {$this->itemsTable}
                WHERE menu_id = ?  
                AND parent_id " . ($parentId === null ? "IS NULL" : "= ?")
                . ($onlyActive ? " AND active = 1" : "") . "
                ORDER BY position ASC";

        $stmt = $this->pdo->prepare($sql);

        if ($parentId === null) {
            $stmt->execute([$menuId]);
        } else {
            $stmt->execute([$menuId, $parentId]);
        }

        return $stmt->fetchAll();
    }

    public function getItemsWithChildren($menuId, bool $onlyActive = true) {
        $items = $this->getItems($menuId, null, $onlyActive);

        foreach ($items as &$item) {
            $item['children'] = $this->getItems($menuId, $item['id'], $onlyActive);
        }

        return $items;
    }

    public function createMenu($name, $label, $description = null) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (name, label, description) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$name, $label, $description]);
    }

    public function addItem($menuId, $title, $url, $position = 0, $icon = null, $parentId = null, $params = null) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->itemsTable} (menu_id, title, url, icon, position, parent_id, params, active)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1)"
        );
        $params = $params ?? '{}';
        return $stmt->execute([$menuId, $title, $url, $icon, $position, $parentId, $params]);
    }

    public function updateItem($itemId, $data) {
        $updates = [];
        $values = [];

        foreach ($data as $key => $value) {
            if (in_array($key, ['title', 'url', 'icon', 'position', 'active', 'parent_id', 'params'])) {
                $updates[] = "$key = ?";
                $values[] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $values[] = $itemId;
        $sql = "UPDATE {$this->itemsTable} SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function deleteItem($itemId) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->itemsTable} WHERE id = ? OR parent_id = ?");
        return $stmt->execute([$itemId, $itemId]);
    }

    public function getAllMenus() {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} ORDER BY name ASC");
        return $stmt->fetchAll();
    }
}
