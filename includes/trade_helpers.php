<?php
// ============================================================
//  CarVerse 3D | ตัวช่วยระบบซื้อ/ขายรถ
//  includes/trade_helpers.php
// ============================================================
const ORDER_LABELS = ['pending'=>'รอชำระเงิน','paid'=>'ชำระแล้ว','completed'=>'ส่งมอบแล้ว','cancelled'=>'ยกเลิก'];
const PAY_LABELS   = ['transfer'=>'โอนเงิน','cash'=>'เงินสด ณ โชว์รูม','installment'=>'ผ่อนชำระ / ไฟแนนซ์'];
const SELL_LABELS  = [
    'pending'   => 'รอตรวจสอบ',
    'offered'   => 'เสนอราคาแล้ว',
    'accepted'  => 'ผู้ขายตกลง',
    'declined'  => 'ผู้ขายปฏิเสธ',
    'rejected'  => 'ไม่รับซื้อ',
    'purchased' => 'รับซื้อแล้ว',
];

/** ป้าย badge พร้อมสีในตัว (ไม่ต้องแก้ CSS) */
function tradeBadge(string $status, array $labels): string {
    $colors = [
        'pending'   => '#1fc8ff', 'offered'  => '#f39c12', 'paid'      => '#3498db',
        'accepted'  => '#2ecc71', 'completed'=> '#2ecc71', 'purchased' => '#2ecc71',
        'cancelled' => '#e74c3c', 'declined' => '#e74c3c', 'rejected'  => '#e74c3c',
    ];
    $c = $colors[$status] ?? '#aaaaaa';
    return '<span style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:700;white-space:nowrap;'
         . "color:$c;border:1px solid {$c}66;background:{$c}22;\">" . htmlspecialchars($labels[$status] ?? $status) . '</span>';
}

/** ยกเลิกออเดอร์ + คืนสต็อก (เรียกภายใน transaction ของผู้เรียก หรือจะให้ฟังก์ชันเปิดเองก็ได้) */
function cancelOrderAndRestock(PDO $pdo, int $orderId, ?int $onlyUserId = null, ?int $adminId = null): bool {
    $pdo->beginTransaction();
    try {
        $sql = "SELECT * FROM orders WHERE order_id = ?" . ($onlyUserId ? " AND user_id = ?" : "") . " FOR UPDATE";
        $st  = $pdo->prepare($sql);
        $st->execute($onlyUserId ? [$orderId, $onlyUserId] : [$orderId]);
        $o = $st->fetch();
        if (!$o || $o['status'] === 'cancelled' || ($onlyUserId && $o['status'] !== 'pending')) {
            $pdo->rollBack();
            return false;
        }
        $pdo->prepare("UPDATE orders SET status='cancelled', handled_by=? WHERE order_id=?")->execute([$adminId, $orderId]);
        $pdo->prepare("UPDATE cars SET stock_qty = stock_qty + ?, status = IF(status='sold','available',status) WHERE car_id=?")
            ->execute([$o['quantity'], $o['car_id']]);
        $pdo->commit();
        return true;
    } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex;
    }
}
