<?php
// ============================================================
//  CarVerse 3D | ลบข้อมูลรถ (Delete)
//  delete_car.php?id=X  (รับเฉพาะ POST + CSRF)
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
requireLogin();
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
    setFlash('error', 'คำขอไม่ถูกต้อง กรุณาลองใหม่');
    header('Location: edit_car.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT model_name, image_url FROM cars WHERE car_id = ?");
$stmt->execute([$id]);
$car = $stmt->fetch();

if (!$car) {
    setFlash('error', 'ไม่พบข้อมูลรถที่ต้องการลบ');
} else {
    // bookings / favorites / car_images จะถูกลบตาม ON DELETE CASCADE
    $pdo->prepare("DELETE FROM cars WHERE car_id = ?")->execute([$id]);
    deleteUploadedFile($car['image_url']);
    setFlash('success', '🗑 ลบรถ "' . $car['model_name'] . '" เรียบร้อยแล้ว');
}
header('Location: edit_car.php');
exit;
