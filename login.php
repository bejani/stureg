<?php
require __DIR__.'/includes/functions.php';
if(user()) redirect('/');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_csrf(); $mobile=trim($_POST['mobile']??''); $ip=$_SERVER['REMOTE_ADDR']??'';
 $guard=db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE mobile=? AND ip_address=? AND was_successful=0 AND created_at >= (NOW() - INTERVAL 15 MINUTE)');$guard->execute([$mobile,$ip]);
 if((int)$guard->fetchColumn()>=5){$error='به‌دلیل تلاش‌های ناموفق متعدد، ورود این حساب برای ۱۵ دقیقه موقتاً مسدود است.';}
 else{
  $s=db()->prepare('SELECT * FROM users WHERE mobile=? LIMIT 1');$s->execute([$mobile]);$u=$s->fetch();
  if($u && !$u['is_active']){$error='این حساب غیرفعال شده است؛ با مدیر سامانه تماس بگیرید.';}
  elseif($u && password_verify($_POST['password']??'', $u['password_hash'])){db()->prepare('INSERT INTO login_attempts(mobile,ip_address,was_successful) VALUES(?,?,1)')->execute([$mobile,$ip]);session_regenerate_id(true);$_SESSION['user']=['id'=>$u['id'],'role'=>$u['role'],'name'=>$u['name']];db()->prepare('UPDATE users SET last_login_at=NOW() WHERE id=?')->execute([$u['id']]);audit('login','user',(int)$u['id']);redirect(in_array($u['role'],['admin','counselor'],true)?'/admin/index.php':'/student/index.php');}
  else{$error='شماره موبایل یا رمز عبور صحیح نیست.';db()->prepare('INSERT INTO login_attempts(mobile,ip_address,was_successful) VALUES(?,?,0)')->execute([$mobile,$ip]);}
 }
}
layout_start('ورود'); ?><div class="auth card"><h1>ورود</h1><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><label>شماره موبایل<input name="mobile" inputmode="tel" required autocomplete="username"></label><label>رمز عبور<input type="password" name="password" required autocomplete="current-password"></label><button class="button" type="submit">ورود</button></form><p class="muted">اطلاعات ورود را از مدیر سامانه دریافت کنید.</p></div><?php layout_end(); ?>
