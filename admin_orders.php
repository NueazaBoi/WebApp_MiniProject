<?php
// ============================================================
//  CarVerse 3D | แอดมิน: จัดการคำสั่งซื้อ
//  admin_orders.php
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/trade_helpers.php';
requireLogin('login.php?redirect=admin_orders.php');
requireAdmin();

/* ── เปลี่ยนสถานะ ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'set_status') {
    $oid = (int)($_POST['order_id'] ?? 0);
    $new = $_POST['status'] ?? '';
    if (verifyCsrf() && $oid && isset(ORDER_LABELS[$new])) {
        $cur = $pdo->prepare("SELECT status FROM orders WHERE order_id=?");
        $cur->execute([$oid]);
        $old = $cur->fetchColumn();

        if ($old === false) {
            setFlash('error', 'ไม่พบคำสั่งซื้อ');
        } elseif ($old === 'cancelled') {
            setFlash('error', 'คำสั่งซื้อที่ยกเลิกแล้วไม่สามารถเปลี่ยนสถานะได้');
        } elseif ($new === 'cancelled') {
            cancelOrderAndRestock($pdo, $oid, null, (int)$_SESSION['user_id']);
            setFlash('success', '↩️ ยกเลิกคำสั่งซื้อ #' . $oid . ' และคืนสต็อกแล้ว');
        } else {
            $pdo->prepare("UPDATE orders SET status=?, handled_by=? WHERE order_id=?")->execute([$new, $_SESSION['user_id'], $oid]);
            setFlash('success', '✅ อัปเดตคำสั่งซื้อ #' . $oid . ' เป็น "' . ORDER_LABELS[$new] . '" แล้ว');
        }
    } else {
        setFlash('error', 'คำขอไม่ถูกต้อง');
    }
    header('Location: admin_orders.php?' . http_build_query(array_diff_key($_GET, ['page' => 1])));
    exit;
}

/* ── ตัวกรอง ── */
$q       = trim($_GET['q'] ?? '');
$statusF = $_GET['status'] ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$where = ['1=1']; $params = [];
if ($q !== '') {
    $where[] = '(u.full_name LIKE ? OR c.model_name LIKE ? OR o.order_id = ?)';
    array_push($params, "%$q%", "%$q%", (int)$q);
}
if (isset(ORDER_LABELS[$statusF])) { $where[] = 'o.status = ?'; $params[] = $statusF; }
$w = 'WHERE ' . implode(' AND ', $where);

$base = "FROM orders o JOIN users u ON o.user_id=u.user_id JOIN cars c ON o.car_id=c.car_id JOIN brands b ON c.brand_id=b.brand_id";

$cnt = $pdo->prepare("SELECT COUNT(*) $base $w"); $cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT o.*, u.full_name, c.model_name, b.brand_name $base $w ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$list = $stmt->fetchAll();

