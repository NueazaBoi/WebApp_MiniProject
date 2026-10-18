<?php
// ============================================================
//  CarVerse 3D | รายงานที่ 2: การจองทดลองขับ
//  report2.php  (รวมการยืนยัน/ยกเลิกการจองโดยแอดมิน)
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
requireLogin('login.php?redirect=report2.php');
requireAdmin();

/* ── อัปเดตสถานะการจอง ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'set_status') {
    $bid = (int)($_POST['booking_id'] ?? 0);
    $new = $_POST['status'] ?? '';
    if (verifyCsrf() && $bid && isset(BOOK_LABELS[$new])) {
        $pdo->prepare("UPDATE test_drive_bookings SET status = ?, confirmed_by = ? WHERE booking_id = ?")
            ->execute([$new, $_SESSION['user_id'], $bid]);
        setFlash('success', '✅ อัปเดตสถานะการจอง #' . $bid . ' เป็น "' . BOOK_LABELS[$new] . '" แล้ว');
    } else {
        setFlash('error', 'คำขอไม่ถูกต้อง');
    }
    header('Location: report2.php?' . http_build_query(array_diff_key($_GET, ['page' => 1])));
    exit;
}

/* ── ตัวกรอง ── */
$from    = $_GET['from'] ?? '';
$to      = $_GET['to']   ?? '';
$statusF = $_GET['status'] ?? '';
$validDate = fn($d) => (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
$where = ['1=1']; $params = [];
if ($validDate($from)) { $where[] = 'bk.booking_date >= ?'; $params[] = $from; } else $from = '';
if ($validDate($to))   { $where[] = 'bk.booking_date <= ?'; $params[] = $to;   } else $to   = '';
if (isset(BOOK_LABELS[$statusF])) { $where[] = 'bk.status = ?'; $params[] = $statusF; }
$w = 'WHERE ' . implode(' AND ', $where);

$run = function (string $sql, array $p = null) use ($pdo, $params) {
    $s = $pdo->prepare($sql); $s->execute($p ?? $params); return $s->fetchAll();
};

// สรุปตามสถานะ
$stRows = $run("SELECT bk.status, COUNT(*) AS n FROM test_drive_bookings bk $w GROUP BY bk.status");
$st = array_fill_keys(array_keys(BOOK_LABELS), 0);
foreach ($stRows as $r) $st[$r['status']] = (int)$r['n'];
$totalBook = array_sum($st);

// รายเดือน (12 เดือนล่าสุดตามตัวกรอง)
$byMonth = array_reverse($run("
    SELECT DATE_FORMAT(bk.booking_date, '%Y-%m') AS ym, COUNT(*) AS n
    FROM test_drive_bookings bk $w
    GROUP BY ym ORDER BY ym DESC LIMIT 12
"));

// รถที่ถูกจองมากสุด
$topCars = $run("
    SELECT b.brand_name, c.model_name, COUNT(*) AS n
    FROM test_drive_bookings bk
    JOIN cars c   ON bk.car_id = c.car_id
    JOIN brands b ON c.brand_id = b.brand_id
    $w
    GROUP BY c.car_id, b.brand_name, c.model_name
    ORDER BY n DESC, c.model_name LIMIT 5
");

// ตารางรายการ + แบ่งหน้า
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$totalPages = max(1, (int)ceil($totalBook / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$list = $run("
    SELECT bk.*, u.full_name, u.phone, c.model_name, b.brand_name
    FROM test_drive_bookings bk
    JOIN users  u ON bk.user_id = u.user_id
    JOIN cars   c ON bk.car_id  = c.car_id
    JOIN brands b ON c.brand_id = b.brand_id
    $w
    ORDER BY bk.booking_date DESC, bk.booking_time DESC
    LIMIT $perPage OFFSET $offset
");

$maxMonth = max(1, ...array_map(fn($r) => (int)$r['n'], $byMonth ?: [['n' => 1]]));
$maxTop   = max(1, ...array_map(fn($r) => (int)$r['n'], $topCars ?: [['n' => 1]]));
$thMonths = ['01'=>'ม.ค.','02'=>'ก.พ.','03'=>'มี.ค.','04'=>'เม.ย.','05'=>'พ.ค.','06'=>'มิ.ย.','07'=>'ก.ค.','08'=>'ส.ค.','09'=>'ก.ย.','10'=>'ต.ค.','11'=>'พ.ย.','12'=>'ธ.ค.'];

$pageTitle = 'รายงานการจองทดลองขับ';
$pageDesc  = 'สถิติการจอง รถยอดนิยม และจัดการสถานะการจองของลูกค้า';
$activeNav = 'report2';
require 'includes/admin_header.php';
echo '<link rel="stylesheet" href="assets/css/particles.css"><script>!function(d){if(matchMedia("(prefers-reduced-motion: reduce)").matches)return;d.classList.add("pfx-on");setTimeout(function(){window.__pfx||d.classList.remove("pfx-on")},3500)}(document.documentElement)</script><script src="assets/js/particles.js" defer></script>';
?>

<div class="toolbar no-print">
  <form method="GET" class="toolbar-form">
    <label class="inline-label">ตั้งแต่ <input type="date" name="from" class="form-control" value="<?= e($from) ?>"></label>
    <label class="inline-label">ถึง <input type="date" name="to" class="form-control" value="<?= e($to) ?>"></label>
    <select name="status" class="form-control">
      <option value="">ทุกสถานะ</option>
      <?php foreach (BOOK_LABELS as $k => $l): ?>
        <option value="<?= $k ?>" <?= $statusF === $k ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-ghost" type="submit">กรอง</button>
    <a href="report2.php" class="btn btn-ghost">ล้าง</a>
  </form>
  <button class="btn btn-outline" onclick="window.print()">🖨 พิมพ์รายงาน</button>
</div>

<div class="stats-grid">
  <div class="stat-card"><div class="stat-label">การจองทั้งหมด</div><div class="stat-value"><?= $totalBook ?></div><div class="stat-sub">รายการ</div></div>
  <?php foreach (BOOK_LABELS as $k => $l): ?>
  <div class="stat-card"><div class="stat-label"><?= $l ?></div><div class="stat-value"><?= $st[$k] ?></div>
    <div class="stat-sub"><?= $totalBook ? round($st[$k] / $totalBook * 100) : 0 ?>%</div></div>
  <?php endforeach; ?>
</div>

<div class="report-grid">
  <section class="panel report-section">
    <h2>📅 จำนวนการจองรายเดือน</h2>
    <div class="chart-bar-wrap">
      <?php foreach ($byMonth as $r): [$y, $m] = explode('-', $r['ym']); ?>
      <div class="chart-bar-item">
        <div class="chart-bar-label"><?= $thMonths[$m] ?> <?= $y + 543 ?></div>
        <div class="chart-bar-track"><div class="chart-bar-fill" style="width:<?= round($r['n'] / $maxMonth * 100) ?>%"></div></div>
        <div class="chart-bar-val"><?= (int)$r['n'] ?></div>
      </div>
      <?php endforeach; if (!$byMonth): ?><p class="text-muted">ยังไม่มีข้อมูลการจอง</p><?php endif; ?>
    </div>
  </section>

  <section class="panel report-section">
    <h2>🏆 รถยอดนิยม (ถูกจองมากสุด)</h2>
    <div class="chart-bar-wrap">
      <?php foreach ($topCars as $r): ?>
      <div class="chart-bar-item">
        <div class="chart-bar-label" style="width:170px;"><?= e($r['brand_name'] . ' ' . $r['model_name']) ?></div>
        <div class="chart-bar-track"><div class="chart-bar-fill" style="width:<?= round($r['n'] / $maxTop * 100) ?>%"></div></div>
        <div class="chart-bar-val"><?= (int)$r['n'] ?></div>
      </div>
      <?php endforeach; if (!$topCars): ?><p class="text-muted">ยังไม่มีข้อมูลการจอง</p><?php endif; ?>
    </div>
  </section>
</div>

<section class="panel table-panel report-section">
  <h2 style="padding:24px 24px 0;">📋 รายการจองทดลองขับ</h2>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>#</th><th>ลูกค้า</th><th>รถ</th><th>วันที่ / เวลา</th><th>หมายเหตุ</th><th>สถานะ</th><th class="no-print">เปลี่ยนสถานะ</th></tr></thead>
      <tbody>
      <?php foreach ($list as $r): ?>
        <tr>
          <td class="text-muted"><?= $r['booking_id'] ?></td>
          <td><strong><?= e($r['full_name']) ?></strong><br><span class="text-muted" style="font-size:.8rem;"><?= e((string)$r['phone']) ?></span></td>
          <td><?= e($r['brand_name'] . ' ' . $r['model_name']) ?></td>
          <td style="white-space:nowrap;"><?= date('d/m/', strtotime($r['booking_date'])) . (date('Y', strtotime($r['booking_date'])) + 543) ?><br><span class="text-muted"><?= substr($r['booking_time'], 0, 5) ?> น.</span></td>
          <td class="text-muted" style="max-width:200px;"><?= e((string)$r['notes']) ?: '—' ?></td>
          <td><span class="badge badge-<?= e($r['status']) ?>"><?= BOOK_LABELS[$r['status']] ?></span></td>
          <td class="no-print">
            <form method="POST" class="row-actions">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="set_status">
              <input type="hidden" name="booking_id" value="<?= $r['booking_id'] ?>">
              <select name="status" class="form-control" style="padding:6px 10px;width:auto;">
                <?php foreach (BOOK_LABELS as $k => $l): ?>
                  <option value="<?= $k ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-primary btn-sm" type="submit">บันทึก</button>
            </form>
          </td>
        </tr>
      <?php endforeach; if (!$list): ?><tr><td colspan="7" class="empty-row">ไม่พบรายการจอง</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php if ($totalPages > 1): ?>
<div class="pagination no-print">
  <?php if ($page > 1): ?><a class="page-btn" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">←</a><?php endif; ?>
  <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
    <a class="page-btn <?= $p === $page ? 'active' : '' ?>" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
  <?php endfor; ?>
  <?php if ($page < $totalPages): ?><a class="page-btn" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">→</a><?php endif; ?>
</div>
<?php endif; ?>

<?php require 'includes/admin_footer.php'; ?>