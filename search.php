<?php
// ============================================================
//  CarVerse 3D | Search & Catalog Page
//  search.php
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

// ── Filter Parameters ──
$brand_id   = (int)($_GET['brand']    ?? 0);
$type_id    = (int)($_GET['type']     ?? 0);
$fuel       = $_GET['fuel']           ?? '';
$trans      = $_GET['trans']          ?? '';
$min_price  = (int)(str_replace(',', '', $_GET['min_price'] ?? 0));
$max_price  = (int)(str_replace(',', '', $_GET['max_price'] ?? 0));
$keyword    = trim($_GET['q']         ?? '');
$sort       = $_GET['sort']           ?? 'newest';
$page       = max(1, (int)($_GET['page'] ?? 1));
$per_page   = 9;

// ── Build WHERE clause ──
$where  = ["c.status = 'available'"];
$params = [];

if ($brand_id)  { $where[] = 'c.brand_id = ?'; $params[] = $brand_id; }
if ($type_id)   { $where[] = 'c.type_id  = ?'; $params[] = $type_id;  }
if ($fuel)      { $where[] = 'c.fuel_type = ?'; $params[] = $fuel;     }
if ($trans)     { $where[] = 'c.transmission = ?'; $params[] = $trans; }
if ($min_price) { $where[] = 'c.price >= ?'; $params[] = $min_price;   }
if ($max_price) { $where[] = 'c.price <= ?'; $params[] = $max_price;   }
if ($keyword) {
    $where[] = '(c.model_name LIKE ? OR b.brand_name LIKE ? OR c.description LIKE ?)';
    $like = "%$keyword%";
    array_push($params, $like, $like, $like);
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

// ── Sort ──
$orderMap = [
  'newest'    => 'c.created_at DESC',
  'price_asc' => 'c.price ASC',
  'price_desc'=> 'c.price DESC',
  'name'      => 'c.model_name ASC',
];
$order = $orderMap[$sort] ?? 'c.created_at DESC';

// ── Count ──
$countSql = "SELECT COUNT(*) FROM cars c JOIN brands b ON c.brand_id=b.brand_id JOIN car_types t ON c.type_id=t.type_id $whereSql";
$total = $pdo->prepare($countSql);
$total->execute($params);
$totalCount = $total->fetchColumn();
$totalPages = max(1, ceil($totalCount / $per_page));
$offset     = ($page - 1) * $per_page;

// ── Fetch Cars ──
$sql = "
    SELECT c.*, b.brand_name, t.type_name
    FROM   cars c
    JOIN   brands b    ON c.brand_id = b.brand_id
    JOIN   car_types t ON c.type_id  = t.type_id
    $whereSql
    ORDER  BY $order
    LIMIT  $per_page OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();

// ── Filter Options ──
$brands   = $pdo->query("SELECT * FROM brands ORDER BY brand_name")->fetchAll();
$types    = $pdo->query("SELECT * FROM car_types ORDER BY type_name")->fetchAll();

// ── Test Drive Booking ──
$bookMsg   = '';
$bookError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    requireLogin('login.php?redirect=search.php');
    $bCar  = (int)$_POST['car_id'];
    $bDate = $_POST['booking_date'];
    $bTime = $_POST['booking_time'];
    $bNote = trim($_POST['notes'] ?? '');

    if (!$bCar || !$bDate || !$bTime) {
        $bookError = 'กรุณากรอกข้อมูลการจองให้ครบ';
    } elseif (strtotime($bDate) < strtotime('today')) {
        $bookError = 'วันที่จองต้องเป็นวันนี้หรือในอนาคต';
    } else {
        $dup = $pdo->prepare("SELECT booking_id FROM test_drive_bookings WHERE user_id=? AND car_id=? AND booking_date=? AND status IN('pending','confirmed')");
        $dup->execute([$_SESSION['user_id'], $bCar, $bDate]);
        if ($dup->fetch()) {
            $bookError = 'คุณมีการจองรถคันนี้วันเดียวกันอยู่แล้ว';
        } else {
            $ins = $pdo->prepare("INSERT INTO test_drive_bookings (user_id,car_id,booking_date,booking_time,notes) VALUES (?,?,?,?,?)");
            $ins->execute([$_SESSION['user_id'], $bCar, $bDate, $bTime, $bNote]);
            $bookMsg = 'จองทดลองขับสำเร็จ! เราจะยืนยันภายใน 24 ชั่วโมง 🎉';
        }
    }
}

