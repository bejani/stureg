<?php
require __DIR__ . '/../includes/functions.php';
require_staff();
$form_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (isset($_POST['create_counselor'])) {
        if (user()['role'] !== 'admin') { http_response_code(403); exit('فقط مدیر سامانه می‌تواند مشاور بسازد.'); }
        $name=trim($_POST['staff_name']??''); $mobile=trim($_POST['staff_mobile']??''); $password=(string)($_POST['staff_password']??'');
        if($name===''||$mobile===''||strlen($password)<8) $form_error='نام، موبایل و رمز مشاور حداقل ۸ کاراکتری لازم است.';
        else try { db()->prepare("INSERT INTO users(mobile,password_hash,role,name) VALUES(?,?, 'counselor',?)")->execute([$mobile,password_hash($password,PASSWORD_DEFAULT),$name]); audit('create','counselor'); flash('حساب مشاور ساخته شد.'); redirect('/admin/index.php'); } catch(PDOException $e){ $form_error='این موبایل قبلاً ثبت شده یا ساخت حساب انجام نشد.'; }
    } elseif (isset($_POST['create_student'])) {
        if (user()['role'] !== 'admin') { http_response_code(403); exit('فقط مدیر سامانه می‌تواند کاربر بسازد.'); }
        $name = trim($_POST['name'] ?? ''); $mobile = trim($_POST['mobile'] ?? ''); $password = (string)($_POST['password'] ?? '');
        if ($name === '' || $mobile === '' || strlen($password) < 6) $form_error = 'نام، شماره موبایل و رمز حداقل ۶ کاراکتری را وارد کنید.';
        else try {
            db()->prepare("INSERT INTO users (mobile,password_hash,role,name) VALUES (?,?,'student',?)")
                ->execute([$mobile, password_hash($password, PASSWORD_DEFAULT), $name]);
            flash('کاربر جدید با موفقیت ساخته شد؛ اطلاعات ورود را به دانش‌آموز بدهید.'); redirect('/admin/index.php');
        } catch (PDOException $e) { $form_error = (string)$e->getCode()==='23000' ? 'این شماره موبایل قبلاً ثبت شده است.' : 'ذخیره کاربر انجام نشد.'; }
    } elseif (isset($_POST['status_update'])) {
        if (user()['role'] !== 'admin') { http_response_code(403); exit('فقط مدیر سامانه می‌تواند وضعیت را تغییر دهد.'); }
        $allowed = ['new','in_progress','referred','done','closed']; $status = $_POST['case_status'] ?? 'new';
        if (in_array($status, $allowed, true)) db()->prepare('UPDATE student_profiles SET case_status=?, last_followup_at=NOW() WHERE user_id=?')->execute([$status,(int)$_POST['student_id']]);
        flash('وضعیت پیگیری به‌روزرسانی شد.'); redirect('/admin/index.php');
    } elseif (isset($_POST['body'])) {
        $body = trim($_POST['body'] ?? ''); if ($body !== '') db()->prepare("INSERT INTO messages(student_id,sender_role,body) VALUES (?, 'admin', ?)")->execute([(int)$_POST['student_id'],$body]);
        flash('پیام ارسال شد.'); redirect('/admin/index.php');
    }
}

$term = trim($_GET['q'] ?? ''); $status = $_GET['status'] ?? ''; $grade = trim($_GET['grade'] ?? '');
$where = ["u.role='student'"]; $params = [];
if ($term !== '') { $where[]='(u.name LIKE ? OR u.mobile LIKE ? OR p.parent_name LIKE ?)'; $params += [$term.'%',$term.'%','%'.$term.'%']; }
if ($status !== '' && in_array($status,['new','in_progress','referred','done','closed'],true)) { $where[]='p.case_status=?'; $params[]=$status; }
if ($grade !== '') { $where[]='p.grade=?'; $params[]=$grade; }
$stats = db()->query("SELECT COUNT(*) total, SUM(p.consent=1) complete, SUM(p.emergency_flag=1) urgent, (SELECT COUNT(*) FROM messages WHERE sender_role='student' AND is_read=0) unread FROM users u LEFT JOIN student_profiles p ON p.user_id=u.id WHERE u.role='student'")->fetch();
$sql="SELECT u.id,u.name,u.mobile,p.grade,p.parent_name,p.parent_phone,p.consent,p.emergency_flag,p.case_status,p.updated_at,p.next_followup_date,
 (SELECT COUNT(*) FROM messages m WHERE m.student_id=u.id AND m.sender_role='student' AND m.is_read=0) unread
 FROM users u LEFT JOIN student_profiles p ON p.user_id=u.id WHERE ".implode(' AND ',$where)." ORDER BY p.emergency_flag DESC, FIELD(p.case_status,'in_progress','referred','new','done','closed'),u.created_at DESC";
