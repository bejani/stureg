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
function redirect(string $url): never { header('Location: '.$url); exit; }
function csrf(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function check_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('درخواست نامعتبر است.'); } }
function flash(?string $message = null): ?string { if ($message !== null) $_SESSION['flash'] = $message; $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $m; }
function user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(?string $role = null): void { $u = user(); if (!$u || ($role && $u['role'] !== $role)) redirect('/login.php'); }
function layout_start(string $title): void { $u = user(); ?><!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($title)?> | <?=APP_NAME?></title><link rel="stylesheet" href="/assets/style.css"></head><body><header><div class="wrap nav"><a class="brand" href="/">StuReg</a><?php if($u): ?><span><?=e($u['name'])?></span><a href="/logout.php">خروج</a><?php endif; ?></div></header><main class="wrap"><?php if($m=flash()): ?><div class="alert success"><?=e($m)?></div><?php endif; ?><?php }
function layout_end(): void { ?></main><footer><div class="wrap">اطلاعات شما محرمانه است و فقط با رضایت شما برای پشتیبانی و ارتباط آموزشی استفاده می‌شود.</div></footer></body></html><?php }
?>
