<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/csrf.php'; require_once __DIR__ . '/../config/database.php';
require_csrf(); $username = post_string('username', 25); $password = (string)($_POST['password'] ?? '');
if ($username === '' || $password === '') { flash('error', 'Enter your username and password.'); redirect('index.php'); }
$stmt = $conn->prepare('SELECT * FROM dr_users WHERE username = ? LIMIT 1'); $stmt->bind_param('s', $username); $stmt->execute(); $user = fetch_one($stmt);
$valid = $user && (password_verify($password, $user['password']) || (!str_starts_with((string)$user['password'], '$') && hash_equals((string)$user['password'], $password)));
if (!$valid) { flash('error', 'Invalid username or password.'); redirect('index.php'); }
if (!str_starts_with((string)$user['password'], '$')) { $hash = password_hash($password, PASSWORD_DEFAULT); $update = $conn->prepare('UPDATE dr_users SET password = ? WHERE id = ?'); $update->bind_param('si', $hash, $user['id']); $update->execute(); }
session_regenerate_id(true); $_SESSION['user_id'] = (int)$user['id']; $_SESSION['username'] = $user['username']; $_SESSION['user_type'] = $user['type'] ?? ''; $_SESSION['user_name'] = user_name($user); $_SESSION['designation'] = $user['designation'] ?? ''; flash('success', 'Welcome back.'); redirect('dashboard.php');
