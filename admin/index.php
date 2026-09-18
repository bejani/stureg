<?php
require __DIR__ . '/../includes/functions.php';
require_login('admin');

$form_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    if (isset($_POST['create_student'])) {
        $name = trim($_POST['name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if ($name === '' || $mobile === '' || strlen($password) < 6) {
            $form_error = 'نام، شماره موبایل و رمز حداقل ۶ کاراکتری را وارد کنید.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = db()->prepare("INSERT INTO users (mobile, password_hash, role, name) VALUES (?, ?, 'student', ?)");
                $stmt->execute([$mobile, $hash, $name]);
                flash('کاربر جدید با موفقیت ساخته شد؛ اطلاعات ورود را به دانش‌آموز بدهید.');
                redirect('/admin/index.php');
            } catch (PDOException $e) {
                if ((string)$e->getCode() === '23000') {
                    $form_error = 'این شماره موبایل قبلاً ثبت شده است.';
                } else {
                    $form_error = 'ذخیره کاربر انجام نشد. مطمئن شوید جدول users در دیتابیس stureg ساخته شده است.';
                }
            }
        }
    } elseif (isset($_POST['flag'])) {
        db()->prepare('UPDATE student_profiles SET emergency_flag = ? WHERE user_id = ?')
            ->execute([$_POST['flag'] === '1' ? 1 : 0, (int)$_POST['student_id']]);
        flash('وضعیت پیگیری به‌روزرسانی شد.');
        redirect('/admin/index.php');
    } elseif (isset($_POST['body'])) {
        $body = trim($_POST['body']);
        if ($body !== '') {
            db()->prepare("INSERT INTO messages (student_id, sender_role, body) VALUES (?, 'admin', ?)")
                ->execute([(int)$_POST['student_id'], $body]);
            flash('پیام ارسال شد.');
        }
        redirect('/admin/index.php');
    }
}

$students = db()->query("SELECT u.id, u.name, u.mobile, p.parent_name, p.parent_phone, p.consent, p.emergency_flag, p.updated_at,
    (SELECT COUNT(*) FROM messages m WHERE m.student_id = u.id AND m.sender_role = 'student' AND m.is_read = 0) AS unread
    FROM users u LEFT JOIN student_profiles p ON p.user_id = u.id
    WHERE u.role = 'student'
    ORDER BY p.emergency_flag DESC, u.created_at DESC");

layout_start('پنل مدیریت');
?>
<div class="page-head">
    <div><p class="eyebrow">پنل مدیریت</p><h1>دانش‌آموزان</h1></div>
    <div class="admin-actions"><span class="badge"><?= e($students->rowCount()) ?> نفر</span><a class="button secondary" href="<?= e(url('/admin/export.php?format=csv')) ?>">خروجی Excel</a><a class="button secondary" href="<?= e(url('/admin/export.php?format=json')) ?>">پشتیبان JSON</a></div>
</div>

<?php if ($form_error): ?>
    <div class="alert error"><?= e($form_error) ?></div>
<?php endif; ?>

<div class="card create-user">
    <h2>تعریف دانش‌آموز جدید</h2>
    <p class="muted">پس از ساخت حساب، شماره موبایل و رمز اولیه را شخصاً و از مسیر امن در اختیار دانش‌آموز قرار دهید.</p>
    <form class="form-grid" method="post" action="<?= e(url('/admin/index.php')) ?>">
        <input type="hidden" name="csrf" value="<?= csrf() ?>">
        <input type="hidden" name="create_student" value="1">
        <label>نام و نام خانوادگی<input name="name" value="<?= e($_POST['name'] ?? '') ?>" required></label>
        <label>شماره موبایل<input name="mobile" value="<?= e($_POST['mobile'] ?? '') ?>" inputmode="tel" required></label>
        <label>رمز اولیه<input type="password" name="password" minlength="6" required></label>
        <div><button class="button" type="submit">ساخت کاربر</button></div>
    </form>
</div>

<div class="card table-wrap">
    <table><thead><tr><th>نام</th><th>موبایل</th><th>والد</th><th>رضایت</th><th>وضعیت</th><th>اقدام</th></tr></thead><tbody>
    <?php while ($s = $students->fetch()): ?>
        <tr class="<?= !empty($s['emergency_flag']) ? 'urgent' : '' ?>">
            <td><strong><?= e($s['name']) ?></strong><?php if ($s['unread']): ?> <span class="badge">پیام جدید</span><?php endif; ?></td>
            <td><?= e($s['mobile']) ?></td>
            <td><?= e($s['parent_name'] ?? '—') ?> <small><?= e($s['parent_phone'] ?? '') ?></small></td>
            <td><?= !empty($s['consent']) ? 'تأیید شده' : 'تکمیل نشده' ?></td>
            <td><?= !empty($s['emergency_flag']) ? 'نیازمند پیگیری' : 'عادی' ?></td>
            <td>
                <a href="<?= e(url('/admin/student.php?id=' . $s['id'])) ?>">مشاهده / پیام</a>
                <form class="inline" method="post" action="<?= e(url('/admin/index.php')) ?>">
                    <input type="hidden" name="csrf" value="<?= csrf() ?>">
                    <input type="hidden" name="student_id" value="<?= (int)$s['id'] ?>">
                    <button name="flag" value="<?= empty($s['emergency_flag']) ? 1 : 0 ?>"><?= empty($s['emergency_flag']) ? 'علامت اضطراری' : 'رفع علامت' ?></button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody></table>
</div>
<?php layout_end(); ?>
