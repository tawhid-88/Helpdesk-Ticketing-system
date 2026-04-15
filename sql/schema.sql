-- ============================================================================
-- NSU Helpdesk Ticketing System - Database Schema
-- Database  : MySQL 8.x
-- Charset   : utf8mb4
-- ============================================================================

CREATE DATABASE IF NOT EXISTS helpdesk_db
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE helpdesk_db;

-- ============================================================================
-- 1. users - all NSU members (student, faculty, staff)
-- ============================================================================
CREATE TABLE users (
    id            INT            AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)   NOT NULL,
    email         VARCHAR(150)   NOT NULL UNIQUE,
    password_md5  CHAR(32)       NOT NULL  COMMENT 'MD5 hex digest of password',
    role          ENUM('student','faculty','staff') NOT NULL DEFAULT 'student',
    created_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================================
-- 2. categories - ticket classification groups
-- ============================================================================
CREATE TABLE categories (
    id            INT            AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)   NOT NULL UNIQUE,
    description   TEXT           NULL,
    created_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================================
-- 3. tickets - support requests submitted by users
-- ============================================================================
CREATE TABLE tickets (
    id                INT            AUTO_INCREMENT PRIMARY KEY,
    user_id           INT            NOT NULL          COMMENT 'FK -> users.id  (ticket creator)',
    assigned_staff_id INT            NULL              COMMENT 'FK -> users.id  (assigned staff)',
    category_id       INT            NULL              COMMENT 'FK -> categories.id',
    subject           VARCHAR(255)   NOT NULL,
    description       TEXT           NOT NULL,
    priority          ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    status            ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
    created_at        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign keys
    CONSTRAINT fk_ticket_creator   FOREIGN KEY (user_id)           REFERENCES users(id)      ON DELETE CASCADE,
    CONSTRAINT fk_ticket_staff     FOREIGN KEY (assigned_staff_id) REFERENCES users(id)      ON DELETE SET NULL,
    CONSTRAINT fk_ticket_category  FOREIGN KEY (category_id)       REFERENCES categories(id) ON DELETE SET NULL,

    -- Indexes for common filters
    INDEX idx_ticket_status   (status),
    INDEX idx_ticket_priority (priority)
) ENGINE=InnoDB;

-- ============================================================================
-- 4. comments - replies and internal notes on a ticket
-- ============================================================================
CREATE TABLE comments (
    id            INT            AUTO_INCREMENT PRIMARY KEY,
    ticket_id     INT            NOT NULL    COMMENT 'FK -> tickets.id',
    user_id       INT            NOT NULL    COMMENT 'FK -> users.id  (author)',
    body          TEXT           NOT NULL,
    is_internal   TINYINT(1)     NOT NULL DEFAULT 0  COMMENT '1 = staff-only internal note',
    created_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_comment_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_author FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,

    INDEX idx_comment_ticket (ticket_id)
) ENGINE=InnoDB;

-- ============================================================================
-- 5. attachments - files uploaded to tickets or comments
-- ============================================================================
CREATE TABLE attachments (
    id            INT            AUTO_INCREMENT PRIMARY KEY,
    ticket_id     INT            NOT NULL    COMMENT 'FK -> tickets.id',
    comment_id    INT            NULL        COMMENT 'FK -> comments.id (optional)',
    file_name     VARCHAR(255)   NOT NULL    COMMENT 'Original file name',
    file_path     VARCHAR(500)   NOT NULL    COMMENT 'Server storage path',
    uploaded_by   INT            NOT NULL    COMMENT 'FK -> users.id',
    created_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_attach_ticket  FOREIGN KEY (ticket_id)  REFERENCES tickets(id)  ON DELETE CASCADE,
    CONSTRAINT fk_attach_comment FOREIGN KEY (comment_id)  REFERENCES comments(id) ON DELETE SET NULL,
    CONSTRAINT fk_attach_user    FOREIGN KEY (uploaded_by) REFERENCES users(id)    ON DELETE CASCADE,

    INDEX idx_attach_ticket (ticket_id)
) ENGINE=InnoDB;

-- ============================================================================
-- 6. ticket_history - audit log of field-level changes
-- ============================================================================
CREATE TABLE ticket_history (
    id              INT            AUTO_INCREMENT PRIMARY KEY,
    ticket_id       INT            NOT NULL    COMMENT 'FK -> tickets.id',
    changed_by      INT            NOT NULL    COMMENT 'FK -> users.id',
    field_changed   VARCHAR(50)    NOT NULL    COMMENT 'Column name that was modified',
    old_value       TEXT           NULL,
    new_value       TEXT           NULL,
    created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_history_ticket FOREIGN KEY (ticket_id)  REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_history_user   FOREIGN KEY (changed_by) REFERENCES users(id)   ON DELETE CASCADE,

    INDEX idx_history_ticket (ticket_id)
) ENGINE=InnoDB;
