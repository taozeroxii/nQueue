ALTER TABLE queues ADD COLUMN display_order INT DEFAULT 0;
CREATE INDEX idx_display_order ON queues(display_order);
-- Initialize existing display_order with id
UPDATE queues SET display_order = id;
