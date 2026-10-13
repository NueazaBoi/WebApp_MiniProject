<?php
// ============================================================
//  CarVerse 3D | ขายรถให้ร้าน (Sell)
//  sell_car.php  — ส่งคำขอขาย + ดูผลการประเมิน + ตอบรับ/ปฏิเสธราคาที่ร้านเสนอ
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/trade_helpers.php';
requireLogin('login.php?redirect=sell_car.php');

$uid    = (int)$_SESSION['user_id'];
$brands = $pdo->query("SELECT * FROM brands ORDER BY brand_name")->fetchAll();
$types  = $pdo->query("SELECT * FROM car_types ORDER BY type_name")->fetchAll();
$msg = ''; $errors = [];
$form = ['brand_id'=>0,'type_id'=>0,'model_name'=>'','year'=>date('Y'),'color'=>'','mileage'=>0,
         'fuel_type'=>'gasoline','transmission'=>'automatic','expected_price'=>'','contact_phone'=>'','description'=>''];
$FUELS = ['gasoline'=>'เบนซิน','diesel'=>'ดีเซล','electric'=>'ไฟฟ้า','hybrid'=>'ไฮบริด'];
$TRANS = ['manual'=>'เกียร์ธรรมดา','automatic'=>'อัตโนมัติ','cvt'=>'CVT'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'submit';

    if (!verifyCsrf()) {
        $errors['_'] = 'Session หมดอายุ กรุณาลองใหม่';

    // ผู้ขายตอบรับ/ปฏิเสธราคาที่ร้านเสนอ
    } elseif ($action === 'accept' || $action === 'decline') {
        $rid = (int)($_POST['request_id'] ?? 0);
        $new = $action === 'accept' ? 'accepted' : 'declined';
        $u = $pdo->prepare("UPDATE sell_requests SET status=? WHERE request_id=? AND user_id=? AND status='offered'");
        $u->execute([$new, $rid, $uid]);
        $msg = $u->rowCount()
            ? ($new === 'accepted' ? 'ยืนยันขายรถแล้ว — ทีมงานจะติดต่อนัดรับรถและชำระเงิน' : 'ปฏิเสธข้อเสนอแล้ว')
            : '';
        if (!$u->rowCount()) $errors['_'] = 'ไม่สามารถดำเนินการกับรายการนี้ได้';

    // ผู้ขายยกเลิกคำขอที่ยังรอตรวจสอบ
    } elseif ($action === 'withdraw') {
        $rid = (int)($_POST['request_id'] ?? 0);
        $r = $pdo->prepare("SELECT image_url FROM sell_requests WHERE request_id=? AND user_id=? AND status='pending'");
        $r->execute([$rid, $uid]);
        if ($row = $r->fetch()) {
            $pdo->prepare("DELETE FROM sell_requests WHERE request_id=?")->execute([$rid]);
            deleteUploadedFile($row['image_url']);
            $msg = 'ยกเลิกคำขอขายเรียบร้อยแล้ว';
        } else {
            $errors['_'] = 'ไม่สามารถยกเลิกรายการนี้ได้';
        }

    // ส่งคำขอขายใหม่
    } else {
        $form['brand_id']       = (int)($_POST['brand_id'] ?? 0);
        $form['type_id']        = (int)($_POST['type_id'] ?? 0);
        $form['model_name']     = trim($_POST['model_name'] ?? '');
        $form['year']           = (int)($_POST['year'] ?? 0);
        $form['color']          = trim($_POST['color'] ?? '');
        $form['mileage']        = (int)($_POST['mileage'] ?? 0);
        $form['fuel_type']      = $_POST['fuel_type'] ?? '';
        $form['transmission']   = $_POST['transmission'] ?? '';
        $form['expected_price'] = trim($_POST['expected_price'] ?? '');
        $form['contact_phone']  = trim($_POST['contact_phone'] ?? '');
        $form['description']    = trim($_POST['description'] ?? '');

        if (!in_array($form['brand_id'], array_map('intval', array_column($brands, 'brand_id')), true)) $errors['brand_id'] = 'เลือกแบรนด์';
        if (!in_array($form['type_id'],  array_map('intval', array_column($types,  'type_id')),  true)) $errors['type_id']  = 'เลือกประเภทรถ';
        if ($form['model_name'] === '' || mb_strlen($form['model_name']) > 150) $errors['model_name'] = 'กรอกชื่อรุ่น (ไม่เกิน 150 ตัวอักษร)';
        if ($form['year'] < 1980 || $form['year'] > (int)date('Y') + 1) $errors['year'] = 'ปีไม่ถูกต้อง';
        if ($form['mileage'] < 0 || $form['mileage'] > 2000000) $errors['mileage'] = 'เลขไมล์ไม่ถูกต้อง';
        if (!isset($FUELS[$form['fuel_type']])) $errors['fuel_type'] = 'เลือกเชื้อเพลิง';
        if (!isset($TRANS[$form['transmission']])) $errors['transmission'] = 'เลือกเกียร์';
        if (!is_numeric($form['expected_price']) || (float)$form['expected_price'] <= 0) $errors['expected_price'] = 'กรอกราคาที่ต้องการ';
        if (!preg_match('/^[0-9+\-\s]{9,20}$/', $form['contact_phone'])) $errors['contact_phone'] = 'กรอกเบอร์โทรให้ถูกต้อง';

        $img = null;
        if (!$errors) {
            try { $img = uploadCarImage($_FILES['image'] ?? []); }
            catch (Exception $ex) { $errors['_'] = $ex->getMessage(); }
        }
        if (!$errors) {
            $pdo->prepare("
                INSERT INTO sell_requests (user_id,brand_id,type_id,model_name,year,color,mileage,fuel_type,transmission,
                                           expected_price,description,image_url,contact_phone)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
            ")->execute([
                $uid, $form['brand_id'], $form['type_id'], $form['model_name'], $form['year'], $form['color'] ?: null,
                $form['mileage'], $form['fuel_type'], $form['transmission'], (float)$form['expected_price'],
                $form['description'] ?: null, $img, $form['contact_phone'],
            ]);
            $msg = 'ส่งคำขอขายรถเรียบร้อย — ทีมงานจะประเมินราคาและแจ้งกลับในหน้านี้';
            $form = array_merge($form, ['model_name'=>'','color'=>'','mileage'=>0,'expected_price'=>'','description'=>'']);
        }
    }
}

