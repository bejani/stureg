<?php
require __DIR__.'/../includes/functions.php'; require_login('student');
$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_csrf(); $current=(string)($_POST['current_password']??''); $new=(string)($_POST['new_password']??''); $confirm=(string)($_POST['confirm_password']??'');
 $q=db()->prepare('SELECT password_hash FROM users WHERE id=?');$q->execute([user()['id']]);$row=$q->fetch();
 if(!$row || !password_verify($current,$row['password_hash'])) $error='رمز فعلی صحیح نیست.';
 elseif(strlen($new)<8) $error='رمز جدید باید حداقل ۸ کاراکتر باشد.';
 elseif($new!==$confirm) $error='تکرار رمز جدید مطابقت ندارد.';
 else { db()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),user()['id']]); flash('رمز عبور با موفقیت تغییر کرد.'); redirect('/student/index.php'); }
}
layout_start('تغییر رمز عبور'); ?><div class="card narrow"><h1>تغییر رمز عبور</h1><p class="muted">رمز جدید را حداقل ۸ کاراکتری انتخاب کنید.</p><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><label>رمز فعلی<input type="password" name="current_password" required></label><label>رمز جدید<input type="password" name="new_password" minlength="8" required></label><label>تکرار رمز جدید<input type="password" name="confirm_password" minlength="8" required></label><button class="button">ذخیره رمز جدید</button><a class="button secondary" href="<?=e(url('/student/index.php'))?>">انصراف</a></form></div><?php layout_end(); ?>
