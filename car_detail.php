<?php
// ============================================================
//  CarVerse 3D | รายละเอียดรถยนต์
//  car_detail.php
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

$carId = (int)($_GET['id'] ?? 0);
if (!$carId) { header('Location: search.php'); exit; }

$stmt = $pdo->prepare("
    SELECT c.*, b.brand_name, t.type_name
    FROM   cars c
    JOIN   brands b    ON c.brand_id = b.brand_id
    JOIN   car_types t ON c.type_id  = t.type_id
    WHERE  c.car_id = ?
");
$stmt->execute([$carId]);
$car = $stmt->fetch();
if (!$car) { header('Location: search.php'); exit; }

// ── โมเดล 3D: ใช้ค่าจากฐานข้อมูล ถ้าไม่มีให้ใช้ไฟล์ .glb ตามชื่อรุ่น ──
$modelUrl = trim($car['model_3d_url'] ?? '');
if ($modelUrl === '' && stripos($car['model_name'], 'fortuner') !== false) {
    $modelUrl = 'assets/models/fortuner_lite.glb';
}

// ── Booking POST ──
$bookMsg = $bookError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    // Action cancel handling if needed
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'book') {
    requireLogin('login.php?redirect=car_detail.php?id=' . $carId);
    $bDate = $_POST['booking_date'] ?? '';
    $bTime = $_POST['booking_time'] ?? '';
    $bNote = trim($_POST['notes'] ?? '');
    if (!$bDate || !$bTime) {
        $bookError = 'กรุณากรอกข้อมูลการจองให้ครบ';
    } elseif (strtotime($bDate) < strtotime('today')) {
        $bookError = 'วันที่จองต้องเป็นวันนี้หรือในอนาคต';
    } else {
        $dup = $pdo->prepare("SELECT booking_id FROM test_drive_bookings WHERE user_id=? AND car_id=? AND booking_date=? AND status IN('pending','confirmed')");
        $dup->execute([$_SESSION['user_id'], $carId, $bDate]);
        if ($dup->fetch()) {
            $bookError = 'คุณมีการจองรถคันนี้วันเดียวกันอยู่แล้ว';
        } else {
            $pdo->prepare("INSERT INTO test_drive_bookings (user_id,car_id,booking_date,booking_time,notes) VALUES (?,?,?,?,?)")
                ->execute([$_SESSION['user_id'], $carId, $bDate, $bTime, $bNote]);
            $bookMsg = 'จองทดลองขับสำเร็จ! เราจะยืนยันภายใน 24 ชั่วโมง 🎉';
        }
    }
}

