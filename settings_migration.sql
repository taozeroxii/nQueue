CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(50) NOT NULL UNIQUE,
    key_value VARCHAR(255)
);

-- Default Settings
INSERT INTO settings (key_name, key_value) 
SELECT * FROM (SELECT 'dept_name', 'คิวตรวจโรคทั่วไป') AS tmp 
WHERE NOT EXISTS (SELECT key_name FROM settings WHERE key_name = 'dept_name') LIMIT 1;

INSERT INTO settings (key_name, key_value) 
SELECT * FROM (SELECT 'dept_sub', 'General OPD Queue') AS tmp 
WHERE NOT EXISTS (SELECT key_name FROM settings WHERE key_name = 'dept_sub') LIMIT 1;
