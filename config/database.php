<?php
declare(strict_types=1);
require_once __DIR__ . '/app.php';
$host = getenv('DOCROUTE_DB_HOST') ?: 'localhost'; $user = getenv('DOCROUTE_DB_USER') ?: 'root';
$password = getenv('DOCROUTE_DB_PASSWORD') ?: ''; $database = getenv('DOCROUTE_DB_NAME') ?: 'docrxzp_pal_db';
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_errno) { error_log('DocuRoute database connection failed: ' . $conn->connect_error); http_response_code(503); exit('Unable to connect to the database. Please contact the system administrator.'); }
$conn->set_charset('utf8mb4');
