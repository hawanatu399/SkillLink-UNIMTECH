-- =============================================================
-- SkillLink UNIMTECH — Database Schema
-- =============================================================
--
-- This schema was reconstructed from the SQL queries used
-- throughout the application (config/database.php connects to
-- a database named `skilllink_unimtech`).
--
-- To set up:
--   1. CREATE DATABASE skilllink_unimtech;
--   2. mysql -u root skilllink_unimtech < schema.sql
--
-- =============================================================

CREATE DATABASE IF NOT EXISTS skilllink_unimtech;
USE skilllink_unimtech;

-- -------------------------------------------------------------
-- USERS
-- Stores students, lecturers, and admins in a single table,
-- distinguished by the `role` column.
-- -------------------------------------------------------------

CREATE TABLE users (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    full_name         VARCHAR(150) NOT NULL,
    student_id        VARCHAR(50)  NULL,
    email             VARCHAR(150) NOT NULL UNIQUE,
    password          VARCHAR(255) NOT NULL,
    role              ENUM('student', 'lecturer', 'admin') NOT NULL DEFAULT 'student',
    department        VARCHAR(100) NULL,
    programme         VARCHAR(100) NULL,
    level             VARCHAR(10)  NULL,
    gender            VARCHAR(20)  NULL,
    phone             VARCHAR(30)  NULL,
    bio               TEXT         NULL,
    interests         TEXT         NULL,
    profile_picture   VARCHAR(255) NULL,
    reputation_points INT NOT NULL DEFAULT 0,
    status            ENUM('Active', 'Suspended') NOT NULL DEFAULT 'Active',
    created_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- SKILLS
-- Skills a student adds to their profile, optionally verified
-- by a lecturer.
-- -------------------------------------------------------------

CREATE TABLE skills (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    skill_name    VARCHAR(150) NOT NULL,
    skill_level   VARCHAR(50)  NOT NULL,
    description   TEXT NULL,
    verified      TINYINT(1) NOT NULL DEFAULT 0,
    verified_by   INT NULL,
    verified_at   TIMESTAMP NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- RESOURCES
-- Learning resources uploaded by students, moderated by
-- lecturers/admins.
-- -------------------------------------------------------------

CREATE TABLE resources (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    title          VARCHAR(200) NOT NULL,
    description    TEXT NOT NULL,
    category       VARCHAR(100) NOT NULL,
    file_name      VARCHAR(255) NOT NULL,
    file_path      VARCHAR(255) NOT NULL,
    status         ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    approved_by    INT NULL,
    approved_at    TIMESTAMP NULL,
    uploaded_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- STUDY GROUPS
-- -------------------------------------------------------------

CREATE TABLE study_groups (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    creator_id     INT NOT NULL,
    group_name     VARCHAR(150) NOT NULL,
    description    TEXT NOT NULL,
    category       VARCHAR(100) NOT NULL,
    status         ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    approved_by    INT NULL,
    approved_at    TIMESTAMP NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- STUDY GROUP MEMBERS
-- Join table between study_groups and users.
-- -------------------------------------------------------------

CREATE TABLE study_group_members (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    group_id    INT NOT NULL,
    user_id     INT NOT NULL,
    joined_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_membership (group_id, user_id),
    FOREIGN KEY (group_id) REFERENCES study_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- COLLABORATION REQUESTS
-- One student requesting to collaborate with another.
-- -------------------------------------------------------------

CREATE TABLE collaboration_requests (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    sender_id     INT NOT NULL,
    receiver_id   INT NOT NULL,
    message       TEXT NULL,
    status        ENUM('Pending', 'Accepted', 'Rejected') NOT NULL DEFAULT 'Pending',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- REVIEWS
-- A student reviewing another student after a collaboration.
-- -------------------------------------------------------------

CREATE TABLE reviews (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    reviewer_id         INT NOT NULL,
    reviewed_user_id    INT NOT NULL,
    collaboration_id    INT NOT NULL,
    rating              TINYINT NOT NULL,
    review              TEXT NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (collaboration_id) REFERENCES collaboration_requests(id) ON DELETE CASCADE,

    CONSTRAINT chk_rating_range CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- MARKETPLACE LISTINGS
-- Lets a student explicitly post what they can teach or what
-- they're looking to learn, rather than waiting to be found via
-- search or the recommendation engine. Complements the passive
-- skills table with an active, browsable board.
-- -------------------------------------------------------------

CREATE TABLE marketplace_listings (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    listing_type   ENUM('Offering', 'Seeking') NOT NULL,
    skill_name     VARCHAR(150) NOT NULL,
    description    TEXT NOT NULL,
    availability   VARCHAR(150) NULL,
    status         ENUM('Open', 'Closed') NOT NULL DEFAULT 'Open',
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- NOTIFICATIONS
-- -------------------------------------------------------------

CREATE TABLE notifications (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    type          VARCHAR(50) NOT NULL,
    message       TEXT NOT NULL,
    related_id    INT NULL,
    is_read       TINYINT(1) NOT NULL DEFAULT 0,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================================
-- SEED DATA (optional)
-- =============================================================
-- The registration form only ever creates 'student' accounts, so
-- lecturer/admin accounts must be created manually. Example below
-- (password for both is: Password123)
-- =============================================================

INSERT INTO users (full_name, email, password, role, department)
VALUES
    ('Dr. Admin User', 'admin@unimtech.edu.sl', '$2b$10$G09WU1PovbMf5RCQDFQhB..tOjzbIsbHczJpz107EsE8zSEFZwF.6', 'admin', 'Computer Science'),
    ('Dr. Sample Lecturer', 'lecturer@unimtech.edu.sl', '$2b$10$G09WU1PovbMf5RCQDFQhB..tOjzbIsbHczJpz107EsE8zSEFZwF.6', 'lecturer', 'Computer Science');
