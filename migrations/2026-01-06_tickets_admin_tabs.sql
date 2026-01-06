-- Run these changes on your database (web_agency_app)
-- Adds: deleted_at (soft delete), sort_order (drag & drop), status limited to open/resolved

-- 1) Add columns (if they don't exist yet)
ALTER TABLE tickets
  ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'open',
  ADD COLUMN deleted_at DATETIME NULL,
  ADD COLUMN sort_order INT NOT NULL DEFAULT 0,
  ADD COLUMN updated_at DATETIME NULL;

-- 2) Normalize existing statuses (optional)
UPDATE tickets SET status = 'open'
WHERE status IS NULL OR status NOT IN ('open','resolved');

-- 3) Ensure updated_at populated
UPDATE tickets SET updated_at = NOW() WHERE updated_at IS NULL;

-- Note: If your tickets table already has `status` and/or `updated_at`,
-- remove the corresponding ADD COLUMN lines before running.
