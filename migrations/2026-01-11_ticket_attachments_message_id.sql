-- Run on your database (web_agency_app)
-- Adds message_id to ticket_attachments so attachments can be displayed inside the chat flow.

-- 1) Add column
ALTER TABLE ticket_attachments
  ADD COLUMN message_id INT NULL AFTER ticket_id;

-- 2) Index
CREATE INDEX idx_ta_message_id ON ticket_attachments(message_id);

-- 3) FK (set NULL if a message is deleted)
ALTER TABLE ticket_attachments
  ADD CONSTRAINT fk_ta_message
    FOREIGN KEY (message_id) REFERENCES ticket_messages(id)
    ON DELETE SET NULL;
