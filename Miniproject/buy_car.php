<?php
// ============================================================
//  CarVerse 3D | สั่งซื้อรถ (Buy)
//  buy_car.php?id=X
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/trade_helpers.php';

$id = (int)($_GET['id'] ?? 0);
requireLogin('login.php?redirect=' . urlencode('buy_car.php?id=' . $id));

$stmt = $pdo->prepare("
    SELECT c.*, b.brand_name, t.type_name
    FROM cars c
    JOIN brands b    ON c.brand_id = b.brand_id
    JOIN car_types t ON c.type_id  = t.type_id
    WHERE c.car_id = ?
");
$stmt->execute([$id]);
$car = $stmt->fetch();

if (!$car) {
    http_response_code(404);
}
$canBuy = $car && $car['status'] === 'available' && (int)$car['stock_qty'] > 0;

$errors = [];
$form   = ['quantity' => 1, 'payment_method' => 'transfer', 'contact_phone' => '', 'address' => '', 'notes' => ''];

if ($canBuy && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors['_'] = 'Session หมดอายุ กรุณาลองใหม่';
    } else {
        $form['quantity']       = (int)($_POST['quantity'] ?? 1);
        $form['payment_method'] = $_POST['payment_method'] ?? '';
        $form['contact_phone']  = trim($_POST['contact_phone'] ?? '');
        $form['address']        = trim($_POST['address'] ?? '');
        $form['notes']          = trim($_POST['notes'] ?? '');

        if ($form['quantity'] < 1 || $form['quantity'] > (int)$car['stock_qty'])
            $errors['quantity'] = 'จำนวนต้องอยู่ระหว่าง 1 – ' . (int)$car['stock_qty'];
        if (!isset(PAY_LABELS[$form['payment_method']]))
            $errors['payment_method'] = 'กรุณาเลือกวิธีชำระเงิน';
        if (!preg_match('/^[0-9+\-\s]{9,20}$/', $form['contact_phone']))
            $errors['contact_phone'] = 'กรุณากรอกเบอร์โทรให้ถูกต้อง';

        if (!$errors) {
            try {
                $pdo->beginTransaction();
                // ล็อกแถวรถกันซื้อซ้อนกัน (race condition)
                $lock = $pdo->prepare("SELECT stock_qty, status, price FROM cars WHERE car_id = ? FOR UPDATE");
                $lock->execute([$id]);
                $cur = $lock->fetch();

                if (!$cur || $cur['status'] !== 'available' || (int)$cur['stock_qty'] < $form['quantity']) {
                    $pdo->rollBack();
                    $errors['_'] = 'ขออภัย รถคันนี้มีไม่พอหรือถูกซื้อไปแล้ว';
                } else {
                    $unit  = (float)$cur['price'];          // ใช้ราคาจากฐานข้อมูลเสมอ
                    $total = $unit * $form['quantity'];
                    $pdo->prepare("
                        INSERT INTO orders (user_id, car_id, quantity, unit_price, total_price,
                                            payment_method, contact_phone, address, notes)
                        VALUES (?,?,?,?,?,?,?,?,?)
                    ")->execute([
                        $_SESSION['user_id'], $id, $form['quantity'], $unit, $total,
                        $form['payment_method'], $form['contact_phone'],
                        $form['address'] ?: null, $form['notes'] ?: null,
                    ]);
                    $orderId = (int)$pdo->lastInsertId();

                    $left = (int)$cur['stock_qty'] - $form['quantity'];
                    $pdo->prepare("UPDATE cars SET stock_qty = ?, status = ? WHERE car_id = ?")
                        ->execute([$left, $left === 0 ? 'sold' : 'available', $id]);
                    $pdo->commit();

                    header('Location: my_orders.php?ordered=' . $orderId);
                    exit;
                }
            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $errors['_'] = 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
            }
        }
    }
}

$pageTitle   = 'สั่งซื้อรถ';
$pageHeading = 'BUY A CAR';
$pageDesc    = $car ? ($car['brand_name'] . ' ' . $car['model_name']) : 'ไม่พบรถ';
$activeNav   = '';
require 'includes/user_header.php';
?>

<?php if (!$car): ?>
  <div class="t-empty"><div style="font-size:4rem">🚫</div><h3 style="color:#e9f7ff">ไม่พบรถที่ต้องการ</h3>
    <a href="search.php" class="btn btn-primary" style="margin-top:16px;display:inline-flex">← กลับไปค้นหารถ</a></div>

<?php elseif (!$canBuy): ?>
  <div class="t-empty"><div style="font-size:4rem">🔒</div>
    <h3 style="color:#e9f7ff">รถคันนี้ไม่พร้อมจำหน่าย</h3>
    <p>สถานะ: <?= e(STATUS_LABELS[$car['status']] ?? $car['status']) ?></p>
    <a href="car_detail.php?id=<?= $id ?>" class="btn btn-outline" style="margin-top:16px;display:inline-flex">← กลับหน้ารายละเอียด</a></div>

