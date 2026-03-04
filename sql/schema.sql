-- Database schema for OJT Tracker
-- Dialect: MySQL 8.0+
CREATE DATABASE IF NOT EXISTS ojt_tracker;
USE ojt_tracker;

-- users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('student') DEFAULT 'student',
  required_hours INT DEFAULT 600,
  course VARCHAR(100) DEFAULT '',
  department VARCHAR(255) DEFAULT '',
  photo VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ojt_logs table
CREATE TABLE IF NOT EXISTS ojt_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  date DATE NOT NULL,
  time_in TIME NOT NULL,
  time_out TIME NOT NULL,
  total_hours DECIMAL(5,2) NOT NULL,
  description TEXT,
  status ENUM('approved') DEFAULT 'approved',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


-- indexes
CREATE INDEX idx_userid ON ojt_logs(user_id);
