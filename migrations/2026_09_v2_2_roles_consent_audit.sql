-- این migration را فقط یک‌بار روی دیتابیس stureg اجرا کنید.
ALTER TABLE users
  MODIFY COLUMN role ENUM('student','admin','counselor') NOT NULL DEFAULT 'student';

ALTER TABLE student_profiles
  ADD COLUMN consent_education TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN consent_health TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN consent_contact TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN consent_counseling TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE messages
  ADD COLUMN category ENUM('general','education','counseling','health','family','urgent') NOT NULL DEFAULT 'general',
  ADD COLUMN is_urgent TINYINT(1) NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_id INT UNSIGNED NULL,
  action VARCHAR(80) NOT NULL,
  entity_type VARCHAR(50) NOT NULL,
  entity_id INT UNSIGNED NULL,
  details TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_activity_actor (actor_id),
  INDEX idx_activity_entity (entity_type, entity_id),
  INDEX idx_activity_created (created_at)
);
