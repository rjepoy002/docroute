<?php
declare(strict_types=1);
function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); }
function url(string $path = ''): string { return BASE_URL . ($path === '' ? '' : '/' . ltrim($path, '/')); }
function redirect(string $path): never { header('Location: ' . url($path)); exit; }
function post_string(string $key, int $max = 500): string { return substr(trim((string)($_POST[$key] ?? '')), 0, $max); }
function is_admin(): bool { return (($_SESSION['user_type'] ?? '') === 'admin'); }
function user_name(array $user): string { return trim(($user['lname'] ?? '') . ', ' . ($user['fname'] ?? '') . ' ' . ($user['mname'] ?? '')); }
function fetch_one(mysqli_stmt $statement): ?array { $result = $statement->get_result(); return $result->fetch_assoc() ?: null; }
function current_user(mysqli $db): ?array { $id = (int)($_SESSION['user_id'] ?? 0); if ($id < 1) return null; $stmt = $db->prepare('SELECT * FROM dr_users WHERE id = ? LIMIT 1'); $stmt->bind_param('i', $id); $stmt->execute(); return fetch_one($stmt); }
function current_route(mysqli $db, string $tracking): ?array { $stmt = $db->prepare('SELECT * FROM dr_logs WHERE id_track = ? ORDER BY id DESC LIMIT 1'); $stmt->bind_param('s', $tracking); $stmt->execute(); return fetch_one($stmt); }
function document_by_tracking(mysqli $db, string $tracking): ?array { $stmt = $db->prepare('SELECT d.*, u.fname, u.mname, u.lname, u.designation FROM dr_documents d LEFT JOIN dr_users u ON u.id = d.author WHERE d.id_track = ? LIMIT 1'); $stmt->bind_param('s', $tracking); $stmt->execute(); return fetch_one($stmt); }
function flash(string $type, string $message): void { $_SESSION['flash'][] = ['type' => $type, 'message' => $message]; }
function status_class(string $status): string { return match (strtolower($status)) { 'pending' => 'badge-pending', 'received' => 'badge-success', 'declined' => 'badge-danger', 'closed' => 'badge-muted', default => 'badge-info' }; }
