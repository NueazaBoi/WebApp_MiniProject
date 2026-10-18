<?php
// ============================================================
//  CarVerse 3D | Register Page
//  register.php
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (isLoggedIn()) { header('Location: index.php'); exit; }

$errors  = [];
$success = false;
$data    = ['username'=>'','email'=>'','full_name'=>'','phone'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['username']  = trim($_POST['username']  ?? '');
    $data['email']     = trim($_POST['email']     ?? '');
    $data['full_name'] = trim($_POST['full_name'] ?? '');
    $data['phone']     = trim($_POST['phone']     ?? '');
    $password          = $_POST['password']          ?? '';
    $confirm           = $_POST['confirm_password']  ?? '';

    // Validation
    if (strlen($data['username']) < 3)
        $errors['username'] = 'Username ต้องมีอย่างน้อย 3 ตัวอักษร';
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username']))
        $errors['username'] = 'Username ใช้ได้เฉพาะ a-z, 0-9, _';

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'รูปแบบอีเมลไม่ถูกต้อง';

    if (empty($data['full_name']))
        $errors['full_name'] = 'กรุณากรอกชื่อ-นามสกุล';

    if (strlen($password) < 8)
        $errors['password'] = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร';

    if ($password !== $confirm)
        $errors['confirm'] = 'รหัสผ่านไม่ตรงกัน';

    // Check unique
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $check->execute([$data['username'], $data['email']]);
        if ($check->fetch()) {
            $errors['username'] = 'Username หรือ Email นี้ถูกใช้งานแล้ว';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, full_name, phone, role)
            VALUES (?, ?, ?, ?, ?, 'user')
        ");
        $stmt->execute([$data['username'], $data['email'], $hash, $data['full_name'], $data['phone']]);
        header('Location: login.php?registered=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>สมัครสมาชิก | CarVerse 3D</title>
  <meta name="description" content="สมัครสมาชิก CarVerse 3D ฟรี เพื่อเข้าถึงรถยนต์พรีเมียม">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/auth.css">
  <link rel="stylesheet" href="assets/css/particles.css">
  <script>!function(d){if(matchMedia("(prefers-reduced-motion: reduce)").matches)return;d.classList.add("pfx-on");setTimeout(function(){window.__pfx||d.classList.remove("pfx-on")},3500)}(document.documentElement)</script>
  <script src="assets/js/particles.js" defer></script>
</head>
<body class="auth-body">

<canvas id="particles" class="particles-canvas"></canvas>
<a href="index.php" class="auth-back">← กลับหน้าหลัก</a>

<div class="auth-container">

  <!-- Left Visual -->
  <div class="auth-visual">
    <canvas id="auth-canvas"></canvas>
    <div class="auth-visual-content">
      <div class="auth-logo">CAR<span>VERSE</span></div>
      <div class="auth-logo-sub">JOIN THE PREMIUM CLUB</div>
      <div class="auth-features">
        <div class="auth-feature">✦ บันทึกรถในฝัน Wishlist</div>
        <div class="auth-feature">✦ จองทดลองขับออนไลน์</div>
        <div class="auth-feature">✦ รับโปรโมชั่นพิเศษก่อนใคร</div>
        <div class="auth-feature">✦ ชมรถ 3D แบบเต็มรูปแบบ</div>
      </div>
    </div>
  </div>

  <!-- Right Form -->
  <div class="auth-form-panel">
    <div class="auth-form-inner">

      <div class="auth-heading">
        <h1>สมัครสมาชิก</h1>
        <p>เริ่มต้นประสบการณ์รถยนต์พรีเมียมของคุณ</p>
      </div>

      <?php if ($errors): ?>
      <div class="alert alert-danger">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        กรุณาตรวจสอบข้อมูลที่กรอก
      </div>
      <?php endif; ?>

      <form method="POST" action="register.php" id="registerForm" novalidate>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="full_name">ชื่อ-นามสกุล *</label>
            <div class="input-wrap">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <input type="text" id="full_name" name="full_name" class="form-control input-with-icon <?= isset($errors['full_name']) ? 'error' : '' ?>"
                placeholder="ชื่อ นามสกุล" value="<?= e($data['full_name']) ?>" required>
            </div>
            <?php if (isset($errors['full_name'])): ?>
            <div class="field-error"><?= e($errors['full_name']) ?></div>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label class="form-label" for="phone">เบอร์โทรศัพท์</label>
            <div class="input-wrap">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.92 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.82 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              <input type="tel" id="phone" name="phone" class="form-control input-with-icon"
                placeholder="0812345678" value="<?= e($data['phone']) ?>">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="username">Username *</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input type="text" id="username" name="username" class="form-control input-with-icon <?= isset($errors['username']) ? 'error' : '' ?>"
              placeholder="username (a-z, 0-9, _)" value="<?= e($data['username']) ?>" required autocomplete="username">
          </div>
          <?php if (isset($errors['username'])): ?>
          <div class="field-error"><?= e($errors['username']) ?></div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="email">อีเมล *</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <input type="email" id="email" name="email" class="form-control input-with-icon <?= isset($errors['email']) ? 'error' : '' ?>"
              placeholder="you@example.com" value="<?= e($data['email']) ?>" required autocomplete="email">
          </div>
          <?php if (isset($errors['email'])): ?>
          <div class="field-error"><?= e($errors['email']) ?></div>
          <?php endif; ?>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="password">รหัสผ่าน *</label>
            <div class="input-wrap">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input type="password" id="password" name="password" class="form-control input-with-icon <?= isset($errors['password']) ? 'error' : '' ?>"
                placeholder="อย่างน้อย 8 ตัว" required autocomplete="new-password" oninput="checkStrength(this.value)">
            </div>
            <div class="strength-bar">
              <div class="strength-track"><div class="strength-fill" id="strengthFill"></div></div>
              <div class="strength-label" id="strengthLabel"></div>
            </div>
            <?php if (isset($errors['password'])): ?>
            <div class="field-error"><?= e($errors['password']) ?></div>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label class="form-label" for="confirm_password">ยืนยันรหัสผ่าน *</label>
            <div class="input-wrap">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              <input type="password" id="confirm_password" name="confirm_password" class="form-control input-with-icon <?= isset($errors['confirm']) ? 'error' : '' ?>"
                placeholder="••••••••" required autocomplete="new-password">
            </div>
            <?php if (isset($errors['confirm'])): ?>
            <div class="field-error"><?= e($errors['confirm']) ?></div>
            <?php endif; ?>
          </div>
        </div>

        <div style="margin-bottom:24px;">
          <label class="checkbox-wrap">
            <input type="checkbox" name="agree" required id="agree">
            <span class="checkmark"></span>
            <span style="font-size:0.875rem;color:var(--clr-muted);">ฉันยอมรับ <a href="#" style="color:var(--clr-accent);">เงื่อนไขการใช้งาน</a></span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
          สมัครสมาชิก
        </button>

        <div class="auth-divider"><span>มีบัญชีแล้ว?</span></div>
        <div class="auth-switch">
          <a href="login.php" class="auth-switch-link">← เข้าสู่ระบบ</a>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- Field error style -->
<style>
  .field-error { font-size:0.8rem; color:var(--clr-danger); margin-top:5px; }
  .form-control.error { border-color:var(--clr-danger); }
  .form-control.error:focus { box-shadow:0 0 0 3px rgba(231,76,60,0.15); }
</style>

<script src="assets/js/main.js"></script>
<script>
  function checkStrength(pass) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    let score = 0;
    if (pass.length >= 8)  score++;
    if (/[A-Z]/.test(pass)) score++;
    if (/[0-9]/.test(pass)) score++;
    if (/[^a-zA-Z0-9]/.test(pass)) score++;
    const levels = [
      { pct: '0%',   color: '',             text: '' },
      { pct: '25%',  color: '#e74c3c',      text: '😟 อ่อนมาก' },
      { pct: '50%',  color: '#f39c12',      text: '😐 พอใช้' },
      { pct: '75%',  color: '#27ae60',      text: '😊 ดี' },
      { pct: '100%', color: 'var(--clr-accent)', text: '🔥 แข็งแกร่ง' },
    ];
    const lvl = levels[score] || levels[0];
    fill.style.width = lvl.pct;
    fill.style.background = lvl.color;
    label.textContent = lvl.text;
  }
</script>
</body>
</html>