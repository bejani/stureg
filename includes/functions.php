<?php
require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function url(string $path = ''): string { return rtrim(APP_BASE_PATH, '/').'/'.ltrim($path, '/'); }
function redirect(string $path): never { header('Location: '.url($path)); exit; }
function csrf(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function check_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('درخواست نامعتبر است.'); } }
function flash(?string $message = null): ?string { if ($message !== null) $_SESSION['flash'] = $message; $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $m; }
function user(): ?array { return $_SESSION['user'] ?? null; }
function session_guard(): void { if (user() && !empty($_SESSION['last_activity']) && time() - (int)$_SESSION['last_activity'] > 1800) { $_SESSION=[]; redirect('/login.php'); } if (user()) $_SESSION['last_activity']=time(); }
function require_login(?string $role = null): void { session_guard(); $u = user(); if (!$u || ($role && $u['role'] !== $role)) redirect('/login.php'); }
function require_staff(): void { session_guard(); $u = user(); if (!$u || !in_array($u['role'], ['admin', 'counselor'], true)) redirect('/login.php'); }
function can_view_sensitive(): bool { return user() && user()['role'] === 'admin'; }
function audit(string $action, string $entityType, ?int $entityId = null, ?string $details = null): void {
    $u = user();
    db()->prepare('INSERT INTO activity_logs(actor_id,action,entity_type,entity_id,details,ip_address) VALUES(?,?,?,?,?,?)')
        ->execute([$u['id'] ?? null, $action, $entityType, $entityId, $details, $_SERVER['REMOTE_ADDR'] ?? null]);
}
function notify(int $userId, string $title, string $body, ?string $link = null): void {
    db()->prepare('INSERT INTO notifications(user_id,title,body,link) VALUES(?,?,?,?)')->execute([$userId,$title,$body,$link]);
}
function unread_notifications(): int {
    $u = user(); if (!$u) return 0;
    $q = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0'); $q->execute([$u['id']]); return (int)$q->fetchColumn();
}
function layout_start(string $title): void { $u = user(); ?><!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($title)?> | <?=APP_NAME?></title><link rel="stylesheet" href="<?=e(url('/assets/style.css'))?>"></head><body><header><div class="wrap nav"><a class="brand" href="<?=e(url('/'))?>">StuReg</a><?php if($u): ?><span><?=e($u['name'])?></span><a href="<?=e(url('/notifications.php'))?>">اعلان‌ها<?php if(($n=unread_notifications())>0):?> <span class="badge"><?=$n?></span><?php endif;?></a><a href="<?=e(url('/logout.php'))?>">خروج</a><?php endif; ?></div></header><main class="wrap"><?php if($m=flash()): ?><div class="alert success"><?=e($m)?></div><?php endif; ?><?php }
function layout_end(): void { ?></main><footer><div class="wrap">اطلاعات شما محرمانه است و فقط با رضایت شما برای پشتیبانی و ارتباط آموزشی استفاده می‌شود.</div></footer></body></html><?php }
?>
