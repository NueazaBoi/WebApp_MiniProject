<?php
// ============================================================
//  CarVerse 3D | ประวัติการจองทดสอบขับ (User)
//  my_bookings.php
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
requireLogin('login.php?redirect=my_bookings.php');

$uid     = $_SESSION['user_id'];
$success = '';
$statusF = $_GET['status'] ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

// ── ยกเลิกการจอง ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    $bid = (int)($_POST['booking_id'] ?? 0);
    $chk = $pdo->prepare("SELECT booking_id FROM test_drive_bookings WHERE booking_id=? AND user_id=? AND status='pending'");
    $chk->execute([$bid, $uid]);
    if ($chk->fetch()) {
        $pdo->prepare("UPDATE test_drive_bookings SET status='cancelled' WHERE booking_id=?")->execute([$bid]);
        $success = 'ยกเลิกการจองเรียบร้อยแล้ว';
    }
}

// ── Query ──
$where  = ['b.user_id = ?'];
$params = [$uid];
if ($statusF && isset(BOOK_LABELS[$statusF])) {
    $where[] = 'b.status = ?';
    $params[] = $statusF;
}
$w = 'WHERE ' . implode(' AND ', $where);

$cntStmt = $pdo->prepare("SELECT COUNT(*) FROM test_drive_bookings b $w");
$cntStmt->execute($params);
$total      = $cntStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));
$offset     = ($page - 1) * $perPage;

