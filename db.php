<?php


define('DB_HOST',    'sql308.infinityfree.com');
define('DB_NAME',    'if0_42157238_markets_db');
define('DB_USER',    'if0_42157238');     
define('DB_PASS',    'S3RGy5gibmGquK7');         
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'uploads/');


function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
        }
    }
    return $pdo;
}


function bootSession(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
}

function me(): ?array {
    bootSession();

    if (empty($_SESSION['user_id'])) return null;

    return [
        'id'    => (int)$_SESSION['user_id'],
        'name'  => $_SESSION['name'] ?? '',
        'role'  => $_SESSION['role'] ?? 'buyer',
        'email' => $_SESSION['email'] ?? '',
    ];
}

function mustLogin(string $to = 'login.php'): array {
    $u = me(); if (!$u) { header("Location: $to"); exit; } return $u;
}
function mustSeller(string $to = 'login.php'): array {
    $u = mustLogin($to);
    if ($u['role'] !== 'seller') { header("Location: $to"); exit; }
    return $u;
}
function mustBuyer(string $to = 'login.php'): array {
    $u = mustLogin($to);
    if ($u['role'] !== 'buyer') { header("Location: $to"); exit; }
    return $u;
}


function json_out(bool $ok, string $msg = '', array $data = [], int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $data));
    exit;
}


function xss(string $v): string {
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}


function saveImage(array $file): string|false {
    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    if (!in_array($file['type'], $allowed))  return false;
    if ($file['size'] > 6 * 1024 * 1024)     return false;
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $name = uniqid('p_', true) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) return false;
    return UPLOAD_URL . $name;
}


function makeRef(): string {
    return 'MSA-' . strtoupper(substr(uniqid(), -7));
}


function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)    return 'Just now';
    if ($diff < 3600)  return floor($diff/60)   . 'm ago';
    if ($diff < 86400) return floor($diff/3600)  . 'h ago';
    return date('d M Y', strtotime($datetime));
}