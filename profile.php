<?php
// ============================================================
//  CarVerse 3D | หน้าโปรไฟล์ผู้ใช้
//  profile.php
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
requireLogin('login.php?redirect=profile.php');

$uid     = $_SESSION['user_id'];
$errors  = [];
$success = '';

// ── ดึงข้อมูล user ──
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

// ── บันทึกข้อมูลส่วนตัว ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        if (!$fullName) {
            $errors[] = 'กรุณากรอกชื่อ-นามสกุล';
        } else {
            $pdo->prepare("UPDATE users SET full_name=?, phone=?, updated_at=NOW() WHERE user_id=?")
                ->execute([$fullName, $phone ?: null, $uid]);
            $_SESSION['full_name'] = $fullName;
            $success = 'อัปเดตข้อมูลส่วนตัวสำเร็จ';
            $user['full_name'] = $fullName;
            $user['phone']     = $phone;
        }
    }

    if ($action === 'change_password') {
        $old = $_POST['old_password']     ?? '';
        $new = $_POST['new_password']     ?? '';
        $con = $_POST['confirm_password'] ?? '';
        if (!password_verify($old, $user['password'])) {
            $errors[] = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
        } elseif (strlen($new) < 6) {
            $errors[] = 'รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร';
        } elseif ($new !== $con) {
            $errors[] = 'รหัสผ่านใหม่และยืนยันไม่ตรงกัน';
        } else {
            $pdo->prepare("UPDATE users SET password=?, updated_at=NOW() WHERE user_id=?")
                ->execute([password_hash($new, PASSWORD_BCRYPT), $uid]);
            $success = 'เปลี่ยนรหัสผ่านสำเร็จ';
        }
    }

    if ($action === 'cancel_booking') {
        $bid = (int)($_POST['booking_id'] ?? 0);
        $chk = $pdo->prepare("SELECT booking_id FROM test_drive_bookings WHERE booking_id=? AND user_id=? AND status='pending'");
        $chk->execute([$bid, $uid]);
        if ($chk->fetch()) {
            $pdo->prepare("UPDATE test_drive_bookings SET status='cancelled' WHERE booking_id=?")->execute([$bid]);
            $success = 'ยกเลิกการจองเรียบร้อยแล้ว';
        }
    }
}

