<?php
// ============================================================
//  CarVerse 3D | Session & Auth Helper
//  ไฟล์: includes/auth.php
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** ตรวจสอบว่า login อยู่หรือไม่ */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/** ตรวจสอบว่าเป็น Admin หรือไม่ */
function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/** บังคับ login ถ้ายังไม่ได้ login */
function requireLogin(string $redirect = 'login.php'): void {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

/** บังคับ Admin เท่านั้น */
function requireAdmin(string $redirect = 'index.php'): void {
    if (!isAdmin()) {
        header("Location: $redirect");
        exit;
    }
}

/** สร้าง session หลัง login สำเร็จ */
function createSession(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['avatar']    = $user['avatar'] ?? null;
}

/** ล้าง session (logout) */
function destroySession(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

/** Escape output HTML */
function e(string $val): string {
    return htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Format ราคา Thai Baht */
function formatPrice(float $price): string {
    return number_format($price, 0, '.', ',') . ' ฿';
}
