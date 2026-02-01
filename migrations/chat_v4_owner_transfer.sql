-- Chat v4: owner transfer (WhatsApp-like)
-- Adds conversations.owner_id + conversation_participants.joined_at
-- Safe to run multiple times on MySQL 8+ (IF NOT EXISTS).

ALTER TABLE conversations
  ADD COLUMN IF NOT EXISTS owner_id INT NULL AFTER created_by;

UPDATE conversations SET owner_id = created_by WHERE owner_id IS NULL;

ALTER TABLE conversation_participants
  ADD COLUMN IF NOT EXISTS joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Backfill joined_at for existing rows (best-effort)
UPDATE conversation_participants SET joined_at = NOW() WHERE joined_at IS NULL;

-- Helpful indexes
CREATE INDEX IF NOT EXISTS idx_conv_owner ON conversations(owner_id);
CREATE INDEX IF NOT EXISTS idx_cp_joined ON conversation_participants(conversation_id, left_at, joined_at);