$stmt = $pdo->prepare("
    SELECT b.*, c.model_name, c.image_url, c.car_id, brd.brand_name, c.price
    FROM   test_drive_bookings b
    JOIN   cars c    ON b.car_id   = c.car_id
    JOIN   brands brd ON c.brand_id = brd.brand_id
    $w
    ORDER  BY b.created_at DESC
    LIMIT  $perPage OFFSET $offset
");
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// ── สถิติ ──
$stats = $pdo->prepare("
    SELECT
        COUNT(*)                                    AS total,
        SUM(status='pending')                       AS pending,
        SUM(status='confirmed')                     AS confirmed,
        SUM(status='completed')                     AS completed,
        SUM(status='cancelled')                     AS cancelled
    FROM test_drive_bookings WHERE user_id=?
");
$stats->execute([$uid]);
$stats = $stats->fetch();

$bookColors = [
    'pending'   => ['rgba(31,200,255,0.15)', '#1fc8ff', 'rgba(31,200,255,0.4)'],
    'confirmed' => ['rgba(39,174,96,0.15)',  '#2ecc71', 'rgba(39,174,96,0.4)'],
    'completed' => ['rgba(39,174,96,0.15)',  '#2ecc71', 'rgba(39,174,96,0.4)'],
    'cancelled' => ['rgba(231,76,60,0.15)',  '#e74c3c', 'rgba(231,76,60,0.4)'],
];

// ตั้งค่า Header ส่วนกลาง
$pageTitle   = 'การจองของฉัน';
$pageHeading = 'MY <span class="text-accent">BOOKINGS</span>';
$pageDesc    = 'ประวัติการจองทดสอบขับทั้งหมดของคุณ — ' . $_SESSION['full_name'];
$activeNav   = 'booking';
require 'includes/user_header.php';
echo '<link rel="stylesheet" href="assets/css/particles.css"><script>!function(d){if(matchMedia("(prefers-reduced-motion: reduce)").matches)return;d.classList.add("pfx-on");setTimeout(function(){window.__pfx||d.classList.remove("pfx-on")},3500)}(document.documentElement)</script><script src="assets/js/particles.js" defer></script>';
?>

<style>
    .stat-strip{display:flex;gap:16px;flex-wrap:wrap;margin-bottom:32px}
    .stat-chip{flex:1;min-width:130px;background:linear-gradient(145deg,rgba(31,200,255,.06),rgba(11,134,214,.02));border:1px solid rgba(31,200,255,.15);border-radius:16px;padding:20px 18px;text-align:center;cursor:pointer;transition:all 200ms;text-decoration:none;display:block}
    .stat-chip:hover,.stat-chip.active{border-color:rgba(31,200,255,.5);box-shadow:0 0 20px rgba(31,200,255,.15)}
    .stat-chip.active{background:rgba(31,200,255,.1)}
    .stat-chip-num{font-size:1.8rem;font-weight:800;color:#1fc8ff;font-family:'Barlow Condensed',sans-serif;line-height:1}
    .stat-chip-lbl{font-size:.75rem;color:#8aa3b2;margin-top:5px}

    .booking-card{display:grid;grid-template-columns:100px 1fr auto;gap:20px;align-items:center;padding:20px 24px;border:1px solid rgba(31,200,255,.12);border-radius:18px;margin-bottom:14px;background:linear-gradient(145deg,rgba(31,200,255,.04),rgba(11,134,214,.01));transition:all 200ms}
    .booking-card:hover{border-color:rgba(31,200,255,.35);box-shadow:0 4px 24px rgba(0,0,0,.3),0 0 20px rgba(31,200,255,.08)}
    .booking-thumb{width:100px;height:70px;border-radius:10px;overflow:hidden;background:rgba(31,200,255,.06);border:1px solid rgba(31,200,255,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .booking-thumb img{width:100%;height:100%;object-fit:cover}
    .booking-thumb-icon{font-size:1.8rem;opacity:.4}
    .booking-info{min-width:0}
    .booking-brand{font-size:.72rem;color:#1fc8ff;font-weight:700;text-transform:uppercase;letter-spacing:.1em}
    .booking-name{font-size:1.05rem;font-weight:700;color:#e9f7ff;margin:3px 0 6px}
    .booking-meta{display:flex;gap:16px;flex-wrap:wrap}
    .booking-meta-item{font-size:.8rem;color:#8aa3b2;display:flex;align-items:center;gap:4px}
    .booking-actions{display:flex;flex-direction:column;align-items:flex-end;gap:10px;flex-shrink:0}
    .booking-status-badge{padding:5px 14px;border-radius:20px;font-size:.75rem;font-weight:700;white-space:nowrap}
    .btn-cancel{padding:6px 16px;font-size:.78rem;border-radius:20px;border:1px solid rgba(231,76,60,.45);background:rgba(231,76,60,.08);color:#e74c3c;cursor:pointer;transition:all 200ms;font-weight:600}
    .btn-cancel:hover{background:rgba(231,76,60,.18);border-color:#e74c3c}
    .empty-state{text-align:center;padding:70px 20px;color:#8aa3b2}
    .empty-icon{font-size:4rem;margin-bottom:16px}
    .filter-pills{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px}
    .filter-pill{padding:7px 18px;border-radius:20px;font-size:.82rem;font-weight:600;border:1px solid rgba(31,200,255,.2);background:rgba(31,200,255,.04);color:#8aa3b2;text-decoration:none;transition:all 150ms}
    .filter-pill:hover{color:#e9f7ff;border-color:rgba(31,200,255,.5)}
    .filter-pill.active{background:rgba(31,200,255,.14);border-color:#1fc8ff;color:#1fc8ff}
    @media(max-width:600px){.booking-card{grid-template-columns:1fr;}.booking-thumb{width:100%;height:160px}.booking-actions{align-items:flex-start}}
</style>

  <?php if ($success): ?>
  <div class="alert alert-success" data-autohide="5000" style="margin-bottom:20px;">✅ <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <!-- ── Stats ── -->
  <div class="stat-strip">
    <a href="my_bookings.php" class="stat-chip <?= !$statusF ? 'active' : '' ?>">
      <div class="stat-chip-num"><?= (int)$stats['total'] ?></div>
      <div class="stat-chip-lbl">ทั้งหมด</div>
    </a>
    <a href="?status=pending" class="stat-chip <?= $statusF==='pending' ? 'active' : '' ?>" style="<?= $statusF==='pending'?'border-color:rgba(31,200,255,.5)':'' ?>">
      <div class="stat-chip-num" style="color:#1fc8ff;"><?= (int)$stats['pending'] ?></div>
      <div class="stat-chip-lbl">รอยืนยัน</div>
    </a>
    <a href="?status=confirmed" class="stat-chip <?= $statusF==='confirmed' ? 'active' : '' ?>">
      <div class="stat-chip-num" style="color:#2ecc71;"><?= (int)$stats['confirmed'] ?></div>
      <div class="stat-chip-lbl">ยืนยันแล้ว</div>
    </a>
    <a href="?status=completed" class="stat-chip <?= $statusF==='completed' ? 'active' : '' ?>">
      <div class="stat-chip-num" style="color:#2ecc71;"><?= (int)$stats['completed'] ?></div>
      <div class="stat-chip-lbl">เสร็จสิ้น</div>
    </a>
    <a href="?status=cancelled" class="stat-chip <?= $statusF==='cancelled' ? 'active' : '' ?>">
      <div class="stat-chip-num" style="color:#e74c3c;"><?= (int)$stats['cancelled'] ?></div>
      <div class="stat-chip-lbl">ยกเลิกแล้ว</div>
    </a>
  </div>

  <!-- ── Result Meta ── -->
  <div class="result-meta" style="margin-bottom:20px;">
    <strong><?= $total ?></strong> รายการ<?= $statusF ? ' · '.BOOK_LABELS[$statusF] : '' ?>
  </div>

  <!-- ── Booking Cards ── -->
  <?php if (empty($bookings)): ?>
  <div class="empty-state">
    <div class="empty-icon">🗓</div>
    <h3 style="color:#e9f7ff;font-family:'Barlow Condensed';font-size:1.8rem;margin-bottom:8px;">ยังไม่มีการจอง</h3>
    <p><?= $statusF ? 'ไม่มีการจองที่มีสถานะ "'.BOOK_LABELS[$statusF].'"' : 'คุณยังไม่เคยจองทดสอบขับรถ' ?></p>
    <a href="search.php" class="btn btn-primary" style="margin-top:20px;display:inline-flex;text-decoration:none;">🔍 ค้นหารถและจองทดสอบขับ</a>
  </div>
  <?php else: ?>

    <?php foreach ($bookings as $b):
      $bc = $bookColors[$b['status']] ?? ['rgba(100,100,100,.15)','#aaa','rgba(100,100,100,.3)'];
    ?>
    <div class="booking-card">

      <!-- Thumbnail -->
      <div class="booking-thumb">
        <?php if ($b['image_url']): ?>
          <img src="<?= htmlspecialchars($b['image_url']) ?>" alt="<?= htmlspecialchars($b['model_name']) ?>">
        <?php else: ?>
          <div class="booking-thumb-icon">🚗</div>
        <?php endif; ?>
      </div>

      <!-- Info -->
      <div class="booking-info">
        <div class="booking-brand"><?= htmlspecialchars($b['brand_name']) ?></div>
        <div class="booking-name"><?= htmlspecialchars($b['model_name']) ?></div>
        <div class="booking-meta">
          <span class="booking-meta-item">📅 <?= date('d/m/Y', strtotime($b['booking_date'])) ?></span>
          <span class="booking-meta-item">⏰ <?= substr($b['booking_time'],0,5) ?> น.</span>
          <span class="booking-meta-item">💰 <?= formatPrice((float)$b['price']) ?></span>
          <span class="booking-meta-item">📋 #<?= $b['booking_id'] ?></span>
        </div>
        <?php if ($b['notes']): ?>
        <div style="margin-top:6px;font-size:.78rem;color:#8aa3b2;">📝 <?= htmlspecialchars($b['notes']) ?></div>
        <?php endif; ?>
      </div>

      <!-- Actions -->
      <div class="booking-actions">
        <span class="booking-status-badge" style="background:<?= $bc[0] ?>;color:<?= $bc[1] ?>;border:1px solid <?= $bc[2] ?>;">
          <?= BOOK_LABELS[$b['status']] ?? $b['status'] ?>
        </span>
        <a href="car_detail.php?id=<?= $b['car_id'] ?>" class="btn btn-outline btn-sm" style="text-decoration:none;font-size:.78rem;padding:6px 14px;">ดูรถ</a>
        <?php if ($b['status'] === 'pending'): ?>
        <form method="POST" onsubmit="return confirm('ยืนยันการยกเลิกการจอง #<?= $b['booking_id'] ?>?')">
          <input type="hidden" name="action" value="cancel">
          <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
          <button type="submit" class="btn-cancel">✕ ยกเลิก</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- ── Pagination ── -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
      <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>" class="page-btn">←</a>
      <?php endif; ?>
      <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
      <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="page-btn <?= $p===$page?'active':'' ?>"><?= $p ?></a>
      <?php endfor; ?>
      <?php if ($page < $totalPages): ?>
      <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>" class="page-btn">→</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  <?php endif; ?>

<?php require 'includes/user_footer.php'; ?>