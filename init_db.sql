-- ========================================
-- DATABASE: web_agency_app
-- ========================================
CREATE DATABASE IF NOT EXISTS web_agency_app
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE web_agency_app;

-- ========================================
-- TABEL USERS
-- (aliniat cu codul: company/phone/address/avatar etc.)
-- ========================================
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','client','employee','staff') NOT NULL DEFAULT 'employee',

  company VARCHAR(255) NULL,
  phone VARCHAR(50) NULL,
  address VARCHAR(255) NULL,
  city VARCHAR(100) NULL,
  country VARCHAR(100) NULL,
  vat VARCHAR(50) NULL,
  avatar VARCHAR(255) NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- TABEL MESSAGES (CHAT)
-- ========================================
CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_messages_user_id (user_id),
  CONSTRAINT fk_messages_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- TABEL TASKS
-- (fără ALTER dublat)
-- ========================================
CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  status ENUM('pending','done') DEFAULT 'pending',
  priority TINYINT NOT NULL DEFAULT 3,
  is_favorite TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================
-- TICKETS
-- ========================================
CREATE TABLE IF NOT EXISTS tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  subject VARCHAR(255) NOT NULL,
    priority TINYINT NOT NULL DEFAULT 3,
  status ENUM('open','resolved') NOT NULL DEFAULT 'open',
  sort_order INT NOT NULL DEFAULT 0,
  deleted_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_tickets_client_id (client_id),
  INDEX idx_tickets_status_deleted (status, deleted_at),

  CONSTRAINT fk_tickets_client
    FOREIGN KEY (client_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ticket_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id INT NOT NULL,
  sender_id INT NOT NULL,
  body TEXT NOT NULL,
  is_internal TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_tm_ticket_id (ticket_id),
  INDEX idx_tm_sender_id (sender_id),

  CONSTRAINT fk_tm_ticket
    FOREIGN KEY (ticket_id) REFERENCES tickets(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_tm_sender
    FOREIGN KEY (sender_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ticket_attachments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id INT NOT NULL,
  message_id INT NULL,
  uploaded_by INT NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_name VARCHAR(255) NOT NULL,
  mime_type VARCHAR(100) NOT NULL,
  size_bytes INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  INDEX idx_ta_ticket_id (ticket_id),
  INDEX idx_ta_message_id (message_id),
  INDEX idx_ta_uploaded_by (uploaded_by),

  CONSTRAINT fk_ta_ticket
    FOREIGN KEY (ticket_id) REFERENCES tickets(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_ta_message
    FOREIGN KEY (message_id) REFERENCES ticket_messages(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_ta_uploader
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



