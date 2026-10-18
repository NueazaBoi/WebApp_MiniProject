<?php
// ============================================================
//  CarVerse 3D | จัดการรถ: รายการ + แก้ไข (Read / Update)
//  edit_car.php        → ตารางรถทั้งหมด (ค้นหา/กรอง/แบ่งหน้า)
//  edit_car.php?id=X   → ฟอร์มแก้ไขรถคันนั้น
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
requireLogin('login.php?redirect=edit_car.php');
requireAdmin();

$brands = $pdo->query("SELECT * FROM brands ORDER BY brand_name")->fetchAll();
$types  = $pdo->query("SELECT * FROM car_types ORDER BY type_name")->fetchAll();
$id     = (int)($_GET['id'] ?? 0);

/* ═══════════════ โหมดแก้ไข ═══════════════ */
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE car_id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        setFlash('error', 'ไม่พบข้อมูลรถที่ต้องการแก้ไข');
        header('Location: edit_car.php');
        exit;
    }

    $errors = [];
    $car    = $existing;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrf()) {
            $errors['_'] = 'Session หมดอายุ กรุณาลองใหม่';
        } else {
            [$errors, $car] = validateCar($_POST);
            $car['image_url'] = $existing['image_url'];
            $newImage = null;
            if (!$errors) {
                try { $newImage = uploadCarImage($_FILES['image'] ?? []); }
                catch (Exception $ex) { $errors['_'] = $ex->getMessage(); }
            }
            if (!$errors) {
                $imagePath = $newImage ?? $existing['image_url'];
                $upd = $pdo->prepare("
                    UPDATE cars SET brand_id=?, type_id=?, model_name=?, year=?, color=?, price=?, mileage=?,
                           fuel_type=?, transmission=?, engine_cc=?, horsepower=?, seats=?, description=?,
                           image_url=?, model_3d_url=?, stock_qty=?, status=?
                    WHERE car_id=?
                ");
                $upd->execute([
                    $car['brand_id'], $car['type_id'], $car['model_name'], $car['year'], $car['color'] ?: null,
                    $car['price'], $car['mileage'], $car['fuel_type'], $car['transmission'],
                    $car['engine_cc'], $car['horsepower'], $car['seats'], $car['description'] ?: null,
                    $imagePath, $car['model_3d_url'] ?: null, $car['stock_qty'], $car['status'], $id,
                ]);
                if ($newImage) deleteUploadedFile($existing['image_url']);   // ลบรูปเก่า
                setFlash('success', '✅ แก้ไขข้อมูล "' . $car['model_name'] . '" เรียบร้อยแล้ว');
                header('Location: edit_car.php');
                exit;
            }
        }
    }

    $pageTitle   = 'แก้ไขข้อมูลรถ';
    $pageDesc    = 'รหัสรถ #' . $id . ' — ' . $existing['model_name'];
    $activeNav   = 'manage';
    $formAction  = 'edit_car.php?id=' . $id;
    $submitLabel = '💾 บันทึกการแก้ไข';
    require 'includes/admin_header.php';
    echo '<link rel="stylesheet" href="assets/css/particles.css"><script>!function(d){if(matchMedia("(prefers-reduced-motion: reduce)").matches)return;d.classList.add("pfx-on");setTimeout(function(){window.__pfx||d.classList.remove("pfx-on")},3500)}(document.documentElement)</script><script src="assets/js/particles.js" defer></script>';
    if (isset($errors['_'])): ?>
      <div class="alert alert-danger"><?= e($errors['_']) ?></div>
    <?php elseif ($errors): ?>
      <div class="alert alert-danger">กรุณาตรวจสอบข้อมูลที่กรอกให้ถูกต้อง</div>
    <?php endif;
    require 'includes/car_form.php';
    require 'includes/admin_footer.php';
    exit;
}

/* ═══════════════ โหมดรายการ ═══════════════ */
$q       = trim($_GET['q'] ?? '');
$status  = $_GET['status'] ?? '';
$brandF  = (int)($_GET['brand'] ?? 0);
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

$where = ['1=1']; $params = [];
if ($q !== '') {
    $where[] = '(c.model_name LIKE ? OR b.brand_name LIKE ? OR c.color LIKE ?)';
    array_push($params, "%$q%", "%$q%", "%$q%");
}
if (isset(STATUS_LABELS[$status])) { $where[] = 'c.status = ?'; $params[] = $status; }
if ($brandF)                       { $where[] = 'c.brand_id = ?'; $params[] = $brandF; }
$whereSql = 'WHERE ' . implode(' AND ', $where);

