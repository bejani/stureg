CREATE DATABASE IF NOT EXISTS stureg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stureg;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mobile VARCHAR(20) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student','admin','counselor') NOT NULL DEFAULT 'student',
  name VARCHAR(150) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  INDEX idx_users_role_active(role,is_active)
);
CREATE TABLE student_profiles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  grade VARCHAR(50), study_field VARCHAR(100), strengths TEXT, difficult_subjects TEXT,
  academic_goal TEXT, learning_style VARCHAR(100), interests TEXT, current_mood VARCHAR(80),
  communication_preference VARCHAR(100), study_resources VARCHAR(100), support_request VARCHAR(150), student_note TEXT,
  national_id VARCHAR(20), birth_date DATE, gender VARCHAR(30), address TEXT,
  emergency_contact VARCHAR(150), emergency_phone VARCHAR(30),
  health_notes TEXT, allergies TEXT, counseling_notes TEXT,
  parent_name VARCHAR(150), parent_phone VARCHAR(30), parent_relation VARCHAR(50),
  consent TINYINT(1) NOT NULL DEFAULT 0, consent_at DATETIME NULL, consent_education TINYINT(1) NOT NULL DEFAULT 0, consent_health TINYINT(1) NOT NULL DEFAULT 0, consent_contact TINYINT(1) NOT NULL DEFAULT 0, consent_counseling TINYINT(1) NOT NULL DEFAULT 0,
  household_status VARCHAR(80), primary_caregiver VARCHAR(150), caregiver_phone VARCHAR(30),
  emergency_flag TINYINT(1) NOT NULL DEFAULT 0,
  case_status ENUM('new','in_progress','referred','done','closed') NOT NULL DEFAULT 'new',
  followup_note TEXT, next_followup_date DATE, last_followup_at DATETIME,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  sender_role ENUM('student','admin') NOT NULL,
  body TEXT NOT NULL,
  category ENUM('general','education','counseling','health','family','urgent') NOT NULL DEFAULT 'general',
  is_urgent TINYINT(1) NOT NULL DEFAULT 0,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, actor_id INT UNSIGNED NULL, action VARCHAR(80) NOT NULL, entity_type VARCHAR(50) NOT NULL, entity_id INT UNSIGNED NULL, details TEXT NULL, ip_address VARCHAR(45) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL, INDEX idx_activity_actor (actor_id), INDEX idx_activity_entity (entity_type,entity_id), INDEX idx_activity_created (created_at)
);
CREATE TABLE admin_notes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  admin_id INT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_admin_notes_student (student_id)
);
CREATE TABLE academic_years (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(30) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE student_enrollments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, student_id INT UNSIGNED NOT NULL,
  academic_year_id INT UNSIGNED NOT NULL, grade VARCHAR(50), study_field VARCHAR(100),
  status ENUM('active','archived') NOT NULL DEFAULT 'active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_student_year(student_id,academic_year_id),
  FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
);
CREATE TABLE referrals (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, student_id INT UNSIGNED NOT NULL,
  counselor_id INT UNSIGNED NOT NULL, created_by INT UNSIGNED NOT NULL,
  subject VARCHAR(150) NOT NULL, reason TEXT NOT NULL,
  priority ENUM('normal','important','urgent') NOT NULL DEFAULT 'normal',
  status ENUM('new','seen','scheduled','in_progress','closed') NOT NULL DEFAULT 'new',
  counselor_note TEXT, next_followup_date DATE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(counselor_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE CASCADE
);
-- After creating an admin password hash with: php -r "echo password_hash('ChangeMe!', PASSWORD_DEFAULT), PHP_EOL;"
-- INSERT INTO users (mobile,password_hash,role,name) VALUES ('09120000000','PASTE_HASH_HERE','admin','مدیر سامانه');
