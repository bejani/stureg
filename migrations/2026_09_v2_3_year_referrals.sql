-- این migration را فقط یک‌بار روی دیتابیس stureg اجرا کنید.
CREATE TABLE IF NOT EXISTS academic_years (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(30) NOT NULL UNIQUE,
 is_active TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS student_enrollments (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 student_id INT UNSIGNED NOT NULL,
 academic_year_id INT UNSIGNED NOT NULL,
 grade VARCHAR(50), study_field VARCHAR(100), status ENUM('active','archived') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_student_year(student_id,academic_year_id),
 FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS referrals (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 student_id INT UNSIGNED NOT NULL,
 counselor_id INT UNSIGNED NOT NULL,
 created_by INT UNSIGNED NOT NULL,
 subject VARCHAR(150) NOT NULL,
 reason TEXT NOT NULL,
 priority ENUM('normal','important','urgent') NOT NULL DEFAULT 'normal',
 status ENUM('new','seen','scheduled','in_progress','closed') NOT NULL DEFAULT 'new',
 counselor_note TEXT NULL,
 next_followup_date DATE NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(counselor_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_referral_counselor(counselor_id), INDEX idx_referral_status(status)
);
