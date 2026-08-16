<?php
require_once __DIR__ . '/config/config.php';

try {
    $db = getDB();

    $sql = "
    CREATE TABLE IF NOT EXISTS `class_schedules` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `course_id` int(11) DEFAULT NULL,
      `title` varchar(255) NOT NULL,
      `start_datetime` datetime NOT NULL,
      `end_datetime` datetime NOT NULL,
      `color` varchar(20) DEFAULT '#4F46E5',
      `notes` text,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `class_attendees` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `schedule_id` int(11) NOT NULL,
      `student_id` int(11) NOT NULL,
      `status` enum('scheduled', 'attended', 'missed', 'cancelled') DEFAULT 'scheduled',
      PRIMARY KEY (`id`),
      FOREIGN KEY (`schedule_id`) REFERENCES `class_schedules`(`id`) ON DELETE CASCADE,
      FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $db->exec($sql);
    echo "<h2>Calendar Tables Migrated Successfully!</h2>";
    echo "<p>You can now delete this file (migrate_calendar.php).</p>";
    echo "<a href='index.php'>Go to Admin Dashboard</a>";

} catch (PDOException $e) {
    die("Migration Failed: " . $e->getMessage());
}
