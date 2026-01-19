CREATE TABLE IF NOT EXISTS queues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vn VARCHAR(50) NOT NULL,
    hn VARCHAR(50),
    patient_name VARCHAR(255),
    oqueue VARCHAR(50), -- Original queue number from HIS
    room_number INT NOT NULL,
    status ENUM('waiting', 'called', 'completed') DEFAULT 'waiting',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (room_number),
    INDEX (status),
    INDEX (created_at)
);