// สรุปยอด
$sum = $pdo->query("
    SELECT COUNT(*) n,
           COALESCE(SUM(status='pending'),0)   pending,
           COALESCE(SUM(status='paid'),0)      paid,
           COALESCE(SUM(status='completed'),0) completed,
           COALESCE(SUM(CASE WHEN status IN ('paid','completed') THEN total_price END),0) revenue
    FROM orders
")->fetch();

$pageTitle = 'จัดการคำสั่งซื้อ';
$pageDesc  = "คำสั่งซื้อทั้งหมด $total รายการ — ยืนยันการชำระเงินและส่งมอบรถ";
$activeNav = 'orders';
require 'includes/admin_header.php';
?>

<div class="stats-grid">
  <div class="stat-card"><div class="stat-label">ออเดอร์ทั้งหมด</div><div class="stat-value"><?= (int)$sum['n'] ?></div><div class="stat-sub">รายการ</div></div>
  <div class="stat-card"><div class="stat-label">รอชำระเงิน</div><div class="stat-value"><?= (int)$sum['pending'] ?></div><div class="stat-sub">รายการ</div></div>
  <div class="stat-card"><div class="stat-label">ชำระแล้ว</div><div class="stat-value"><?= (int)$sum['paid'] ?></div><div class="stat-sub">รายการ</div></div>
  <div class="stat-card"><div class="stat-label">ส่งมอบแล้ว</div><div class="stat-value"><?= (int)$sum['completed'] ?></div><div class="stat-sub">รายการ</div></div>
  <div class="stat-card"><div class="stat-label">รายได้ (ชำระแล้ว+ส่งมอบ)</div><div class="stat-value" style="font-size:1.4rem;"><?= number_format((float)$sum['revenue'] / 1000000, 2) ?> ล้าน</div><div class="stat-sub">บาท</div></div>
</div>

<div class="toolbar">
  <form method="GET" class="toolbar-form">
    <input type="text" name="q" class="form-control" placeholder="🔍 ชื่อลูกค้า / รุ่นรถ / เลขออเดอร์" value="<?= e($q) ?>">
    <select name="status" class="form-control" onchange="this.form.submit()">
      <option value="">ทุกสถานะ</option>
      <?php foreach (ORDER_LABELS as $k => $l): ?><option value="<?= $k ?>" <?= $statusF === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
    </select>
    <button class="btn btn-ghost" type="submit">ค้นหา</button>
    <a href="admin_orders.php" class="btn btn-ghost">ล้าง</a>
  </form>
</div>

<div class="panel table-panel">
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>#</th><th>ลูกค้า</th><th>รถ</th><th>จำนวน</th><th>ยอดรวม</th><th>ชำระเงิน</th><th>วันที่สั่ง</th><th>สถานะ</th><th>เปลี่ยนสถานะ</th></tr></thead>
      <tbody>
      <?php if (!$list): ?><tr><td colspan="9" class="empty-row">ไม่พบคำสั่งซื้อ</td></tr><?php endif; ?>
      <?php foreach ($list as $o): ?>
        <tr>
          <td class="text-muted"><?= (int)$o['order_id'] ?></td>
          <td><strong><?= e($o['full_name']) ?></strong><br><span class="text-muted" style="font-size:.8rem;"><?= e($o['contact_phone']) ?></span></td>
          <td><?= e($o['brand_name'] . ' ' . $o['model_name']) ?></td>
          <td><?= (int)$o['quantity'] ?></td>
          <td class="text-accent" style="font-weight:600;white-space:nowrap;"><?= formatPrice((float)$o['total_price']) ?></td>
          <td><?= e(PAY_LABELS[$o['payment_method']] ?? $o['payment_method']) ?></td>
          <td style="white-space:nowrap;"><?= date('d/m/', strtotime($o['created_at'])) . (date('Y', strtotime($o['created_at'])) + 543) ?><br><span class="text-muted"><?= date('H:i', strtotime($o['created_at'])) ?> น.</span></td>
          <td><?= tradeBadge($o['status'], ORDER_LABELS) ?></td>
          <td>
            <?php if ($o['status'] === 'cancelled'): ?>
              <span class="text-muted">—</span>
            <?php else: ?>
            <form method="POST" class="row-actions" onsubmit="return this.status.value!=='cancelled' || confirm('ยกเลิกออเดอร์และคืนสต็อก?')">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="set_status">
              <input type="hidden" name="order_id" value="<?= (int)$o['order_id'] ?>">
              <select name="status" class="form-control" style="padding:6px 10px;width:auto;">
                <?php foreach (ORDER_LABELS as $k => $l): ?><option value="<?= $k ?>" <?= $o['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
              </select>
              <button class="btn btn-primary btn-sm" type="submit">บันทึก</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination">
  <?php if ($page > 1): ?><a class="page-btn" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">←</a><?php endif; ?>
  <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
    <a class="page-btn <?= $p === $page ? 'active' : '' ?>" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
  <?php endfor; ?>
  <?php if ($page < $totalPages): ?><a class="page-btn" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">→</a><?php endif; ?>
</div>
<?php endif; ?>

<?php require 'includes/admin_footer.php'; ?>
