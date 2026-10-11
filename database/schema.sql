-- ==============================================================
-- Skema Database: KawalAman (Sistem Pelaporan & Edukasi Bullying)
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `db_kawalaman` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_kawalaman`;

-- -------------------------------------------------------------
-- Tabel 1: users (Akun Tim Satgas & Administrator)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'satgas') NOT NULL DEFAULT 'satgas',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Tabel 2: reports (Laporan Kasus Bullying)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_code` VARCHAR(30) NOT NULL UNIQUE,
    `reporter_name` VARCHAR(100) NOT NULL,
    `is_anonymous` TINYINT(1) NOT NULL DEFAULT 0,
    `reporter_email` VARCHAR(100) NOT NULL,
    `category` ENUM('verbal', 'fisik', 'relasional', 'cyber', 'lainnya') NOT NULL DEFAULT 'verbal',
    `incident_date` DATE NOT NULL,
    `incident_location` VARCHAR(255) NOT NULL,
    `incident_description` TEXT NOT NULL,
    `evidence_file` VARCHAR(255) NULL,
    `evidence_type` ENUM('image', 'video', 'none') NOT NULL DEFAULT 'none',
    `status` ENUM('menunggu', 'diproses', 'selesai', 'ditolak') NOT NULL DEFAULT 'menunggu',
    `satgas_notes` TEXT NULL,
    `handled_by` INT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ticket` (`ticket_code`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`),
    CONSTRAINT `fk_reports_handled_by` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Tabel 3: report_logs (Riwayat Tindakan & Perubahan Status)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `report_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `report_id` INT NOT NULL,
    `user_id` INT NULL,
    `status_before` VARCHAR(50) NULL,
    `status_after` VARCHAR(50) NOT NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_report_id` (`report_id`),
    CONSTRAINT `fk_logs_report_id` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_logs_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Data Awal: Akun Petugas Satgas & Admin Default
-- Password satgas: satgas123
-- Password admin: admin123
-- -------------------------------------------------------------
INSERT INTO `users` (`name`, `username`, `password`, `role`)
SELECT 'Satgas Anti-Bullying', 'satgas', '$2y$12$5Uk89uEris3CIzY8rw/5zuO6tidWnYrkOCQa1AOENoGSueXKk2i5e', 'satgas'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `username` = 'satgas');

INSERT INTO `users` (`name`, `username`, `password`, `role`)
SELECT 'Administrator Utama', 'admin', '$2y$12$LKMDvAIPVI0IhNppNzKxy.CmsQS0XR2fzXVyVwI4emJ.tHsNeAnqe', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `username` = 'admin');
