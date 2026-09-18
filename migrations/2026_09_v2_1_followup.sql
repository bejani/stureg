-- این migration را فقط یک‌بار روی دیتابیس stureg اجرا کنید.
ALTER TABLE student_profiles
  ADD COLUMN case_status ENUM('new','in_progress','referred','done','closed') NOT NULL DEFAULT 'new',
  ADD COLUMN followup_note TEXT NULL,
  ADD COLUMN next_followup_date DATE NULL,
  ADD COLUMN last_followup_at DATETIME NULL;

CREATE TABLE IF NOT EXISTS admin_notes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  admin_id INT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_admin_notes_student (student_id)
);
