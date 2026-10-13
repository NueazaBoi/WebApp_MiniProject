<?php
// ============================================================
//  CarVerse 3D | แอดมิน: จัดการคำขอขายรถ (รับซื้อ)
//  admin_sell.php
//  flow: pending → (เสนอราคา) offered → (ผู้ขายตกลง) accepted → (รับซื้อ+ลงสต็อก) purchased
//        หรือ pending → rejected (ไม่รับซื้อ)
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/trade_helpers.php';
requireLogin('login.php?redirect=admin_sell.php');
requireAdmin();

$back = fn() => header('Location: admin_sell.php?' . http_build_query(array_diff_key($_GET, ['page' => 1])));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $rid    = (int)($_POST['request_id'] ?? 0);
    $note   = trim($_POST['admin_note'] ?? '');

    $st = $pdo->prepare("SELECT * FROM sell_requests WHERE request_id=?");
    $st->execute([$rid]);
    $req = $st->fetch();

    if (!verifyCsrf() || !$req) {
        setFlash('error', 'คำขอไม่ถูกต้อง');

    // เสนอราคารับซื้อ (ใช้ซ้ำได้ตอนสถานะ pending / offered / declined)
    } elseif ($action === 'offer' && in_array($req['status'], ['pending','offered','declined'], true)) {
        $price = $_POST['offer_price'] ?? '';
        if (!is_numeric($price) || (float)$price <= 0) {
            setFlash('error', 'กรุณากรอกราคาเสนอให้ถูกต้อง');
        } else {
            $pdo->prepare("UPDATE sell_requests SET status='offered', offer_price=?, admin_note=?, reviewed_by=? WHERE request_id=?")
                ->execute([(float)$price, $note ?: null, $_SESSION['user_id'], $rid]);
            setFlash('success', '💰 เสนอราคารับซื้อรายการ #' . $rid . ' แล้ว');
        }

    // ไม่รับซื้อ
    } elseif ($action === 'reject' && in_array($req['status'], ['pending','offered','declined'], true)) {
        $pdo->prepare("UPDATE sell_requests SET status='rejected', admin_note=?, reviewed_by=? WHERE request_id=?")
            ->execute([$note ?: null, $_SESSION['user_id'], $rid]);
        setFlash('success', 'ปฏิเสธรายการ #' . $rid . ' แล้ว');

    // รับซื้อแล้ว → เพิ่มเข้าสต็อก
    } elseif ($action === 'purchase' && $req['status'] === 'accepted') {
        $resale = $_POST['resale_price'] ?? '';
        if (!is_numeric($resale) || (float)$resale <= 0) {
            setFlash('error', 'กรุณากรอกราคาขายต่อ (เพื่อลงสต็อก)');
        } else {
            try {
                $pdo->beginTransaction();
                $pdo->prepare("
                    INSERT INTO cars (brand_id,type_id,model_name,year,color,price,mileage,fuel_type,transmission,
                                      description,image_url,stock_qty,status,created_by)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,1,'available',?)
                ")->execute([
                    $req['brand_id'], $req['type_id'], $req['model_name'], $req['year'], $req['color'],
                    (float)$resale, $req['mileage'], $req['fuel_type'], $req['transmission'],
                    $req['description'], $req['image_url'], $_SESSION['user_id'],
                ]);
                $carId = (int)$pdo->lastInsertId();
                $pdo->prepare("UPDATE sell_requests SET status='purchased', car_id=?, reviewed_by=? WHERE request_id=?")
                    ->execute([$carId, $_SESSION['user_id'], $rid]);
                $pdo->commit();
                setFlash('success', '🚗 รับซื้อรายการ #' . $rid . ' และเพิ่มเข้าสต็อกเป็นรถ #' . $carId . ' แล้ว');
            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                setFlash('error', 'เกิดข้อผิดพลาดในการเพิ่มรถเข้าสต็อก');
            }
        }
    } else {
        setFlash('error', 'ไม่สามารถทำรายการนี้ในสถานะปัจจุบันได้');
    }
    $back();
    exit;
}

/* ── รายการ ── */
$statusF = $_GET['status'] ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$where = ['1=1']; $params = [];
if (isset(SELL_LABELS[$statusF])) { $where[] = 's.status = ?'; $params[] = $statusF; }
$w = 'WHERE ' . implode(' AND ', $where);

