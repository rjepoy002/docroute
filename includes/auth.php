<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';
if (session_status() !== PHP_SESSION_ACTIVE) { session_name('docuroute_session'); session_start(); }
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';
function require_auth(): void { if (empty($_SESSION['user_id'])) redirect('index.php'); }
function require_admin(): void { require_auth(); if (!is_admin()) { flash('error', 'Administrator access is required.'); redirect('dashboard.php'); } }