$mine = $pdo->prepare("
    SELECT s.*, b.brand_name, t.type_name
    FROM sell_requests s
    JOIN brands b    ON s.brand_id = b.brand_id
    JOIN car_types t ON s.type_id  = t.type_id
    WHERE s.user_id = ?
    ORDER BY s.created_at DESC
");
$mine->execute([$uid]);
$requests = $mine->fetchAll();

$pageTitle   = 'ขายรถให้ CarVerse';
$pageHeading = 'SELL YOUR CAR';
$pageDesc    = 'กรอกข้อมูลรถของคุณ ทีมงานจะประเมินราคาและเสนอราคารับซื้อ';
$activeNav   = 'sell';
require 'includes/user_header.php';
$fe = fn($k) => isset($errors[$k]) ? '<div class="t-err">' . e($errors[$k]) . '</div>' : '';
?>

<?php if ($msg): ?><div class="alert alert-success" data-autohide="6000" style="margin-bottom:20px;">✅ <?= e($msg) ?></div><?php endif; ?>
<?php if (isset($errors['_'])): ?><div class="alert alert-danger" style="margin-bottom:20px;"><?= e($errors['_']) ?></div>
<?php elseif ($errors): ?><div class="alert alert-danger" style="margin-bottom:20px;">กรุณาตรวจสอบข้อมูลที่กรอกให้ถูกต้อง</div><?php endif; ?>

<form method="POST" action="sell_car.php" enctype="multipart/form-data" class="t-card">
  <?= csrfField() ?>
  <input type="hidden" name="action" value="submit">
  <h2>📝 ข้อมูลรถที่ต้องการขาย</h2>
  <div class="t-grid">
    <div><label class="t-label">แบรนด์ *</label>
      <select name="brand_id" class="form-control">
        <option value="0">— เลือก —</option>
        <?php foreach ($brands as $b): ?><option value="<?= $b['brand_id'] ?>" <?= $form['brand_id'] === (int)$b['brand_id'] ? 'selected' : '' ?>><?= e($b['brand_name']) ?></option><?php endforeach; ?>
      </select><?= $fe('brand_id') ?></div>
    <div><label class="t-label">ประเภทรถ *</label>
      <select name="type_id" class="form-control">
        <option value="0">— เลือก —</option>
        <?php foreach ($types as $t): ?><option value="<?= $t['type_id'] ?>" <?= $form['type_id'] === (int)$t['type_id'] ? 'selected' : '' ?>><?= e($t['type_name']) ?></option><?php endforeach; ?>
      </select><?= $fe('type_id') ?></div>
    <div><label class="t-label">ชื่อรุ่น *</label>
      <input type="text" name="model_name" class="form-control" value="<?= e($form['model_name']) ?>" placeholder="เช่น Civic e:HEV RS"><?= $fe('model_name') ?></div>
    <div><label class="t-label">ปีรถ *</label>
      <input type="number" name="year" class="form-control" value="<?= (int)$form['year'] ?>"><?= $fe('year') ?></div>
    <div><label class="t-label">สี</label>
      <input type="text" name="color" class="form-control" value="<?= e($form['color']) ?>"></div>
    <div><label class="t-label">เลขไมล์ (กม.)</label>
      <input type="number" name="mileage" class="form-control" min="0" value="<?= (int)$form['mileage'] ?>"><?= $fe('mileage') ?></div>
    <div><label class="t-label">เชื้อเพลิง</label>
      <select name="fuel_type" class="form-control"><?php foreach ($FUELS as $k => $l): ?><option value="<?= $k ?>" <?= $form['fuel_type'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
    <div><label class="t-label">เกียร์</label>
      <select name="transmission" class="form-control"><?php foreach ($TRANS as $k => $l): ?><option value="<?= $k ?>" <?= $form['transmission'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
    <div><label class="t-label">ราคาที่ต้องการ (บาท) *</label>
      <input type="number" step="1000" min="1" name="expected_price" class="form-control" value="<?= e((string)$form['expected_price']) ?>"><?= $fe('expected_price') ?></div>
    <div><label class="t-label">เบอร์โทรติดต่อ *</label>
      <input type="tel" name="contact_phone" class="form-control" value="<?= e($form['contact_phone']) ?>"><?= $fe('contact_phone') ?></div>
    <div class="full"><label class="t-label">รายละเอียด / สภาพรถ / ประวัติอุบัติเหตุ</label>
      <textarea name="description" rows="3" class="form-control"><?= e($form['description']) ?></textarea></div>
    <div class="full"><label class="t-label">รูปรถ (JPG / PNG / WebP)</label>
      <input type="file" name="image" accept="image/*" class="form-control"></div>
  </div>
  <button type="submit" class="btn btn-primary btn-lg" style="margin-top:18px;">📤 ส่งคำขอขายรถ</button>
</form>

<h2 style="font-family:'Barlow Condensed',sans-serif;font-size:1.6rem;color:#e9f7ff;margin:34px 0 14px;">คำขอขายของฉัน (<?= count($requests) ?>)</h2>

<?php if (!$requests): ?><div class="t-empty">ยังไม่มีคำขอขายรถ</div><?php endif; ?>
<?php foreach ($requests as $r): ?>
  <div class="t-item">
    <div class="t-thumb"><?php if ($r['image_url']): ?><img src="<?= e($r['image_url']) ?>" alt=""><?php else: ?>🚗<?php endif; ?></div>
    <div>
      <div style="font-size:.72rem;color:#1fc8ff;font-weight:700;text-transform:uppercase;letter-spacing:.1em"><?= e($r['brand_name']) ?> · <?= e($r['type_name']) ?></div>
      <div style="font-size:1.05rem;font-weight:700;color:#e9f7ff;"><?= e($r['model_name']) ?> (<?= e((string)$r['year']) ?>)</div>
      <div class="t-meta">
        <span>📋 #<?= (int)$r['request_id'] ?></span>
        <span>📅 <?= date('d/m/Y', strtotime($r['created_at'])) ?></span>
        <span>🛣 <?= number_format((int)$r['mileage']) ?> กม.</span>
        <span>ราคาที่ต้องการ: <?= formatPrice((float)$r['expected_price']) ?></span>
      </div>
      <?php if ($r['offer_price'] !== null): ?>
        <div style="margin-top:8px;color:#f39c12;font-weight:700;">💰 ราคาที่ร้านเสนอ: <?= formatPrice((float)$r['offer_price']) ?></div>
      <?php endif; ?>
      <?php if ($r['admin_note']): ?><div class="t-meta">💬 ทีมงาน: <?= e($r['admin_note']) ?></div><?php endif; ?>
    </div>
    <div class="t-side">
      <?= tradeBadge($r['status'], SELL_LABELS) ?>
      <?php if ($r['status'] === 'offered'): ?>
        <form method="POST" onsubmit="return confirm('ยืนยันขายรถในราคานี้?')"><?= csrfField() ?>
          <input type="hidden" name="action" value="accept"><input type="hidden" name="request_id" value="<?= (int)$r['request_id'] ?>">
          <button class="t-btn-ok">✔ ตกลงขาย</button></form>
        <form method="POST" onsubmit="return confirm('ปฏิเสธข้อเสนอนี้?')"><?= csrfField() ?>
          <input type="hidden" name="action" value="decline"><input type="hidden" name="request_id" value="<?= (int)$r['request_id'] ?>">
          <button class="t-btn-danger">✕ ปฏิเสธ</button></form>
      <?php elseif ($r['status'] === 'pending'): ?>
        <form method="POST" onsubmit="return confirm('ยกเลิกคำขอนี้?')"><?= csrfField() ?>
          <input type="hidden" name="action" value="withdraw"><input type="hidden" name="request_id" value="<?= (int)$r['request_id'] ?>">
          <button class="t-btn-danger">ยกเลิกคำขอ</button></form>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>

<?php require 'includes/user_footer.php'; ?>
