-- English_web / English_admin_web Database Migration
-- SQL Script for Courses and Enrollments

CREATE TABLE IF NOT EXISTS `courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `instructor` VARCHAR(255) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `image_url` VARCHAR(255) DEFAULT NULL,
    `is_published` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `course_episodes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `episode_number` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `duration` VARCHAR(50) DEFAULT NULL,
    `video_url` VARCHAR(500) DEFAULT NULL,
    `is_locked` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `course_materials` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `episode_number` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `file_url` VARCHAR(500) NOT NULL,
    `size_mb` DECIMAL(5,2) DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `course_enrollments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `payment_slip_url` VARCHAR(500) DEFAULT NULL,
    `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `approved_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Mock Initial Data for Testing
INSERT INTO `courses` (`title`, `category`, `instructor`, `price`, `is_published`) VALUES
('ติวเข้ม วิทย์ ป.6', 'วิทย์', 'Kru. Nam', 1500.00, 1),
('คณิตศาสตร์ ม.3 พิชิตสอบเข้า', 'คณิต', 'Kru. Som', 2000.00, 1);

-- Insert Mock Episodes for Science Course
INSERT INTO `course_episodes` (`course_id`, `episode_number`, `title`, `duration`, `video_url`, `is_locked`) VALUES
(1, 1, 'บทที่ 1: Introduction (Test Video)', '03:54 mins', 'njX2bu-_Vw4', 0),
(1, 2, 'บทที่ 2: โครงสร้างและหน้าที่ของเซลล์', '12:45 mins', '', 1),
(1, 3, 'บทที่ 3: ระบบร่างกายของมนุษย์', '45:34 mins', '', 1);

-- Insert Mock Materials
INSERT INTO `course_materials` (`course_id`, `episode_number`, `title`, `file_url`, `size_mb`) VALUES
(1, 1, 'Sheet EP.1', '#', 2.50),
(1, 2, 'Sheet EP.2', '#', 1.80),
(1, 3, 'Sheet EP.3', '#', 3.20);