$cnt = $pdo->prepare("SELECT COUNT(*) FROM cars c JOIN brands b ON c.brand_id=b.brand_id $whereSql");
$cnt->execute($params);
$total      = (int)$cnt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$stmt = $pdo->prepare("
    SELECT c.*, b.brand_name, t.type_name
    FROM cars c
    JOIN brands b    ON c.brand_id = b.brand_id
    JOIN car_types t ON c.type_id  = t.type_id
    $whereSql
    ORDER BY c.car_id DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$cars = $stmt->fetchAll();

$pageTitle = 'จัดการรถยนต์';
$pageDesc  = "รถทั้งหมด $total คัน — แก้ไขหรือลบข้อมูลได้จากหน้านี้";
$activeNav = 'manage';
require 'includes/admin_header.php';
echo '<link rel="stylesheet" href="assets/css/particles.css"><script>!function(d){if(matchMedia("(prefers-reduced-motion: reduce)").matches)return;d.classList.add("pfx-on");setTimeout(function(){window.__pfx||d.classList.remove("pfx-on")},3500)}(document.documentElement)</script><script src="assets/js/particles.js" defer></script>';
?>

<div class="toolbar">
  <form method="GET" action="edit_car.php" class="toolbar-form">
    <input type="text" name="q" class="form-control" placeholder="🔍 ค้นหาชื่อรุ่น / แบรนด์ / สี" value="<?= e($q) ?>">
    <select name="brand" class="form-control" onchange="this.form.submit()">
      <option value="0">ทุกแบรนด์</option>
      <?php foreach ($brands as $b): ?>
        <option value="<?= $b['brand_id'] ?>" <?= $brandF === (int)$b['brand_id'] ? 'selected' : '' ?>><?= e($b['brand_name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="status" class="form-control" onchange="this.form.submit()">
      <option value="">ทุกสถานะ</option>
      <?php foreach (STATUS_LABELS as $k => $l): ?>
        <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-ghost" type="submit">ค้นหา</button>
  </form>
  <a href="add_car.php" class="btn btn-primary">➕ เพิ่มรถใหม่</a>
</div>

<div class="panel table-panel">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th><th>รูป</th><th>รุ่น</th><th>แบรนด์ / ประเภท</th><th>ปี</th>
          <th>ราคา</th><th>สต็อก</th><th>สถานะ</th><th style="text-align:right;">จัดการ</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$cars): ?>
        <tr><td colspan="9" class="empty-row">ไม่พบข้อมูลรถ</td></tr>
      <?php endif; ?>
      <?php foreach ($cars as $c): ?>
        <tr>
          <td class="text-muted"><?= $c['car_id'] ?></td>
          <td>
            <?php if ($c['image_url']): ?>
              <img src="<?= e($c['image_url']) ?>" class="thumb" alt="">
            <?php else: ?>
              <div class="thumb thumb-empty">🚗</div>
            <?php endif; ?>
          </td>
          <td><strong><?= e($c['model_name']) ?></strong><br><span class="text-muted" style="font-size:.8rem;"><?= e((string)$c['color']) ?></span></td>
          <td><?= e($c['brand_name']) ?><br><span class="text-muted" style="font-size:.8rem;"><?= e($c['type_name']) ?></span></td>
          <td><?= $c['year'] ?></td>
          <td class="text-accent" style="font-weight:600;white-space:nowrap;"><?= formatPrice((float)$c['price']) ?></td>
          <td><?= $c['stock_qty'] ?></td>
          <td><span class="badge badge-<?= e($c['status']) ?>"><?= STATUS_LABELS[$c['status']] ?? e($c['status']) ?></span></td>
          <td>
            <div class="row-actions">
              <a href="edit_car.php?id=<?= $c['car_id'] ?>" class="btn btn-ghost btn-sm">✏️ แก้ไข</a>
              <button type="button" class="btn btn-danger btn-sm"
                onclick="confirmDelete(<?= (int)$c['car_id'] ?>, <?= e(json_encode($c['brand_name'] . ' ' . $c['model_name'], JSON_UNESCAPED_UNICODE)) ?>)">🗑 ลบ</button>
            </div>
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

<!-- Modal ยืนยันการลบ (ใช้กับ confirmDelete() ใน main.js) -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal-box">
    <div class="modal-title">🗑 ยืนยันการลบรถ</div>
    <div class="modal-body">
      ต้องการลบ <strong id="deleteCarName" class="text-accent"></strong> ใช่หรือไม่?<br>
      <span style="font-size:.85rem;">ข้อมูลการจองทดลองขับและรายการโปรดของรถคันนี้จะถูกลบไปด้วย และไม่สามารถกู้คืนได้</span>
    </div>
    <form method="POST" id="deleteForm" action="delete_car.php">
      <?= csrfField() ?>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('deleteModal')">ยกเลิก</button>
        <button type="submit" class="btn btn-danger">ลบเลย</button>
      </div>
    </form>
  </div>
</div>

<?php require 'includes/admin_footer.php'; ?>