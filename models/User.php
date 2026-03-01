<?php
// models/User.php

class User {
    private $conn;
    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function create($data) {
        // columns: full_name,email,password,role,required_hours,course,photo,created_at
        $stmt = $this->conn->prepare('INSERT INTO users (full_name, email, password, role, required_hours, course, photo, created_at) VALUES (?,?,?,?,?,?,?,NOW())');
        return $stmt->execute([
            $data['full_name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'] ?? 'student',
            $data['required_hours'] ?? 600,
            $data['course'] ?? '',
            $data['photo'] ?? null
        ]);
    }

    public function allStudents() {
        $stmt = $this->conn->query("SELECT * FROM users WHERE role='student'");
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function update($id, $full_name, $required_hours, $course = null, $photo = null) {
        $fields = ['full_name = ?', 'required_hours = ?'];
        $params = [$full_name, $required_hours];
        if ($course !== null) {
            $fields[] = 'course = ?';
            $params[] = $course;
        }
        if ($photo !== null) {
            $fields[] = 'photo = ?';
            $params[] = $photo;
        }
        $params[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
}
