<?php
// ============================================================
//  CarVerse 3D | รายงานที่ 1: สต็อกและมูลค่ารถยนต์
//  report1.php
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
requireLogin('login.php?redirect=report1.php');
requireAdmin();

$brandF  = (int)($_GET['brand'] ?? 0);
$statusF = $_GET['status'] ?? '';
$where = ['1=1']; $params = [];
if ($brandF) { $where[] = 'c.brand_id = ?'; $params[] = $brandF; }
if (isset(STATUS_LABELS[$statusF])) { $where[] = 'c.status = ?'; $params[] = $statusF; }
$w = 'WHERE ' . implode(' AND ', $where);

$run = function (string $sql) use ($pdo, $params) {
    $s = $pdo->prepare($sql); $s->execute($params); return $s->fetchAll();
};

// สรุปรวม
$sum = $run("
    SELECT COUNT(*) AS models,
           COALESCE(SUM(c.stock_qty),0) AS units,
           COALESCE(SUM(c.price * c.stock_qty),0) AS stock_value,
           COALESCE(AVG(c.price),0) AS avg_price,
           COALESCE(SUM(c.status='available'),0) AS n_available,
           COALESCE(SUM(c.status='reserved'),0)  AS n_reserved,
           COALESCE(SUM(c.status='sold'),0)      AS n_sold
    FROM cars c $w
")[0];

// ตามแบรนด์
$byBrand = $run("
    SELECT b.brand_name, COUNT(*) AS models, SUM(c.stock_qty) AS units,
           AVG(c.price) AS avg_price, MIN(c.price) AS min_price, MAX(c.price) AS max_price,
           SUM(c.price * c.stock_qty) AS value
    FROM cars c JOIN brands b ON c.brand_id = b.brand_id $w
    GROUP BY b.brand_id, b.brand_name
    ORDER BY value DESC
");

// ตามประเภท
$byType = $run("
    SELECT t.type_name, COUNT(*) AS models, SUM(c.stock_qty) AS units
    FROM cars c JOIN car_types t ON c.type_id = t.type_id $w
    GROUP BY t.type_id, t.type_name
    ORDER BY units DESC
");

// ตามเชื้อเพลิง
$byFuel = $run("
    SELECT c.fuel_type, COUNT(*) AS models
    FROM cars c $w
    GROUP BY c.fuel_type
    ORDER BY models DESC
");

$brands = $pdo->query("SELECT * FROM brands ORDER BY brand_name")->fetchAll();
$maxValue = max(1, ...array_map(fn($r) => (float)$r['value'], $byBrand ?: [['value' => 1]]));
$maxUnits = max(1, ...array_map(fn($r) => (int)$r['units'],  $byType  ?: [['units' => 1]]));
$maxFuel  = max(1, ...array_map(fn($r) => (int)$r['models'], $byFuel  ?: [['models' => 1]]));

$pageTitle = 'รายงานสต็อกและมูลค่ารถยนต์';
$pageDesc  = 'สรุปจำนวนรถ มูลค่าสต็อก และการกระจายตามแบรนด์ ประเภท เชื้อเพลิง';
$activeNav = 'report1';
require 'includes/admin_header.php';
echo '<link rel="stylesheet" href="assets/css/particles.css"><script>!function(d){if(matchMedia("(prefers-reduced-motion: reduce)").matches)return;d.classList.add("pfx-on");setTimeout(function(){window.__pfx||d.classList.remove("pfx-on")},3500)}(document.documentElement)</script><script src="assets/js/particles.js" defer></script>';
?>

<div class="toolbar no-print">
  <form method="GET" class="toolbar-form">
    <select name="brand" class="form-control" onchange="this.form.submit()">
      <option value="0">ทุกแบรนด์</option>
      <?php foreach ($brands as $b): ?>
        <option value="<?= $b['brand_id'] ?>" <?= $brandF === (int)$b['brand_id'] ? 'selected' : '' ?>><?= e($b['brand_name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="status" class="form-control" onchange="this.form.submit()">
      <option value="">ทุกสถานะ</option>
      <?php foreach (STATUS_LABELS as $k => $l): ?>
        <option value="<?= $k ?>" <?= $statusF === $k ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
    <a href="report1.php" class="btn btn-ghost">ล้างตัวกรอง</a>
  </form>
  <button class="btn btn-outline" onclick="window.print()">🖨 พิมพ์รายงาน</button>
</div>

<div class="stats-grid">
  <div class="stat-card"><div class="stat-label">รุ่นรถทั้งหมด</div><div class="stat-value"><?= number_format((int)$sum['models']) ?></div><div class="stat-sub">รุ่นในระบบ</div></div>
  <div class="stat-card"><div class="stat-label">จำนวนคันในสต็อก</div><div class="stat-value"><?= number_format((int)$sum['units']) ?></div><div class="stat-sub">คัน</div></div>
  <div class="stat-card"><div class="stat-label">มูลค่าสต็อกรวม</div><div class="stat-value" style="font-size:1.5rem;"><?= number_format((float)$sum['stock_value'] / 1000000, 2) ?> ล้าน</div><div class="stat-sub">บาท</div></div>
  <div class="stat-card"><div class="stat-label">ราคาเฉลี่ย / รุ่น</div><div class="stat-value" style="font-size:1.5rem;"><?= number_format((float)$sum['avg_price']) ?></div><div class="stat-sub">บาท</div></div>
  <div class="stat-card"><div class="stat-label">พร้อมขาย / จอง / ขายแล้ว</div><div class="stat-value" style="font-size:1.5rem;"><?= (int)$sum['n_available'] ?> / <?= (int)$sum['n_reserved'] ?> / <?= (int)$sum['n_sold'] ?></div><div class="stat-sub">รุ่น</div></div>
</div>

<div class="report-grid">
  <section class="panel report-section">
    <h2>💰 มูลค่าสต็อกตามแบรนด์</h2>
    <div class="chart-bar-wrap">
      <?php foreach ($byBrand as $r): ?>
      <div class="chart-bar-item">
        <div class="chart-bar-label"><?= e($r['brand_name']) ?></div>
        <div class="chart-bar-track"><div class="chart-bar-fill" style="width:<?= round($r['value'] / $maxValue * 100) ?>%"></div></div>
        <div class="chart-bar-val" style="width:90px;"><?= number_format($r['value'] / 1000000, 1) ?> ล้าน</div>
      </div>
      <?php endforeach; if (!$byBrand): ?><p class="text-muted">ไม่มีข้อมูล</p><?php endif; ?>
    </div>
  </section>

  <section class="panel report-section">
    <h2>🚙 จำนวนคันตามประเภทรถ</h2>
    <div class="chart-bar-wrap">
      <?php foreach ($byType as $r): ?>
      <div class="chart-bar-item">
        <div class="chart-bar-label"><?= e($r['type_name']) ?></div>
        <div class="chart-bar-track"><div class="chart-bar-fill" style="width:<?= round($r['units'] / $maxUnits * 100) ?>%"></div></div>
        <div class="chart-bar-val"><?= (int)$r['units'] ?> คัน</div>
      </div>
      <?php endforeach; if (!$byType): ?><p class="text-muted">ไม่มีข้อมูล</p><?php endif; ?>
    </div>

    <h2 style="margin-top:32px;">⛽ สัดส่วนตามเชื้อเพลิง</h2>
    <div class="chart-bar-wrap">
      <?php foreach ($byFuel as $r): ?>
      <div class="chart-bar-item">
        <div class="chart-bar-label"><?= FUEL_LABELS[$r['fuel_type']] ?? e($r['fuel_type']) ?></div>
        <div class="chart-bar-track"><div class="chart-bar-fill" style="width:<?= round($r['models'] / $maxFuel * 100) ?>%"></div></div>
        <div class="chart-bar-val"><?= (int)$r['models'] ?> รุ่น</div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<section class="panel table-panel report-section">
  <h2 style="padding:24px 24px 0;">📋 ตารางสรุปรายแบรนด์</h2>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>แบรนด์</th><th>จำนวนรุ่น</th><th>จำนวนคัน</th><th>ราคาต่ำสุด</th><th>ราคาเฉลี่ย</th><th>ราคาสูงสุด</th><th>มูลค่าสต็อก</th></tr></thead>
      <tbody>
        <?php foreach ($byBrand as $r): ?>
        <tr>
          <td><strong><?= e($r['brand_name']) ?></strong></td>
          <td><?= (int)$r['models'] ?></td>
          <td><?= (int)$r['units'] ?></td>
          <td><?= formatPrice((float)$r['min_price']) ?></td>
          <td><?= formatPrice((float)$r['avg_price']) ?></td>
          <td><?= formatPrice((float)$r['max_price']) ?></td>
          <td class="text-accent" style="font-weight:600;"><?= formatPrice((float)$r['value']) ?></td>
        </tr>
        <?php endforeach; if (!$byBrand): ?><tr><td colspan="7" class="empty-row">ไม่มีข้อมูล</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require 'includes/admin_footer.php'; ?>