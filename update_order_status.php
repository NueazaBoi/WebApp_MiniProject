<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// ตรวจสอบสิทธิ์ต้องเป็นแอดมิน
if (!isAdmin()) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status']; // เช่น 'paid', 'delivered', 'cancelled'

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    if ($stmt->execute([$new_status, $order_id])) {
        header("Location: admin_orders.php?success=1");
        exit();
    } else {
        $error = "อัปเดตสถานะไม่สำเร็จ";
    }
}
?>