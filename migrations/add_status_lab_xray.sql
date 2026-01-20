-- Ensure the status column supports 'lab' and 'xray'
-- If using ENUM, run:
ALTER TABLE queues MODIFY COLUMN status ENUM('waiting', 'called', 'completed', 'lab', 'xray') DEFAULT 'waiting';

-- If using VARCHAR (standard for this project likely), no schema change needed, 
-- but this file serves as documentation that 'lab' and 'xray' are now valid state values.

-- Optional: Update existing records if needed (none expected for new feature)