$stmt=db()->prepare($sql); $stmt->execute($params); $students=$stmt;
$grades=db()->query("SELECT DISTINCT grade FROM student_profiles WHERE grade IS NOT NULL AND grade<>'' ORDER BY grade")->fetchAll(PDO::FETCH_COLUMN);
$labels=['new'=>'جدید','in_progress'=>'در حال پیگیری','referred'=>'ارجاع‌شده','done'=>'انجام‌شده','closed'=>'بسته‌شده'];
layout_start('پنل مدیریت'); ?>
<div class="page-head"><div><p class="eyebrow">نسخه ۲.۳ · پنل کارکنان</p><h1>داشبورد دانش‌آموزان</h1></div><div class="admin-actions"><a class="button secondary" href="<?=e(url('/admin/referrals.php'))?>">ارجاع‌های مشاوره</a><?php if(user()['role']==='admin'):?><a class="button secondary" href="<?=e(url('/admin/users.php'))?>">مدیریت کاربران</a><a class="button secondary" href="<?=e(url('/admin/years.php'))?>">سال‌های تحصیلی</a><a class="button secondary" href="<?=e(url('/admin/import.php'))?>">ورود گروهی CSV</a><a class="button secondary" href="<?=e(url('/admin/audit.php'))?>">گزارش فعالیت‌ها</a><a class="button secondary" href="<?=e(url('/admin/export.php?format=csv'))?>">خروجی Excel</a><a class="button secondary" href="<?=e(url('/admin/export.php?format=json'))?>">پشتیبان JSON</a><?php endif;?></div></div>
<div class="stats-grid"><div class="stat-card"><strong><?=e($stats['total']??0)?></strong><span>کل دانش‌آموزان</span></div><div class="stat-card"><strong><?=e($stats['complete']??0)?></strong><span>دارای رضایت</span></div><div class="stat-card warning"><strong><?=e($stats['urgent']??0)?></strong><span>پرچم اضطراری</span></div><div class="stat-card accent"><strong><?=e($stats['unread']??0)?></strong><span>پیام خوانده‌نشده</span></div></div>
<?php if($form_error):?><div class="alert error"><?=e($form_error)?></div><?php endif;?>
<?php if(user()['role']==='admin'):?><div class="card create-user"><h2>تعریف حساب مشاور</h2><p class="muted">مشاور فقط اطلاعات حمایتی لازم را می‌بیند و به ساخت حساب یا خروجی کامل دسترسی ندارد.</p><form class="form-grid" method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="create_counselor" value="1"><label>نام مشاور<input name="staff_name" required></label><label>شماره موبایل<input name="staff_mobile" inputmode="tel" required></label><label>رمز اولیه<input type="password" name="staff_password" minlength="8" required></label><div><button class="button">ساخت حساب مشاور</button></div></form></div><div class="card create-user"><h2>تعریف دانش‌آموز جدید</h2><p class="muted">رمز اولیه را از مسیر امن در اختیار دانش‌آموز قرار دهید.</p><form class="form-grid" method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="create_student" value="1"><label>نام و نام خانوادگی<input name="name" required></label><label>شماره موبایل<input name="mobile" inputmode="tel" required></label><label>رمز اولیه<input type="password" name="password" minlength="6" required></label><div><button class="button">ساخت کاربر</button></div></form></div><?php endif;?>
<div class="card filter-card"><h2>جست‌وجو و فیلتر</h2><form class="filter-form" method="get"><input name="q" placeholder="نام، موبایل یا نام والد" value="<?=e($term)?>"><select name="status"><option value="">همه وضعیت‌ها</option><?php foreach($labels as $key=>$label):?><option value="<?=e($key)?>" <?= $status===$key?'selected':'' ?>><?=e($label)?></option><?php endforeach;?></select><select name="grade"><option value="">همه پایه‌ها</option><?php foreach($grades as $g):?><option <?= $grade===$g?'selected':'' ?>><?=e($g)?></option><?php endforeach;?></select><button class="button">اعمال فیلتر</button><a class="button secondary" href="<?=e(url('/admin/index.php'))?>">حذف فیلتر</a></form></div>
<div class="card table-wrap"><table><thead><tr><th>نام</th><th>پایه</th><th>والد</th><th>رضایت</th><th>وضعیت پیگیری</th><th>عملیات</th></tr></thead><tbody><?php while($s=$students->fetch()):?><tr class="<?=!empty($s['emergency_flag'])?'urgent':''?>"><td><strong><?=e($s['name'])?></strong><?php if($s['unread']):?><span class="badge">پیام جدید</span><?php endif;?></td><td><?=e($s['grade']??'—')?></td><td><?=e($s['parent_name']??'—')?></td><td><?=!empty($s['consent'])?'تأیید شده':'تکمیل نشده'?></td><td><form class="inline status-form" method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="student_id" value="<?=e($s['id'])?>"><select name="case_status"><?php foreach($labels as $key=>$label):?><option value="<?=e($key)?>" <?=($s['case_status']??'new')===$key?'selected':''?>><?=e($label)?></option><?php endforeach;?></select><button name="status_update" value="1">ذخیره</button></form></td><td><a href="<?=e(url('/admin/student.php?id='.$s['id']))?>">پرونده / پیام</a></td></tr><?php endwhile;?></tbody></table></div><?php layout_end(); ?>
