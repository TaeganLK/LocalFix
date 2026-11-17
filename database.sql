
CREATE DATABASE IF NOT EXISTS localfix_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE localfix_db;

-- 1. Users
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'resident',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Categories
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB;

-- 3. Reports
CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(30) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Submitted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_reports_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id),

    CONSTRAINT fk_reports_category
        FOREIGN KEY (category_id)
        REFERENCES categories(category_id),

    INDEX idx_reports_user (user_id),
    INDEX idx_reports_status (status)
) ENGINE=InnoDB;

-- 4. Report Updates
CREATE TABLE report_updates (
    update_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    admin_id INT NOT NULL,
    status VARCHAR(30) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_updates_report
        FOREIGN KEY (report_id)
        REFERENCES reports(report_id),

    CONSTRAINT fk_updates_admin
        FOREIGN KEY (admin_id)
        REFERENCES users(user_id),

    INDEX idx_updates_report (report_id)
) ENGINE=InnoDB;

-- Insert initial fault categories
INSERT INTO categories (category_name, description) VALUES
('Potholes', 'Damaged roads and potholes'),
('Streetlights', 'Broken or faulty streetlights'),
('Water Leaks', 'Water leaks and burst pipes'),
('Blocked Drains', 'Blocked or damaged drainage systems'),
('Illegal Dumping', 'Illegal dumping of waste'),
('Traffic Lights', 'Faulty or damaged traffic lights'),
('Other', 'Other municipal faults');