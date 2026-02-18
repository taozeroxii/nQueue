-- Migration: Add 'not_found' status to queues table
-- Date: 2026-02-18
-- Description: เพิ่มสถานะ 'not_found' (เรียกไม่พบ) ในระบบคิว

ALTER TABLE queues MODIFY COLUMN status ENUM('waiting', 'called', 'completed', 'lab', 'xray', 'not_found') DEFAULT 'waiting';
