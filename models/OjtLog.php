<?php
// models/OjtLog.php

class OjtLog {
    private $conn;
    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function create($data) {
        // automatically consider logs approved (no approval workflow)
        $stmt = $this->conn->prepare("INSERT INTO ojt_logs (user_id, date, time_in, time_out, total_hours, description, status, created_at) VALUES (?,?,?,?,?,?, 'approved',NOW())");
        return $stmt->execute([
            $data['user_id'],
            $data['date'],
            $data['time_in'],
            $data['time_out'],
            $data['total_hours'],
            $data['description']
        ]);
    }

    /**
     * Fetch logs for a user optionally bounded by date range and paginated.
     *
     * @param int $user_id
     * @param string|null $start  start date (inclusive)
     * @param string|null $end    end date (inclusive)
     * @param int|null $limit     number of rows to return
     * @param int|null $offset    row offset for pagination
     * @return array
     */
    public function findByUser($user_id, $start=null, $end=null, $limit=null, $offset=null) {
        $sql = "SELECT * FROM ojt_logs WHERE user_id = ?";
        $params = [$user_id];
        if ($start) { $sql .= " AND date >= ?"; $params[] = $start; }
        if ($end) { $sql .= " AND date <= ?"; $params[] = $end; }
        $sql .= " ORDER BY date DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = (int)$limit;
            if ($offset !== null) {
                $sql .= " OFFSET ?";
                $params[] = (int)$offset;
            }
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTotals($user_id) {
        $stmt = $this->conn->prepare("SELECT SUM(total_hours) as total FROM ojt_logs WHERE user_id = ? AND status='approved'");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }

    /**
     * Count logs belonging to a user (optionally within date range).
     */
    public function countByUser($user_id, $start=null, $end=null) {
        $sql = "SELECT COUNT(*) as cnt FROM ojt_logs WHERE user_id = ?";
        $params = [$user_id];
        if ($start) { $sql .= " AND date >= ?"; $params[] = $start; }
        if ($end) { $sql .= " AND date <= ?"; $params[] = $end; }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return intval($row['cnt']);
    }


    public function delete($id, $user_id) {
        $stmt = $this->conn->prepare('DELETE FROM ojt_logs WHERE id=? AND user_id=?');
        return $stmt->execute([$id, $user_id]);
    }
}