$cnt = $pdo->prepare("SELECT COUNT(*) FROM sell_requests s $w"); $cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("
    SELECT s.*, u.full_name, b.brand_name, t.type_name
    FROM sell_requests s
    JOIN users u     ON s.user_id  = u.user_id
    JOIN brands b    ON s.brand_id = b.brand_id
    JOIN car_types t ON s.type_id  = t.type_id
    $w
    ORDER BY FIELD(s.status,'pending','accepted','offered','declined','rejected','purchased'), s.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$list = $stmt->fetchAll();

$counts = array_fill_keys(array_keys(SELL_LABELS), 0);
foreach ($pdo->query("SELECT status, COUNT(*) n FROM sell_requests GROUP BY status") as $r) $counts[$r['status']] = (int)$r['n'];

$pageTitle = 'รับซื้อรถ (คำขอขาย)';
$pageDesc  = "คำขอขายรถทั้งหมด $total รายการ — ประเมินราคา เสนอราคา และรับเข้าสต็อก";
$activeNav = 'sell';
require 'includes/admin_header.php';
?>

<div class="stats-grid">
  <?php foreach (SELL_LABELS as $k => $l): ?>
    <a href="?status=<?= $k ?>" class="stat-card" style="text-decoration:none;"><div class="stat-label"><?= e($l) ?></div><div class="stat-value"><?= $counts[$k] ?></div><div class="stat-sub">รายการ</div></a>
  <?php endforeach; ?>
</div>

<div class="toolbar">
  <form method="GET" class="toolbar-form">
    <select name="status" class="form-control" onchange="this.form.submit()">
      <option value="">ทุกสถานะ</option>
      <?php foreach (SELL_LABELS as $k => $l): ?><option value="<?= $k ?>" <?= $statusF === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
    </select>
    <a href="admin_sell.php" class="btn btn-ghost">ล้าง</a>
  </form>
</div>

<div class="panel table-panel">
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>#</th><th>รูป</th><th>รถ</th><th>ผู้ขาย</th><th>ราคาที่ต้องการ</th><th>ราคาเสนอ</th><th>สถานะ</th><th>ดำเนินการ</th></tr></thead>
      <tbody>
      <?php if (!$list): ?><tr><td colspan="8" class="empty-row">ไม่พบคำขอขายรถ</td></tr><?php endif; ?>
      <?php foreach ($list as $r): ?>
        <tr>
          <td class="text-muted"><?= (int)$r['request_id'] ?></td>
          <td><?php if ($r['image_url']): ?><img src="<?= e($r['image_url']) ?>" class="thumb" alt=""><?php else: ?><div class="thumb thumb-empty">🚗</div><?php endif; ?></td>
          <td><strong><?= e($r['brand_name'] . ' ' . $r['model_name']) ?></strong> (<?= e((string)$r['year']) ?>)<br>
              <span class="text-muted" style="font-size:.8rem;"><?= e($r['type_name']) ?> · <?= number_format((int)$r['mileage']) ?> กม. · <?= e((string)$r['color']) ?></span>
              <?php if ($r['description']): ?><br><span class="text-muted" style="font-size:.78rem;"><?= e(mb_strimwidth($r['description'], 0, 80, '…')) ?></span><?php endif; ?></td>
          <td><?= e($r['full_name']) ?><br><span class="text-muted" style="font-size:.8rem;"><?= e($r['contact_phone']) ?></span></td>
          <td style="white-space:nowrap;"><?= formatPrice((float)$r['expected_price']) ?></td>
          <td class="text-accent" style="white-space:nowrap;"><?= $r['offer_price'] !== null ? formatPrice((float)$r['offer_price']) : '—' ?></td>
          <td><?= tradeBadge($r['status'], SELL_LABELS) ?></td>
          <td style="min-width:260px;">
            <?php if (in_array($r['status'], ['pending','offered','declined'], true)): ?>
              <form method="POST" style="display:grid;gap:6px;">
                <?= csrfField() ?>
                <input type="hidden" name="request_id" value="<?= (int)$r['request_id'] ?>">
                <input type="number" name="offer_price" class="form-control" min="1" step="1000" placeholder="ราคาที่เสนอ (บาท)" value="<?= e((string)($r['offer_price'] ?? '')) ?>" style="padding:6px 10px;">
                <input type="text" name="admin_note" class="form-control" placeholder="หมายเหตุถึงผู้ขาย" value="<?= e((string)$r['admin_note']) ?>" style="padding:6px 10px;">
                <div class="row-actions">
                  <button class="btn btn-primary btn-sm" name="action" value="offer">💰 เสนอราคา</button>
                  <button class="btn btn-danger btn-sm" name="action" value="reject" onclick="return confirm('ไม่รับซื้อรถคันนี้?')">ไม่รับซื้อ</button>
                </div>
              </form>
            <?php elseif ($r['status'] === 'accepted'): ?>
              <form method="POST" style="display:grid;gap:6px;" onsubmit="return confirm('ยืนยันรับซื้อและเพิ่มเข้าสต็อก?')">
                <?= csrfField() ?>
                <input type="hidden" name="request_id" value="<?= (int)$r['request_id'] ?>">
                <input type="number" name="resale_price" class="form-control" min="1" step="1000" placeholder="ราคาขายต่อ (บาท)" style="padding:6px 10px;">
                <button class="btn btn-primary btn-sm" name="action" value="purchase">📥 รับซื้อ + ลงสต็อก</button>
              </form>
            <?php elseif ($r['status'] === 'purchased' && $r['car_id']): ?>
              <a href="edit_car.php?id=<?= (int)$r['car_id'] ?>" class="btn btn-ghost btn-sm">ดูรถในสต็อก #<?= (int)$r['car_id'] ?></a>
            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
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
