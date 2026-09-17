CREATE DATABASE IF NOT EXISTS stureg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stureg;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mobile VARCHAR(20) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student','admin') NOT NULL DEFAULT 'student',
  name VARCHAR(150) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
  consent TINYINT(1) NOT NULL DEFAULT 0, consent_at DATETIME NULL,
  household_status VARCHAR(80), primary_caregiver VARCHAR(150), caregiver_phone VARCHAR(30),
  emergency_flag TINYINT(1) NOT NULL DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  sender_role ENUM('student','admin') NOT NULL,
  body TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);
-- After creating an admin password hash with: php -r "echo password_hash('ChangeMe!', PASSWORD_DEFAULT), PHP_EOL;"
-- INSERT INTO users (mobile,password_hash,role,name) VALUES ('09120000000','PASTE_HASH_HERE','admin','مدیر سامانه');
