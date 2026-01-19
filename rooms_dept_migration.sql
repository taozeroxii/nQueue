ALTER TABLE rooms ADD COLUMN department VARCHAR(100) DEFAULT 'OPD';

-- Assign some defaults
UPDATE rooms SET department = 'OPD' WHERE id IN (1, 2);
UPDATE rooms SET department = 'Dental' WHERE id > 2;
-- Create dummy Dental room if not exists
INSERT INTO rooms (room_name, description, department) 
SELECT * FROM (SELECT '3', 'Dental 1', 'Dental') AS tmp 
WHERE NOT EXISTS (SELECT id FROM rooms WHERE room_name = '3') LIMIT 1;
