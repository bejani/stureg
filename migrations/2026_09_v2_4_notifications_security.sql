-- این migration را فقط یک‌بار روی دیتابیس stureg اجرا کنید.
CREATE TABLE IF NOT EXISTS notifications (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 title VARCHAR(180) NOT NULL,
 body TEXT NOT NULL,
 link VARCHAR(255) NULL,
 is_read TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_notifications_user(user_id,is_read,created_at)
);
CREATE TABLE IF NOT EXISTS login_attempts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 mobile VARCHAR(20) NOT NULL,
 ip_address VARCHAR(45) NULL,
 was_successful TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_login_attempts_lookup(mobile,ip_address,created_at)
);
