-- این migration را فقط یک‌بار روی دیتابیس stureg اجرا کنید.
ALTER TABLE users
  ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN last_login_at DATETIME NULL,
  ADD INDEX idx_users_role_active(role,is_active);
