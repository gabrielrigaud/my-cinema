<?php

require_once __DIR__ . '/../config/database.php';

class Room {
    private $db;
    private $table = 'rooms';

    public function __construct() {
        $this->db = new Database();
    }

    public function getAll($page = 1, $limit = ITEMS_PER_PAGE, $search = '') {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT * FROM {$this->table} WHERE active = 1";

        if (!empty($search)) {
            $sql .= " AND (name LIKE :search1 OR type LIKE :search2)";
        }

        $sql .= " ORDER BY name LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        if (!empty($search)) {
            $searchParam = '%' . $search . '%';
            $stmt->bindValue(':search1', $searchParam);
            $stmt->bindValue(':search2', $searchParam);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count($search = '') {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE active = 1";

        if (!empty($search)) {
            $sql .= " AND (name LIKE :search1 OR type LIKE :search2)";
        }

        $stmt = $this->db->prepare($sql);

        if (!empty($search)) {
            $searchParam = '%' . $search . '%';
            $stmt->bindValue(':search1', $searchParam);
            $stmt->bindValue(':search2', $searchParam);
        }

        $stmt->execute();
        return $stmt->fetch()['total'];
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create($data) {
        if ($this->getByName($data['name'])) {
            throw new Exception("A room with this name already exists");
        }
        
        $sql = "INSERT INTO {$this->table} (name, capacity, type, active) 
                VALUES (:name, :capacity, :type, :active)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':capacity', $data['capacity'], PDO::PARAM_INT);
        $stmt->bindValue(':type', $data['type'] ?? 'Standard');
        $stmt->bindValue(':active', $data['active'] ?? true, PDO::PARAM_BOOL);
        
        return $stmt->execute();
    }

    public function update($id, $data) {
        $existing = $this->getByName($data['name']);
        if ($existing && $existing['id'] != $id) {
            throw new Exception("A room with this name already exists");
        }
        
        $sql = "UPDATE {$this->table} SET 
                name = :name, 
                capacity = :capacity, 
                type = :type, 
                active = :active 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':capacity', $data['capacity'], PDO::PARAM_INT);
        $stmt->bindValue(':type', $data['type'] ?? 'Standard');
        $stmt->bindValue(':active', $data['active'] ?? true, PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "SELECT COUNT(*) as count FROM screenings
                WHERE room_id = :id AND start_time > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $futureScreenings = $stmt->fetch()['count'];
        
        if ($futureScreenings > 0) {
            throw new Exception("Cannot delete a room with future scheduled screenings");
        }

        $sql = "UPDATE {$this->table} SET active = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function getByName($name) {
        $sql = "SELECT * FROM {$this->table} WHERE name = :name AND active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getAllForSelect() {
        $sql = "SELECT id, name, capacity, type FROM {$this->table} WHERE active = 1 ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTypes() {
        $sql = "SELECT DISTINCT type FROM {$this->table} WHERE active = 1 AND type IS NOT NULL ORDER BY type";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function checkAvailability($roomId, $startTime, $duration, $excludeScreeningId = null) {
        $sql = "SELECT s.*, m.duration as movie_duration 
                FROM screenings s
                JOIN movies m ON s.movie_id = m.id
                WHERE s.room_id = :room_id";
        
        $params = [':room_id' => $roomId];
        
        if ($excludeScreeningId) {
            $sql .= " AND s.id != :exclude_id";
            $params[':exclude_id'] = $excludeScreeningId;
        }
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        $screenings = $stmt->fetchAll();
        
        $start = new DateTime($startTime);
        $end = clone $start;
        $end->add(new DateInterval('PT' . $duration . 'M'));
        
        foreach ($screenings as $screening) {
            $screeningStart = new DateTime($screening['start_time']);
            $screeningEnd = clone $screeningStart;
            $screeningEnd->add(new DateInterval('PT' . $screening['movie_duration'] . 'M'));

            if (($start >= $screeningStart && $start < $screeningEnd) ||
                ($end > $screeningStart && $end <= $screeningEnd) ||
                ($start <= $screeningStart && $end >= $screeningEnd)) {
                return false;
            }
        }

        return true;
    }

    public function toggleActive($id, $active) {
        $sql = "UPDATE {$this->table} SET active = :active WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':active', $active, PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
