-- این migration را یک‌بار در دیتابیس stureg اجرا کنید.
-- همه فیلدهای این بخش به‌جز موارد قبلی اختیاری هستند.

ALTER TABLE student_profiles
  ADD COLUMN grade VARCHAR(50) NULL AFTER user_id,
  ADD COLUMN study_field VARCHAR(100) NULL AFTER grade,
  ADD COLUMN strengths TEXT NULL AFTER study_field,
  ADD COLUMN difficult_subjects TEXT NULL AFTER strengths,
  ADD COLUMN academic_goal TEXT NULL AFTER difficult_subjects,
  ADD COLUMN learning_style VARCHAR(100) NULL AFTER academic_goal,
  ADD COLUMN interests TEXT NULL AFTER learning_style,
  ADD COLUMN current_mood VARCHAR(80) NULL AFTER interests,
  ADD COLUMN communication_preference VARCHAR(100) NULL AFTER current_mood,
  ADD COLUMN study_resources VARCHAR(100) NULL AFTER communication_preference,
  ADD COLUMN support_request VARCHAR(150) NULL AFTER study_resources,
  ADD COLUMN student_note TEXT NULL AFTER support_request;
