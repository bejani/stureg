-- این migration را یک‌بار داخل دیتابیس stureg اجرا کنید.
-- اطلاعات اختیاری هستند و دانش‌آموز می‌تواند «ترجیح می‌دهم پاسخ ندهم» را انتخاب کند.

ALTER TABLE student_profiles
  ADD COLUMN household_status VARCHAR(80) NULL AFTER consent_at,
  ADD COLUMN primary_caregiver VARCHAR(150) NULL AFTER household_status,
  ADD COLUMN caregiver_phone VARCHAR(30) NULL AFTER primary_caregiver;
