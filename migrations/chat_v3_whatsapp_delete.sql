-- Chat v3: WhatsApp-like delete/hide/leave
-- MySQL 8+ recommended (uses ADD COLUMN IF NOT EXISTS).

-- Conversations: soft-delete ("Șterge pentru toți")
ALTER TABLE conversations
  ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL,
  ADD COLUMN IF NOT EXISTS deleted_by INT NULL;

-- Participant-level: hide ("Ascunde") + leave ("Părăsește")
ALTER TABLE conversation_participants
  ADD COLUMN IF NOT EXISTS hidden_at DATETIME NULL,
  ADD COLUMN IF NOT EXISTS left_at DATETIME NULL,
  ADD COLUMN IF NOT EXISTS last_delivered_at DATETIME NULL,
  ADD COLUMN IF NOT EXISTS last_read_at DATETIME NULL;

-- Optional: ensure General chat exists (safe)
INSERT IGNORE INTO conversations (id, type, title, created_by, created_at)
VALUES (1, 'general', 'General', 1, NOW());
