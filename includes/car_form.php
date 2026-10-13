<?php
// ============================================================
//  CarVerse 3D | ฟอร์มรถ (ใช้ร่วม add_car.php / edit_car.php)
//  ต้องมีตัวแปร: $car, $errors, $brands, $types, $formAction, $submitLabel
// ============================================================
$v   = fn(string $k, $def = '') => e((string)($car[$k] ?? $def));
$err = fn(string $k) => isset($errors[$k]) ? '<div class="field-error">' . e($errors[$k]) . '</div>' : '';
$cls = fn(string $k) => isset($errors[$k]) ? ' error' : '';
?>
<form method="POST" action="<?= e($formAction) ?>" enctype="multipart/form-data" class="panel" novalidate>
  <?= csrfField() ?>

  <h2 class="panel-title">ข้อมูลหลัก</h2>
  <div class="form-grid">
    <div class="form-group span-2">
      <label class="form-label" for="model_name">ชื่อรุ่น *</label>
      <input type="text" id="model_name" name="model_name" class="form-control<?= $cls('model_name') ?>" value="<?= $v('model_name') ?>" placeholder="เช่น M4 Competition" maxlength="150" required>
      <?= $err('model_name') ?>
    </div>
    <div class="form-group">
      <label class="form-label" for="year">ปี *</label>
      <input type="number" id="year" name="year" class="form-control<?= $cls('year') ?>" value="<?= $v('year', date('Y')) ?>" min="1990" max="<?= date('Y') + 1 ?>" required>
      <?= $err('year') ?>
    </div>

    <div class="form-group">
      <label class="form-label" for="brand_id">แบรนด์ *</label>
      <select id="brand_id" name="brand_id" class="form-control<?= $cls('brand_id') ?>" required>
        <option value="">-- เลือกแบรนด์ --</option>
        <?php foreach ($brands as $b): ?>
          <option value="<?= $b['brand_id'] ?>" <?= (int)($car['brand_id'] ?? 0) === (int)$b['brand_id'] ? 'selected' : '' ?>><?= e($b['brand_name']) ?></option>
        <?php endforeach; ?>
      </select>
      <?= $err('brand_id') ?>
    </div>
    <div class="form-group">
      <label class="form-label" for="type_id">ประเภทรถ *</label>
      <select id="type_id" name="type_id" class="form-control<?= $cls('type_id') ?>" required>
        <option value="">-- เลือกประเภท --</option>
        <?php foreach ($types as $t): ?>
          <option value="<?= $t['type_id'] ?>" <?= (int)($car['type_id'] ?? 0) === (int)$t['type_id'] ? 'selected' : '' ?>><?= e($t['type_name']) ?></option>
        <?php endforeach; ?>
      </select>
      <?= $err('type_id') ?>
    </div>
    <div class="form-group">
      <label class="form-label" for="color">สี</label>
      <input type="text" id="color" name="color" class="form-control" value="<?= $v('color') ?>" placeholder="เช่น Black">
    </div>

    <div class="form-group">
      <label class="form-label" for="price">ราคา (บาท) *</label>
      <input type="number" id="price" name="price" class="form-control<?= $cls('price') ?>" value="<?= $v('price') ?>" min="0" step="1000" required>
      <?= $err('price') ?>
    </div>
    <div class="form-group">
      <label class="form-label" for="stock_qty">จำนวนในสต็อก</label>
      <input type="number" id="stock_qty" name="stock_qty" class="form-control" value="<?= $v('stock_qty', 1) ?>" min="0">
    </div>
    <div class="form-group">
      <label class="form-label" for="status">สถานะ</label>
      <select id="status" name="status" class="form-control">
        <?php foreach (STATUS_LABELS as $k => $l): ?>
          <option value="<?= $k ?>" <?= ($car['status'] ?? 'available') === $k ? 'selected' : '' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <h2 class="panel-title">สเปครถ</h2>
  <div class="form-grid">
    <div class="form-group">
      <label class="form-label" for="fuel_type">เชื้อเพลิง</label>
      <select id="fuel_type" name="fuel_type" class="form-control">
        <?php foreach (FUEL_LABELS as $k => $l): ?>
          <option value="<?= $k ?>" <?= ($car['fuel_type'] ?? 'gasoline') === $k ? 'selected' : '' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label" for="transmission">เกียร์</label>
      <select id="transmission" name="transmission" class="form-control">
        <?php foreach (TRANS_LABELS as $k => $l): ?>
          <option value="<?= $k ?>" <?= ($car['transmission'] ?? 'automatic') === $k ? 'selected' : '' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label" for="seats">จำนวนที่นั่ง</label>
      <input type="number" id="seats" name="seats" class="form-control<?= $cls('seats') ?>" value="<?= $v('seats', 5) ?>" min="1" max="20">
      <?= $err('seats') ?>
    </div>
    <div class="form-group">
      <label class="form-label" for="engine_cc">ขนาดเครื่อง (cc)</label>
      <input type="number" id="engine_cc" name="engine_cc" class="form-control" value="<?= $v('engine_cc') ?>" min="0">
    </div>
    <div class="form-group">
      <label class="form-label" for="horsepower">แรงม้า (HP)</label>
      <input type="number" id="horsepower" name="horsepower" class="form-control" value="<?= $v('horsepower') ?>" min="0">
    </div>
    <div class="form-group">
      <label class="form-label" for="mileage">เลขไมล์ (กม.)</label>
      <input type="number" id="mileage" name="mileage" class="form-control" value="<?= $v('mileage', 0) ?>" min="0">
    </div>
  </div>

  <h2 class="panel-title">รูปภาพ &amp; โมเดล 3D</h2>
  <div class="form-grid">
    <div class="form-group span-2">
      <label class="form-label" for="image">รูปรถ (JPG / PNG / WEBP ไม่เกิน 5 MB)</label>
      <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/webp" data-preview="imgPreview">
    </div>
    <div class="form-group">
      <label class="form-label">ตัวอย่างรูป</label>
      <img id="imgPreview" class="img-preview" alt="preview"
           src="<?= !empty($car['image_url']) ? e($car['image_url']) : '' ?>"
           style="display:<?= !empty($car['image_url']) ? 'block' : 'none' ?>;">
    </div>
    <div class="form-group span-3">
      <label class="form-label" for="model_3d_url">ไฟล์โมเดล 3D (.glb) — path หรือ URL (ไม่บังคับ)</label>
      <input type="text" id="model_3d_url" name="model_3d_url" class="form-control" value="<?= $v('model_3d_url') ?>" placeholder="assets/models/mclaren_p1.glb">
    </div>
    <div class="form-group span-3">
      <label class="form-label" for="description">รายละเอียด</label>
      <textarea id="description" name="description" class="form-control" rows="4" placeholder="จุดเด่นของรถคันนี้..."><?= $v('description') ?></textarea>
    </div>
  </div>

  <div class="form-actions">
    <a href="edit_car.php" class="btn btn-ghost">ยกเลิก</a>
    <button type="submit" class="btn btn-primary btn-lg"><?= e($submitLabel) ?></button>
  </div>
</form>