$pageTitle   = 'ค้นหารถยนต์';
$pageHeading = 'ค้นหา <span class="text-accent">รถยนต์</span>';
$pageDesc    = 'รถยนต์พรีเมียมกว่า ' . $totalCount . ' คันรอให้คุณค้นพบ';
$activeNav   = 'search';
require 'includes/user_header.php';
echo '<link rel="stylesheet" href="assets/css/particles.css"><script>!function(d){if(matchMedia("(prefers-reduced-motion: reduce)").matches)return;d.classList.add("pfx-on");setTimeout(function(){window.__pfx||d.classList.remove("pfx-on")},3500)}(document.documentElement)</script><script src="assets/js/particles.js" defer></script>';
?>

<?php if ($bookMsg):  ?><div class="alert alert-success" data-autohide="6000">✅ <?= e($bookMsg)  ?></div><?php endif; ?>
<?php if ($bookError):?><div class="alert alert-danger"  data-autohide="6000">❌ <?= e($bookError)?></div><?php endif; ?>

  <div class="search-layout">

    <!-- ── FILTER SIDEBAR ── -->
    <aside class="filter-sidebar">
      <div class="filter-header">
        <h2>🔍 ค้นกรอง</h2>
        <a href="search.php" class="filter-clear">ล้างทั้งหมด</a>
      </div>

      <form method="GET" action="search.php" id="filterForm">

        <!-- Keyword Search -->
        <div class="filter-group">
          <label class="filter-label">ค้นหา</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" name="q" class="form-control input-with-icon"
              placeholder="ชื่อรถ แบรนด์..." value="<?= e($keyword) ?>">
          </div>
        </div>

        <!-- Brand -->
        <div class="filter-group">
          <label class="filter-label">แบรนด์</label>
          <div class="filter-options">
            <label class="filter-chip <?= !$brand_id ? 'active' : '' ?>">
              <input type="radio" name="brand" value="0" <?= !$brand_id ? 'checked' : '' ?> onchange="this.form.submit()">
              ทั้งหมด
            </label>
            <?php foreach ($brands as $b): ?>
            <label class="filter-chip <?= $brand_id === (int)$b['brand_id'] ? 'active' : '' ?>">
              <input type="radio" name="brand" value="<?= $b['brand_id'] ?>" <?= $brand_id === (int)$b['brand_id'] ? 'checked' : '' ?> onchange="this.form.submit()">
              <?= e($b['brand_name']) ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Type -->
        <div class="filter-group">
          <label class="filter-label">ประเภทรถ</label>
          <select name="type" class="form-control" onchange="this.form.submit()">
            <option value="0">-- ทุกประเภท --</option>
            <?php foreach ($types as $t): ?>
            <option value="<?= $t['type_id'] ?>" <?= $type_id === (int)$t['type_id'] ? 'selected' : '' ?>>
              <?= e($t['type_name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Fuel Type -->
        <div class="filter-group">
          <label class="filter-label">เชื้อเพลิง</label>
          <select name="fuel" class="form-control" onchange="this.form.submit()">
            <option value="">-- ทุกประเภท --</option>
            <?php foreach (['gasoline'=>'น้ำมัน','diesel'=>'ดีเซล','electric'=>'ไฟฟ้า','hybrid'=>'ไฮบริด'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= $fuel === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Price Range -->
        <div class="filter-group">
          <label class="filter-label">ช่วงราคา (บาท)</label>
          <div class="price-inputs">
            <input type="text" name="min_price" class="form-control price-input" placeholder="ต่ำสุด" value="<?= $min_price ? number_format($min_price) : '' ?>">
            <span>-</span>
            <input type="text" name="max_price" class="form-control price-input" placeholder="สูงสุด" value="<?= $max_price ? number_format($max_price) : '' ?>">
          </div>
        </div>

        <!-- Sort -->
        <div class="filter-group">
          <label class="filter-label">เรียงตาม</label>
          <select name="sort" class="form-control" onchange="this.form.submit()">
            <option value="newest"     <?= $sort === 'newest'     ? 'selected':'' ?>>ใหม่ล่าสุด</option>
            <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected':'' ?>>ราคา: น้อย → มาก</option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected':'' ?>>ราคา: มาก → น้อย</option>
            <option value="name"       <?= $sort === 'name'       ? 'selected':'' ?>>ชื่อ A-Z</option>
          </select>
        </div>

        <button type="submit" class="btn btn-primary btn-full">ค้นหา</button>
      </form>
    </aside>

    <!-- ── CAR RESULTS ── -->
    <main class="search-results">

      <div class="result-meta">
        <span>พบรถ <strong><?= $totalCount ?></strong> คัน</span>
      </div>

      <?php if (empty($cars)): ?>
      <div class="no-results">
        <div style="font-size:4rem;">🔍</div>
        <h3>ไม่พบรถที่ตรงกัน</h3>
        <p>ลองเปลี่ยนเงื่อนไขการค้นหา</p>
        <a href="search.php" class="btn btn-outline" style="margin-top:16px;">ล้างตัวกรอง</a>
      </div>
      <?php else: ?>

      <div class="car-grid">
        <?php foreach ($cars as $i => $car): ?>
        <div class="car-card">
          <div style="position:relative;overflow:hidden;height:200px;background:var(--clr-surface);">
            <?php if ($car['image_url']): ?>
              <img src="<?= e($car['image_url']) ?>" alt="<?= e($car['model_name']) ?>" class="car-card-img">
            <?php else: ?>
              <div class="car-placeholder">
                <svg width="56" height="56" fill="none" stroke="rgba(232,184,75,0.3)" stroke-width="1.5" viewBox="0 0 24 24">
                  <path d="M5 17H3a2 2 0 0 1-2-2v-4l2.85-5.7A2 2 0 0 1 5.63 4h12.74a2 2 0 0 1 1.78 1.1L23 11v4a2 2 0 0 1-2 2h-2"/>
                  <circle cx="7.5" cy="17.5" r="2.5"/><circle cx="16.5" cy="17.5" r="2.5"/>
                </svg>
              </div>
            <?php endif; ?>
            <span class="badge badge-<?= $car['status'] ?>" style="position:absolute;top:10px;right:10px;">
              <?= ['available'=>'พร้อมขาย','sold'=>'ขายแล้ว','reserved'=>'จอง'][$car['status']] ?? $car['status'] ?>
            </span>
            <?php if ($car['model_3d_url']): ?>
            <span style="position:absolute;top:10px;left:10px;background:rgba(26,108,245,0.8);color:#fff;font-size:0.7rem;padding:3px 8px;border-radius:12px;font-weight:600;">3D</span>
            <?php endif; ?>
          </div>
          <div class="car-card-body">
            <div class="car-card-brand"><?= e($car['brand_name']) ?> · <?= e($car['type_name']) ?></div>
            <div class="car-card-name"><?= e($car['model_name']) ?> <?= $car['year'] ?></div>
            <div class="car-card-price"><?= formatPrice($car['price']) ?></div>
            <div class="car-card-specs">
              <div class="spec-item">⛽ <?= ['gasoline'=>'น้ำมัน','diesel'=>'ดีเซล','electric'=>'ไฟฟ้า','hybrid'=>'ไฮบริด'][$car['fuel_type']] ?? $car['fuel_type'] ?></div>
              <div class="spec-item">⚙️ <?= ['manual'=>'Manual','automatic'=>'Auto','cvt'=>'CVT'][$car['transmission']] ?? '' ?></div>
              <?php if ($car['horsepower']): ?><div class="spec-item">⚡ <?= $car['horsepower'] ?> HP</div><?php endif; ?>
              <div class="spec-item">👤 <?= $car['seats'] ?> ที่นั่ง</div>
            </div>
            
            <!-- ปุ่มดำเนินการ -->
            <div style="margin-top:14px;display:grid;grid-template-columns:1fr 1fr;gap:6px;">
              <a href="car_detail.php?id=<?= $car['car_id'] ?>" class="btn btn-ghost btn-sm" style="grid-column:1/-1;justify-content:center;text-decoration:none;">🔍 ดูรายละเอียด</a>
              <a href="buy_car.php?id=<?= $car['car_id'] ?>" class="btn btn-primary btn-sm" style="justify-content:center;text-decoration:none;">🛒 ซื้อรถ</a>
              <?php if (isLoggedIn()): ?>
              <button class="btn btn-outline btn-sm"
                onclick="openBookingModal(<?= $car['car_id'] ?>, '<?= addslashes(e($car['brand_name'].' '.$car['model_name'])) ?>')"
                style="justify-content:center;">
                🚗 จอง
              </button>
              <?php else: ?>
              <a href="login.php?redirect=search.php" class="btn btn-outline btn-sm" style="justify-content:center;text-decoration:none;">🔐 จอง</a>
              <?php endif; ?>
            </div>

          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>" class="page-btn">←</a>
        <?php endif; ?>
        <?php for ($p = max(1, $page-2); $p <= min($totalPages, $page+2); $p++): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>" class="page-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>" class="page-btn">→</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php endif; ?>
    </main>
  </div>
</div>

<!-- ── TEST DRIVE BOOKING MODAL ── -->
<div class="modal-overlay" id="bookingModal">
  <div class="modal-box" style="max-width:500px;">
    <div class="modal-title">🚗 จองทดลองขับ – <span id="modalCarName"></span></div>
    <div class="modal-body">กรอกข้อมูลเพื่อจองคิวทดลองขับ เราจะยืนยันภายใน 24 ชั่วโมง</div>
    <form method="POST" action="search.php">
      <input type="hidden" name="action" value="book">
      <input type="hidden" name="car_id" id="modalCarId">
      <div class="form-group">
        <label class="form-label">วันที่ต้องการทดลองขับ</label>
        <input type="date" name="booking_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">เวลา</label>
        <input type="time" name="booking_time" class="form-control" min="09:00" max="18:00" value="10:00" required>
      </div>
      <div class="form-group">
        <label class="form-label">หมายเหตุเพิ่มเติม (ไม่บังคับ)</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="เช่น ต้องการขับในวันหยุด"></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('bookingModal')">ยกเลิก</button>
        <button type="submit" class="btn btn-primary">ยืนยันการจอง</button>
      </div>
    </form>
  </div>
</div>

<script src="assets/js/main.js"></script>
<script>
  function openBookingModal(carId, carName) {
    document.getElementById('modalCarId').value  = carId;
    document.getElementById('modalCarName').textContent = carName;
    document.getElementById('bookingModal').classList.add('active');
  }

  // ฟังก์ชันใส่คอมม่าคั่นราคาอัตโนมัติขณะพิมพ์
  document.querySelectorAll('.price-input').forEach(input => {
    input.addEventListener('input', function (e) {
      let value = this.value.replace(/,/g, '');
      if (!isNaN(value) && value !== '') {
        this.value = Number(value).toLocaleString('en-US');
      }
    });

    // ลบคอมม่าออกก่อนส่งฟอร์ม เพื่อให้ PHP รับค่าตัวเลขไปประมวลผลได้ถูกต้อง
    input.form.addEventListener('submit', function() {
      document.querySelectorAll('.price-input').forEach(el => {
        el.value = el.value.replace(/,/g, '');
      });
    });
  });
</script>
<script src="assets/js/main.js"></script>
<?php require 'includes/user_footer.php'; ?>ฟ