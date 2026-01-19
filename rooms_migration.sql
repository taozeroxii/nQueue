CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(50) NOT NULL,
    description VARCHAR(255)
);

INSERT INTO rooms (room_name, description) 
SELECT * FROM (SELECT '1', 'General') AS tmp 
WHERE NOT EXISTS (SELECT room_name FROM rooms WHERE room_name = '1') LIMIT 1;

INSERT INTO rooms (room_name, description) 
SELECT * FROM (SELECT '2', 'Special') AS tmp 
WHERE NOT EXISTS (SELECT room_name FROM rooms WHERE room_name = '2') LIMIT 1;
