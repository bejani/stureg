-- اجرای این اسکریپت پس از انتخاب دیتابیس stureg
-- شماره ورود ادمین: 09120000000
-- رمز ورود ادمین: admin@123

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mobile` VARCHAR(20) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('student','admin') NOT NULL DEFAULT 'student',
  `name` VARCHAR(150) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`mobile`, `password_hash`, `role`, `name`)
VALUES (
  '09120000000',
  '$2b$10$jfW/mZx4DPZHX6Z1gk/RX.bfpAcBI23oG2NquKx1PzCSqcj0hNAHS',
  'admin',
  'مدیر سامانه'
)
ON DUPLICATE KEY UPDATE
  `password_hash` = VALUES(`password_hash`),
  `role` = 'admin',
  `name` = VALUES(`name`);
