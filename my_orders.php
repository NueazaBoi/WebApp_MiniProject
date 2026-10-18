<?php
// ============================================================
//  CarVerse 3D | คำสั่งซื้อของฉัน (User)
//  my_orders.php
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/trade_helpers.php';
requireLogin('login.php?redirect=my_orders.php');

$uid = (int)$_SESSION['user_id'];
$msg = ''; $err = '';

if (isset($_GET['ordered'])) $msg = 'สั่งซื้อสำเร็จ! เลขที่คำสั่งซื้อ #' . (int)$_GET['ordered'] . ' — ทีมงานจะติดต่อกลับเพื่อยืนยันการชำระเงิน';

// ยกเลิกออเดอร์ (เฉพาะที่ยังรอชำระเงิน)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    if (!verifyCsrf()) {
        $err = 'Session หมดอายุ กรุณาลองใหม่';
    } elseif (cancelOrderAndRestock($pdo, (int)($_POST['order_id'] ?? 0), $uid)) {
        $msg = 'ยกเลิกคำสั่งซื้อเรียบร้อยแล้ว';
    } else {
        $err = 'ไม่สามารถยกเลิกคำสั่งซื้อนี้ได้';
    }
}

$statusF = $_GET['status'] ?? '';
$where = ['o.user_id = ?']; $params = [$uid];
if (isset(ORDER_LABELS[$statusF])) { $where[] = 'o.status = ?'; $params[] = $statusF; }
$w = 'WHERE ' . implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT o.*, c.model_name, c.image_url, c.year, b.brand_name
    FROM orders o
    JOIN cars c   ON o.car_id = c.car_id
    JOIN brands b ON c.brand_id = b.brand_id
    $w
    ORDER BY o.created_at DESC
    LIMIT 100
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

$sumStmt = $pdo->prepare("SELECT COALESCE(SUM(total_price),0) FROM orders WHERE user_id=? AND status IN ('paid','completed')");
$sumStmt->execute([$uid]);
$spent = (float)$sumStmt->fetchColumn();

$pageTitle   = 'คำสั่งซื้อของฉัน';
$pageHeading = 'MY ORDERS';
$pageDesc    = 'ประวัติการสั่งซื้อรถทั้งหมดของคุณ — ' . ($_SESSION['full_name'] ?? '');
$activeNav   = 'orders';
require 'includes/user_header.php';
echo '<link rel="stylesheet" href="assets/css/particles.css"><script>!function(d){if(matchMedia("(prefers-reduced-motion: reduce)").matches)return;d.classList.add("pfx-on");setTimeout(function(){window.__pfx||d.classList.remove("pfx-on")},3500)}(document.documentElement)</script><script src="assets/js/particles.js" defer></script>';
?>

<?php if ($msg): ?><div class="alert alert-success" data-autohide="6000" style="margin-bottom:20px;">✅ <?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger" style="margin-bottom:20px;"><?= e($err) ?></div><?php endif; ?>

<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:22px;align-items:center;">
  <a href="my_orders.php" class="btn btn-sm <?= !$statusF ? 'btn-primary' : 'btn-ghost' ?>">ทั้งหมด</a>
  <?php foreach (ORDER_LABELS as $k => $l): ?>
    <a href="?status=<?= $k ?>" class="btn btn-sm <?= $statusF === $k ? 'btn-primary' : 'btn-ghost' ?>"><?= e($l) ?></a>
  <?php endforeach; ?>
  <span style="margin-left:auto;color:#8aa3b2;font-size:.85rem;">ยอดซื้อสำเร็จรวม: <strong style="color:#1fc8ff"><?= formatPrice($spent) ?></strong></span>
</div>

<?php if (!$orders): ?>
  <div class="t-empty"><div style="font-size:4rem">🛒</div><h3 style="color:#e9f7ff;font-family:'Barlow Condensed';font-size:1.8rem;">ยังไม่มีคำสั่งซื้อ</h3>
    <a href="search.php" class="btn btn-primary" style="margin-top:16px;display:inline-flex">🔍 เลือกซื้อรถ</a></div>
<?php endif; ?>

<?php foreach ($orders as $o): ?>
  <div class="t-item">
    <div class="t-thumb"><?php if ($o['image_url']): ?><img src="<?= e($o['image_url']) ?>" alt=""><?php else: ?>🚗<?php endif; ?></div>
    <div>
      <div style="font-size:.72rem;color:#1fc8ff;font-weight:700;text-transform:uppercase;letter-spacing:.1em"><?= e($o['brand_name']) ?></div>
      <div style="font-size:1.05rem;font-weight:700;color:#e9f7ff;"><?= e($o['model_name']) ?> (<?= e((string)$o['year']) ?>)</div>
      <div class="t-meta">
        <span>📋 #<?= (int)$o['order_id'] ?></span>
        <span>📅 <?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></span>
        <span>🔢 <?= (int)$o['quantity'] ?> คัน</span>
        <span>💳 <?= e(PAY_LABELS[$o['payment_method']] ?? $o['payment_method']) ?></span>
      </div>
      <div style="margin-top:6px;color:#1fc8ff;font-weight:700;font-size:1.05rem;"><?= formatPrice((float)$o['total_price']) ?></div>
      <?php if ($o['notes']): ?><div class="t-meta">📝 <?= e($o['notes']) ?></div><?php endif; ?>
    </div>
    <div class="t-side">
      <?= tradeBadge($o['status'], ORDER_LABELS) ?>
      <a href="car_detail.php?id=<?= (int)$o['car_id'] ?>" class="btn btn-outline btn-sm" style="font-size:.78rem;padding:6px 14px;">ดูรถ</a>
      <?php if ($o['status'] === 'pending'): ?>
        <form method="POST" onsubmit="return confirm('ยืนยันการยกเลิกคำสั่งซื้อ #<?= (int)$o['order_id'] ?>?')">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="cancel">
          <input type="hidden" name="order_id" value="<?= (int)$o['order_id'] ?>">
          <button type="submit" class="t-btn-danger">✕ ยกเลิก</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>

<?php require 'includes/user_footer.php'; ?>