// ── ดึงประวัติการจอง 10 รายการล่าสุด ──
$bStmt = $pdo->prepare("
    SELECT b.*, c.model_name, brd.brand_name
    FROM   test_drive_bookings b
    JOIN   cars c    ON b.car_id   = c.car_id
    JOIN   brands brd ON c.brand_id = brd.brand_id
    WHERE  b.user_id = ?
    ORDER  BY b.created_at DESC
    LIMIT  10
");
$bStmt->execute([$uid]);
$bookings = $bStmt->fetchAll();

// ── สถิติ ──
$cntAll = $pdo->prepare("SELECT COUNT(*) FROM test_drive_bookings WHERE user_id=?");
$cntAll->execute([$uid]); $totalBook = $cntAll->fetchColumn();
$cntConf = $pdo->prepare("SELECT COUNT(*) FROM test_drive_bookings WHERE user_id=? AND status='confirmed'");
$cntConf->execute([$uid]); $confirmedBook = $cntConf->fetchColumn();

$bookColors = [
    'pending'   => ['rgba(31,200,255,0.15)', '#1fc8ff', 'rgba(31,200,255,0.4)'],
    'confirmed' => ['rgba(39,174,96,0.15)',  '#2ecc71', 'rgba(39,174,96,0.4)'],
    'completed' => ['rgba(39,174,96,0.15)',  '#2ecc71', 'rgba(39,174,96,0.4)'],
    'cancelled' => ['rgba(231,76,60,0.15)',  '#e74c3c', 'rgba(231,76,60,0.4)'],
];

// ตั้งค่า Header ส่วนกลาง
$pageTitle   = 'โปรไฟล์ของฉัน';
$pageHeading = 'PROFILE';
$pageDesc    = 'จัดการข้อมูลส่วนตัวและดูประวัติการจองทดสอบขับ';
$activeNav   = 'profile';
require 'includes/user_header.php';
echo '<link rel="stylesheet" href="assets/css/particles.css"><script>!function(d){if(matchMedia("(prefers-reduced-motion: reduce)").matches)return;d.classList.add("pfx-on");setTimeout(function(){window.__pfx||d.classList.remove("pfx-on")},3500)}(document.documentElement)</script><script src="assets/js/particles.js" defer></script>';
?>

<style>
    .profile-grid{display:grid;grid-template-columns:290px 1fr;gap:32px;align-items:start;position:relative;z-index:1}
    .profile-card{background:linear-gradient(145deg,rgba(31,200,255,.07),rgba(11,134,214,.02));border:1px solid rgba(31,200,255,.2);border-radius:24px;padding:36px 24px;text-align:center;backdrop-filter:blur(24px);position:sticky;top:92px;box-shadow:0 4px 40px rgba(0,0,0,.4),inset 0 1px 0 rgba(31,200,255,.1)}
    .avatar-ring{width:96px;height:96px;border-radius:50%;margin:0 auto 18px;background:linear-gradient(135deg,#1fc8ff,#0b86d6);display:flex;align-items:center;justify-content:center;font-size:2.6rem;font-weight:800;box-shadow:0 0 40px rgba(31,200,255,.4);font-family:'Barlow Condensed',sans-serif;color:#001018}
    .profile-name{font-size:1.25rem;font-weight:700;color:#e9f7ff;margin-bottom:4px}
    .profile-email{font-size:.82rem;color:#8aa3b2;margin-bottom:8px}
    .profile-role{display:inline-block;padding:3px 14px;border-radius:20px;font-size:.75rem;font-weight:700;background:rgba(31,200,255,.14);border:1px solid rgba(31,200,255,.4);color:#1fc8ff}
    .profile-stat{display:flex;justify-content:center;gap:24px;margin-top:24px;padding-top:24px;border-top:1px solid rgba(31,200,255,.1)}
    .pstat-num{font-size:1.7rem;font-weight:800;color:#1fc8ff;font-family:'Barlow Condensed',sans-serif;line-height:1}
    .pstat-lbl{font-size:.7rem;color:#8aa3b2;margin-top:4px}
    .member-since{font-size:.78rem;color:#8aa3b2;margin-top:20px;padding-top:16px;border-top:1px solid rgba(31,200,255,.08)}

    .tab-bar{display:flex;gap:0;margin-bottom:28px;border-bottom:1px solid rgba(31,200,255,.15)}
    .tab-btn{padding:12px 22px;font-size:.88rem;font-weight:600;cursor:pointer;border:none;background:none;color:#8aa3b2;border-bottom:2px solid transparent;transition:all 200ms;margin-bottom:-1px;font-family:'Outfit',sans-serif}
    .tab-btn.active{color:#1fc8ff;border-bottom-color:#1fc8ff}
    .tab-btn:hover{color:#e9f7ff}
    .tab-panel{display:none}.tab-panel.active{display:block}
    .panel-box{background:linear-gradient(145deg,rgba(31,200,255,.05),rgba(11,134,214,.02));border:1px solid rgba(31,200,255,.14);border-radius:20px;padding:32px}
    .panel-title{font-size:1.05rem;font-weight:700;color:#e9f7ff;margin-bottom:24px;display:flex;align-items:center;gap:10px}
    .panel-title::before{content:'';display:block;width:3px;height:18px;background:#1fc8ff;border-radius:2px;box-shadow:0 0 8px rgba(31,200,255,.6);flex-shrink:0}

    .booking-row{display:flex;align-items:center;gap:14px;padding:14px 18px;border:1px solid rgba(31,200,255,.1);border-radius:14px;margin-bottom:10px;background:rgba(31,200,255,.025);transition:border-color 200ms}
    .booking-row:hover{border-color:rgba(31,200,255,.3)}
    .booking-car{flex:1;min-width:0}
    .booking-car-name{font-weight:600;color:#e9f7ff;font-size:.92rem}
    .booking-car-date{font-size:.78rem;color:#8aa3b2;margin-top:3px}
    .booking-status{padding:4px 12px;border-radius:20px;font-size:.72rem;font-weight:700;white-space:nowrap;flex-shrink:0}
    .btn-cancel-sm{padding:5px 14px;font-size:.75rem;border-radius:20px;border:1px solid rgba(231,76,60,.5);background:rgba(231,76,60,.08);color:#e74c3c;cursor:pointer;transition:all 200ms;font-weight:600;flex-shrink:0}
    .btn-cancel-sm:hover{background:rgba(231,76,60,.18);border-color:#e74c3c}
    .empty-state{text-align:center;padding:50px 20px;color:#8aa3b2}
    .empty-icon{font-size:3rem;margin-bottom:12px}
    @media(max-width:900px){.profile-grid{grid-template-columns:1fr}.profile-card{position:static}}
</style>

  <?php if ($errors): ?>
  <div class="alert alert-danger" style="margin-bottom:20px;">
    <?php foreach ($errors as $err): ?><div>❌ <?= htmlspecialchars($err) ?></div><?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php if ($success): ?>
  <div class="alert alert-success" data-autohide="5000" style="margin-bottom:20px;">✅ <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <div class="profile-grid">

    <!-- ── LEFT: Profile Card ── -->
    <aside class="profile-card">
      <div class="avatar-ring"><?= mb_strtoupper(mb_substr($user['full_name'], 0, 1, 'UTF-8')) ?></div>
      <div class="profile-name"><?= htmlspecialchars($user['full_name']) ?></div>
      <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
      <div style="margin-top:8px;">
        <span class="profile-role"><?= $user['role'] === 'admin' ? '⚡ Admin' : '👤 Member' ?></span>
      </div>
      <div class="profile-stat">
        <div class="pstat"><div class="pstat-num"><?= $totalBook ?></div><div class="pstat-lbl">การจองทั้งหมด</div></div>
        <div class="pstat"><div class="pstat-num"><?= $confirmedBook ?></div><div class="pstat-lbl">ยืนยันแล้ว</div></div>
      </div>
      <div class="member-since">สมาชิกตั้งแต่ <?= date('d M Y', strtotime($user['created_at'])) ?></div>
      <div style="margin-top:20px;">
        <a href="my_bookings.php" class="btn btn-primary btn-full" style="display:flex;justify-content:center;text-decoration:none;margin-top:0;">
          🗓 ดูการจองทั้งหมด
        </a>
      </div>
    </aside>

    <!-- ── RIGHT: Tab Content ── -->
    <main>
      <div class="tab-bar">
        <button class="tab-btn active" onclick="switchTab('info',this)">📋 ข้อมูลส่วนตัว</button>
        <button class="tab-btn" onclick="switchTab('password',this)">🔑 เปลี่ยนรหัสผ่าน</button>
        <button class="tab-btn" onclick="switchTab('bookings',this)">🚗 การจองล่าสุด</button>
      </div>

      <!-- ── Tab: Info ── -->
      <div id="tab-info" class="tab-panel active">
        <div class="panel-box">
          <div class="panel-title">แก้ไขข้อมูลส่วนตัว</div>
          <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            <div class="form-group">
              <label class="form-label">ชื่อผู้ใช้</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled style="opacity:.5;cursor:not-allowed;">
            </div>
            <div class="form-group">
              <label class="form-label">อีเมล</label>
              <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:.5;cursor:not-allowed;">
            </div>
            <div class="form-group">
              <label class="form-label">ชื่อ-นามสกุล <span style="color:#e74c3c;">*</span></label>
              <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">เบอร์โทรศัพท์</label>
              <input type="text" name="phone" class="form-control" placeholder="0xx-xxx-xxxx" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">💾 บันทึกข้อมูล</button>
          </form>
        </div>
      </div>

      <!-- ── Tab: Password ── -->
      <div id="tab-password" class="tab-panel">
        <div class="panel-box">
          <div class="panel-title">เปลี่ยนรหัสผ่าน</div>
          <form method="POST">
            <input type="hidden" name="action" value="change_password">
            <div class="form-group">
              <label class="form-label">รหัสผ่านปัจจุบัน</label>
              <input type="password" name="old_password" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">รหัสผ่านใหม่ (อย่างน้อย 6 ตัว)</label>
              <input type="password" name="new_password" class="form-control" required minlength="6">
            </div>
            <div class="form-group">
              <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
              <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">🔑 เปลี่ยนรหัสผ่าน</button>
          </form>
        </div>
      </div>

      <!-- ── Tab: Bookings ── -->
      <div id="tab-bookings" class="tab-panel">
        <div class="panel-box">
          <div class="panel-title">การจองทดสอบขับล่าสุด (10 รายการ)</div>
          <?php if (empty($bookings)): ?>
          <div class="empty-state">
            <div class="empty-icon">🗓</div>
            <p>ยังไม่มีการจองทดสอบขับ</p>
            <a href="search.php" class="btn btn-primary" style="margin-top:16px;display:inline-flex;text-decoration:none;">ไปค้นหารถ</a>
          </div>
          <?php else: foreach ($bookings as $b):
            $bc = $bookColors[$b['status']] ?? ['rgba(100,100,100,.15)','#aaa','rgba(100,100,100,.3)']; ?>
          <div class="booking-row">
            <div class="booking-car">
              <div class="booking-car-name">🚗 <?= htmlspecialchars($b['brand_name'].' '.$b['model_name']) ?></div>
              <div class="booking-car-date">📅 <?= date('d/m/Y', strtotime($b['booking_date'])) ?> &nbsp;⏰ <?= substr($b['booking_time'],0,5) ?></div>
            </div>
            <span class="booking-status" style="background:<?= $bc[0] ?>;color:<?= $bc[1] ?>;border:1px solid <?= $bc[2] ?>;">
              <?= BOOK_LABELS[$b['status']] ?? $b['status'] ?>
            </span>
            <?php if ($b['status'] === 'pending'): ?>
            <form method="POST" onsubmit="return confirm('ยืนยันการยกเลิกการจอง?')">
              <input type="hidden" name="action" value="cancel_booking">
              <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
              <button type="submit" class="btn-cancel-sm">✕ ยกเลิก</button>
            </form>
            <?php endif; ?>
          </div>
          <?php endforeach; endif; ?>
          <?php if (!empty($bookings)): ?>
          <div style="text-align:center;margin-top:16px;">
            <a href="my_bookings.php" class="btn btn-outline btn-sm" style="display:inline-flex;text-decoration:none;">ดูทั้งหมด →</a>
          </div>
          <?php endif; ?>
        </div>
      </div>

    </main>
  </div>
</div>

<script src="assets/js/main.js"></script>
<script>
function switchTab(name, btn) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  btn.classList.add('active');
}
<?php $lastAction = $_POST['action'] ?? ''; ?>
<?php if ($lastAction === 'change_password'): ?>
window.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.tab-btn')[1].click());
<?php elseif ($lastAction === 'cancel_booking'): ?>
window.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.tab-btn')[2].click());
<?php endif; ?>
</script>
<?php require 'includes/user_footer.php'; ?>