// ── Related cars (same brand, exclude this) ──
$related = $pdo->prepare("
    SELECT c.*, b.brand_name, t.type_name
    FROM   cars c
    JOIN   brands b ON c.brand_id=b.brand_id
    JOIN   car_types t ON c.type_id=t.type_id
    WHERE  c.brand_id=? AND c.car_id<>? AND c.status='available'
    LIMIT 3
");
$related->execute([$car['brand_id'], $carId]);
$related = $related->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($car['brand_name'].' '.$car['model_name']) ?> | CarVerse 3D</title>
  <meta name="description" content="<?= htmlspecialchars($car['description'] ?? $car['brand_name'].' '.$car['model_name'].' ราคา '.number_format($car['price']).' บาท') ?>">
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/search.css">


  <!-- ES Module Import Map สำหรับ Three.js -->
  <script type="importmap">
  {
    "imports": {
      "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
      "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
    }
  }
  </script>
  <script type="module" src="assets/js/car_viewer.js"></script>

  <style>
    .detail-grid{display:grid;grid-template-columns:1fr 420px;gap:40px;align-items:start;position:relative;z-index:1}
    .car-image-wrap{border-radius:24px;overflow:hidden;border:1px solid rgba(31,200,255,.2);background:rgba(31,200,255,.03);position:relative;aspect-ratio:16/9;min-height:450px;}
    .badge-status{display:inline-block;padding:5px 16px;border-radius:20px;font-size:.78rem;font-weight:700;position:absolute;top:16px;right:16px;z-index:10;}

    .info-panel{background:linear-gradient(145deg,rgba(31,200,255,.06),rgba(11,134,214,.02));border:1px solid rgba(31,200,255,.18);border-radius:24px;padding:32px;position:sticky;top:92px;backdrop-filter:blur(24px);box-shadow:0 4px 40px rgba(0,0,0,.4),inset 0 1px 0 rgba(31,200,255,.1)}
    .car-brand-tag{font-size:.72rem;color:#1fc8ff;font-weight:700;text-transform:uppercase;letter-spacing:.14em;margin-bottom:6px}
    .car-title{font-family:'Barlow Condensed',sans-serif;font-size:clamp(1.8rem,3vw,2.6rem);font-weight:800;color:#e9f7ff;line-height:1.1;margin-bottom:4px;text-transform:uppercase}
    .car-year-type{font-size:.85rem;color:#8aa3b2;margin-bottom:20px}
    .car-price{font-family:'Barlow Condensed',sans-serif;font-size:2.4rem;font-weight:800;color:#1fc8ff;line-height:1;text-shadow:0 0 24px rgba(31,200,255,.4);margin-bottom:24px}
    .spec-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:24px}
    .spec-box{background:rgba(31,200,255,.05);border:1px solid rgba(31,200,255,.1);border-radius:12px;padding:12px 14px}
    .spec-box-lbl{font-size:.68rem;color:#8aa3b2;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px}
    .spec-box-val{font-size:.95rem;font-weight:600;color:#e9f7ff}
    .divider{height:1px;background:linear-gradient(90deg,transparent,rgba(31,200,255,.2),transparent);margin:20px 0}

    .specs-section{margin-top:40px}
    .specs-title{font-family:'Barlow Condensed',sans-serif;font-size:1.4rem;font-weight:800;color:#e9f7ff;text-transform:uppercase;letter-spacing:.1em;margin-bottom:20px;display:flex;align-items:center;gap:12px}
    .specs-title::after{content:'';flex:1;height:1px;background:rgba(31,200,255,.15)}
    .specs-table{width:100%;border-collapse:collapse}
    .specs-table tr{border-bottom:1px solid rgba(31,200,255,.07)}
    .specs-table tr:hover td{background:rgba(31,200,255,.04)}
    .specs-table td{padding:13px 16px;font-size:.9rem}
    .specs-table td:first-child{color:#8aa3b2;width:40%;font-weight:500}
    .specs-table td:last-child{color:#e9f7ff;font-weight:600}

    .desc-section{margin-top:40px;background:linear-gradient(145deg,rgba(31,200,255,.05),rgba(11,134,214,.01));border:1px solid rgba(31,200,255,.12);border-radius:20px;padding:28px}
    .related-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;margin-top:20px}

    .booking-form-inner{display:flex;flex-direction:column;gap:14px}
    .booking-form-inner .form-group{margin-bottom:0}
    @media(max-width:1024px){.detail-grid{grid-template-columns:1fr}.info-panel{position:static}}
    @media(max-width:600px){.spec-grid{grid-template-columns:1fr}}
  </style>
</head>
<body>

<canvas id="particles" class="particles-canvas"></canvas>

<!-- Navbar -->
<nav class="navbar" id="main-navbar">
  <div class="nav-logo">CAR<span>VERSE</span></div>
  <div class="nav-links">
    <a href="index.php"  class="nav-link">หน้าหลัก</a>
    <a href="search.php" class="nav-link active">ค้นหารถ</a>
    <?php if (isLoggedIn()): ?>
      <a href="my_bookings.php" class="nav-link">การจองของฉัน</a>
      <a href="profile.php"     class="nav-link">โปรไฟล์</a>
      <?php if (isAdmin()): ?>
        <a href="add_car.php" class="nav-link">เพิ่มรถ</a>
        <a href="report1.php" class="nav-link">รายงาน</a>
      <?php endif; ?>
      <a href="logout.php" class="btn btn-outline btn-sm">ออกจากระบบ</a>
    <?php else: ?>
      <a href="login.php"    class="nav-link">เข้าสู่ระบบ</a>
      <a href="register.php" class="btn btn-primary btn-sm">สมัครสมาชิก</a>
    <?php endif; ?>
  </div>
</nav>

<!-- Breadcrumb -->
<div style="padding:100px 0 0;position:relative;z-index:1;">
  <div class="container">
    <div style="font-size:.82rem;color:#8aa3b2;margin-bottom:20px;">
      <a href="index.php"  style="color:#8aa3b2;text-decoration:none;">หน้าหลัก</a> /
      <a href="search.php" style="color:#8aa3b2;text-decoration:none;">ค้นหารถ</a> /
      <span style="color:#1fc8ff;"><?= htmlspecialchars($car['brand_name'].' '.$car['model_name']) ?></span>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom:80px;">

  <?php if ($bookMsg):  ?><div class="alert alert-success" data-autohide="6000" style="margin-bottom:24px;">✅ <?= htmlspecialchars($bookMsg) ?></div><?php endif; ?>
  <?php if ($bookError):?><div class="alert alert-danger"  data-autohide="6000" style="margin-bottom:24px;">❌ <?= htmlspecialchars($bookError) ?></div><?php endif; ?>

  <div class="detail-grid">

    <!-- ── LEFT: 360 Viewer + Specs ── -->
    <div>
      <!-- 3D 360 Viewer Container -->
      <div class="car-image-wrap">
        <span class="badge-status badge-<?= $car['status'] ?>">
          <?= STATUS_LABELS[$car['status']] ?? $car['status'] ?>
        </span>
        
        <?php if ($modelUrl !== ''): ?>
        <!-- Element สำหรับเริ่มทำงานกับ car_viewer.js -->
        <div data-car360 
             data-color="<?= htmlspecialchars($car['color'] ?? '') ?>" 
             data-model="<?= htmlspecialchars($modelUrl) ?>"
             style="width:100%;height:100%;">
        </div>
        <?php elseif (!empty($car['image_url'])): ?>
        <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['model_name']) ?>" style="width:100%;height:100%;object-fit:cover;display:block;border-radius:20px;">
        <?php else: ?>
        <div style="height:100%;display:flex;align-items:center;justify-content:center;font-size:4rem;opacity:.3;">🚗</div>
        <?php endif; ?>
      </div>
      <?php if ($modelUrl !== ''): ?>
      <div style="font-size:.7rem;color:#8aa3b2;margin:8px 4px 0;text-align:right;">
        โมเดล 3D เสมือนจริง 360° • <?= htmlspecialchars($car['brand_name'].' '.$car['model_name']) ?>
      </div>
      <?php endif; ?>

      <!-- Specs Table -->
      <div class="specs-section">
        <div class="specs-title">ข้อมูลจำเพาะ</div>
        <div style="background:linear-gradient(145deg,rgba(31,200,255,.04),rgba(11,134,214,.01));border:1px solid rgba(31,200,255,.12);border-radius:20px;overflow:hidden;">
          <table class="specs-table">
            <tr><td>แบรนด์</td><td><?= htmlspecialchars($car['brand_name']) ?></td></tr>
            <tr><td>รุ่น</td><td><?= htmlspecialchars($car['model_name']) ?></td></tr>
            <tr><td>ประเภท</td><td><?= htmlspecialchars($car['type_name']) ?></td></tr>
            <tr><td>ปีรถ</td><td><?= $car['year'] ?></td></tr>
            <tr><td>สี</td><td><?= htmlspecialchars($car['color'] ?? '-') ?></td></tr>
            <tr><td>เชื้อเพลิง</td><td><?= FUEL_LABELS[$car['fuel_type']] ?? $car['fuel_type'] ?></td></tr>
            <tr><td>ระบบเกียร์</td><td><?= TRANS_LABELS[$car['transmission']] ?? $car['transmission'] ?></td></tr>
            <?php if ($car['engine_cc']): ?><tr><td>ความจุเครื่องยนต์</td><td><?= number_format($car['engine_cc']) ?> cc</td></tr><?php endif; ?>
            <?php if ($car['horsepower']): ?><tr><td>แรงม้า</td><td><?= $car['horsepower'] ?> HP</td></tr><?php endif; ?>
            <tr><td>จำนวนที่นั่ง</td><td><?= $car['seats'] ?> ที่นั่ง</td></tr>
            <?php if ($car['mileage']): ?><tr><td>ระยะทาง</td><td><?= number_format($car['mileage']) ?> กม.</td></tr><?php endif; ?>
            <tr><td>สต็อก</td><td><?= $car['stock_qty'] ?> คัน</td></tr>
            <tr><td>สถานะ</td><td><span class="badge badge-<?= $car['status'] ?>"><?= STATUS_LABELS[$car['status']] ?? $car['status'] ?></span></td></tr>
          </table>
        </div>
      </div>

      <!-- Description -->
      <?php if ($car['description']): ?>
      <div class="desc-section">
        <div class="specs-title">รายละเอียด</div>
        <p style="color:#8aa3b2;line-height:1.8;font-size:.95rem;"><?= nl2br(htmlspecialchars($car['description'])) ?></p>
      </div>
      <?php endif; ?>

      <!-- Related Cars -->
      <?php if ($related): ?>
      <div style="margin-top:48px;">
        <div class="specs-title">รถ<?= htmlspecialchars($car['brand_name']) ?> อื่นๆ</div>
        <div class="related-grid">
          <?php foreach ($related as $r): ?>
          <a href="car_detail.php?id=<?= $r['car_id'] ?>" class="car-card" style="text-decoration:none;display:block;">
            <div style="height:150px;overflow:hidden;background:rgba(31,200,255,.04);">
              <?php if ($r['image_url']): ?>
                <img src="<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['model_name']) ?>" class="car-card-img" style="height:150px;">
              <?php else: ?>
                <div style="height:100%;display:flex;align-items:center;justify-content:center;font-size:3rem;opacity:.2;">🚗</div>
              <?php endif; ?>
            </div>
            <div class="car-card-body">
              <div class="car-card-brand"><?= htmlspecialchars($r['brand_name']) ?> · <?= htmlspecialchars($r['type_name']) ?></div>
              <div class="car-card-name"><?= htmlspecialchars($r['model_name']) ?> <?= $r['year'] ?></div>
              <div class="car-card-price"><?= formatPrice((float)$r['price']) ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- ── RIGHT: Info Panel ── -->
    <aside class="info-panel">
      <div class="car-brand-tag"><?= htmlspecialchars($car['brand_name']) ?> · <?= htmlspecialchars($car['type_name']) ?></div>
      <h1 class="car-title"><?= htmlspecialchars($car['model_name']) ?></h1>
      <div class="car-year-type">ปี <?= $car['year'] ?> <?php if($car['color']): ?>· <?= htmlspecialchars($car['color']) ?><?php endif; ?></div>
      <div class="car-price"><?= formatPrice((float)$car['price']) ?></div>

      <!-- Quick Specs -->
      <div class="spec-grid">
        <div class="spec-box">
          <div class="spec-box-lbl">เชื้อเพลิง</div>
          <div class="spec-box-val"><?= FUEL_LABELS[$car['fuel_type']] ?? '-' ?></div>
        </div>
        <div class="spec-box">
          <div class="spec-box-lbl">เกียร์</div>
          <div class="spec-box-val"><?= TRANS_LABELS[$car['transmission']] ?? '-' ?></div>
        </div>
        <?php if ($car['horsepower']): ?>
        <div class="spec-box">
          <div class="spec-box-lbl">แรงม้า</div>
          <div class="spec-box-val"><?= $car['horsepower'] ?> HP</div>
        </div>
        <?php endif; ?>
        <div class="spec-box">
          <div class="spec-box-lbl">ที่นั่ง</div>
          <div class="spec-box-val"><?= $car['seats'] ?> คน</div>
        </div>
      </div>

      <div class="divider"></div>

      <!-- Booking Form -->
      <?php if ($car['status'] === 'available'): ?>
        <?php if (isLoggedIn()): ?>
        <div style="margin-bottom:8px;font-size:.88rem;font-weight:600;color:#e9f7ff;">🗓 จองทดสอบขับ</div>
        <form method="POST" class="booking-form-inner">
          <input type="hidden" name="action" value="book">
          <div class="form-group">
            <label class="form-label">วันที่</label>
            <input type="date" name="booking_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">เวลา</label>
            <input type="time" name="booking_time" class="form-control" min="09:00" max="18:00" value="10:00" required>
          </div>
          <div class="form-group">
            <label class="form-label">หมายเหตุ (ไม่บังคับ)</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="เช่น ต้องการขับในวันหยุด"></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-full" style="margin-top:4px;">🚗 ยืนยันการจอง</button>
        </form>
        <?php else: ?>
        <div style="text-align:center;padding:20px 0;">
          <p style="color:#8aa3b2;font-size:.88rem;margin-bottom:16px;">เข้าสู่ระบบเพื่อจองทดสอบขับ</p>
          <a href="login.php?redirect=car_detail.php?id=<?= $carId ?>" class="btn btn-primary btn-full" style="display:flex;justify-content:center;text-decoration:none;">🔐 เข้าสู่ระบบ</a>
          <a href="register.php" class="btn btn-ghost btn-full" style="display:flex;justify-content:center;text-decoration:none;margin-top:10px;">สมัครสมาชิกฟรี</a>
        </div>
        <?php endif; ?>
      <?php else: ?>
      <div style="text-align:center;padding:16px;background:rgba(231,76,60,.1);border:1px solid rgba(231,76,60,.3);border-radius:14px;color:#e74c3c;font-weight:600;">
        ❌ รถคันนี้ไม่พร้อมให้บริการ
      </div>
      <?php endif; ?>

      <div class="divider"></div>
      <a href="search.php" class="btn btn-ghost btn-full" style="display:flex;justify-content:center;text-decoration:none;">← กลับไปค้นหารถ</a>
    </aside>
  </div>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>