<?php
// Copy this file to config.local.php and change credentials for your local MySQL.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'stureg';
const DB_USER = 'root';
const DB_PASS = '';
const APP_NAME = 'StuReg';
// When installed at http://localhost/stureg use '/stureg'; use '' at domain root.
const APP_BASE_PATH = '/stureg';
const SESSION_NAME = 'stureg_session';

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

date_default_timezone_set('Asia/Tehran');
?>
