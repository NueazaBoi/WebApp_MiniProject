<?php
// ============================================================
//  CarVerse 3D | Database Connection
//  ไฟล์: includes/db.php
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // เปลี่ยนตาม MySQL ของคุณ
define('DB_PASS', '');            // เปลี่ยนตาม MySQL ของคุณ
define('DB_NAME', 'carverse_db');
define('DB_CHAR', 'utf8mb4');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHAR,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}