<?php else: ?>
  <?php if (isset($errors['_'])): ?><div class="alert alert-danger" style="margin-bottom:20px;"><?= e($errors['_']) ?></div><?php endif; ?>

  <form method="POST" action="buy_car.php?id=<?= $id ?>" style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;" id="buyForm">
    <?= csrfField() ?>

    <div>
      <div class="t-card">
        <h2>ข้อมูลการสั่งซื้อ</h2>
        <div class="t-grid">
          <div>
            <label class="t-label" for="quantity">จำนวน (สต็อก <?= (int)$car['stock_qty'] ?> คัน)</label>
            <input type="number" class="form-control" id="quantity" name="quantity" min="1" max="<?= (int)$car['stock_qty'] ?>" value="<?= (int)$form['quantity'] ?>" oninput="calcTotal()">
            <?php if (isset($errors['quantity'])): ?><div class="t-err"><?= e($errors['quantity']) ?></div><?php endif; ?>
          </div>
          <div>
            <label class="t-label" for="payment_method">วิธีชำระเงิน</label>
            <select class="form-control" id="payment_method" name="payment_method">
              <?php foreach (PAY_LABELS as $k => $l): ?>
                <option value="<?= $k ?>" <?= $form['payment_method'] === $k ? 'selected' : '' ?>><?= e($l) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($errors['payment_method'])): ?><div class="t-err"><?= e($errors['payment_method']) ?></div><?php endif; ?>
          </div>
          <div class="full">
            <label class="t-label" for="contact_phone">เบอร์โทรติดต่อ *</label>
            <input type="tel" class="form-control" id="contact_phone" name="contact_phone" placeholder="0812345678" value="<?= e($form['contact_phone']) ?>">
            <?php if (isset($errors['contact_phone'])): ?><div class="t-err"><?= e($errors['contact_phone']) ?></div><?php endif; ?>
          </div>
          <div class="full">
            <label class="t-label" for="address">ที่อยู่จัดส่ง / ออกใบเสร็จ (ถ้ารับที่โชว์รูมเว้นว่างได้)</label>
            <textarea class="form-control" id="address" name="address" rows="3"><?= e($form['address']) ?></textarea>
          </div>
          <div class="full">
            <label class="t-label" for="notes">หมายเหตุ</label>
            <textarea class="form-control" id="notes" name="notes" rows="2"><?= e($form['notes']) ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <aside class="t-card" style="position:sticky;top:90px;">
      <h2>สรุปรายการ</h2>
      <?php if ($car['image_url']): ?>
        <img src="<?= e($car['image_url']) ?>" alt="" style="width:100%;border-radius:12px;margin-bottom:14px;max-height:180px;object-fit:cover;">
      <?php endif; ?>
      <div style="font-size:.72rem;color:#1fc8ff;font-weight:700;text-transform:uppercase;letter-spacing:.1em"><?= e($car['brand_name']) ?> · <?= e($car['type_name']) ?></div>
      <div style="font-size:1.15rem;font-weight:700;color:#e9f7ff;margin:4px 0 12px"><?= e($car['model_name']) ?> (<?= e((string)$car['year']) ?>)</div>
      <div class="t-row"><span>ราคาต่อคัน</span><span><?= formatPrice((float)$car['price']) ?></span></div>
      <div class="t-row"><span>จำนวน</span><span id="sumQty"><?= (int)$form['quantity'] ?> คัน</span></div>
      <div class="t-row" style="align-items:center"><span>ยอดรวม</span><span class="t-total" id="sumTotal"><?= formatPrice((float)$car['price'] * (int)$form['quantity']) ?></span></div>
      <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:16px;" onclick="return confirm('ยืนยันการสั่งซื้อ?')">🛒 ยืนยันสั่งซื้อ</button>
      <a href="car_detail.php?id=<?= $id ?>" class="btn btn-ghost btn-full" style="margin-top:8px;display:flex;justify-content:center;">ยกเลิก</a>
    </aside>
  </form>

  <script>
    const UNIT = <?= json_encode((float)$car['price']) ?>, MAXQ = <?= (int)$car['stock_qty'] ?>;
    function calcTotal() {
      let q = Math.min(Math.max(parseInt(document.getElementById('quantity').value) || 1, 1), MAXQ);
      document.getElementById('sumQty').textContent = q + ' คัน';
      document.getElementById('sumTotal').textContent = '฿' + (UNIT * q).toLocaleString('th-TH');
    }
  </script>
  <style>@media(max-width:900px){#buyForm{grid-template-columns:1fr!important}}</style>
<?php endif; ?>

<?php require 'includes/user_footer.php'; ?>
