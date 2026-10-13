<?php
// ============================================================
//  CarVerse 3D | Login Page
//  login.php
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

// ถ้า login อยู่แล้ว redirect
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    } else {
        // ค้นหาด้วย username หรือ email
        $stmt = $pdo->prepare("
            SELECT * FROM users
            WHERE (username = ? OR email = ?) AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            createSession($user);
            $redirect = $_GET['redirect'] ?? ($user['role'] === 'admin' ? 'edit_car.php' : 'index.php');
            header("Location: $redirect");
            exit;
        } else {
            $error = 'ชื่อผู้ใช้/อีเมล หรือรหัสผ่านไม่ถูกต้อง';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เข้าสู่ระบบ | CarVerse 3D</title>
  <meta name="description" content="เข้าสู่ระบบ CarVerse 3D เพื่อเข้าถึงฟีเจอร์พรีเมียม">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-body">

<canvas id="particles" class="particles-canvas"></canvas>

<!-- Back to Home -->
<a href="index.php" class="auth-back">← กลับหน้าหลัก</a>

<div class="auth-container">

  <!-- Left Panel - 3D Visual -->
  <div class="auth-visual">
    <canvas id="auth-canvas"></canvas>
    <div class="auth-visual-content">
      <div class="auth-logo">CAR<span>VERSE</span></div>
      <div class="auth-logo-sub">3D PREMIUM CARS</div>
      <div class="auth-features">
        <div class="auth-feature">✦ ชมรถ 3D Real-Time</div>
        <div class="auth-feature">✦ จองทดลองขับออนไลน์</div>
        <div class="auth-feature">✦ รถพรีเมียมกว่า 100 คัน</div>
      </div>
    </div>
  </div>

  <!-- Right Panel - Login Form -->
  <div class="auth-form-panel">
    <div class="auth-form-inner">

      <div class="auth-heading">
        <h1>ยินดีต้อนรับกลับ</h1>
        <p>เข้าสู่ระบบเพื่อประสบการณ์รถยนต์ที่ดีที่สุด</p>
      </div>

      <?php if ($error): ?>
      <div class="alert alert-danger" data-autohide="5000">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        <?= e($error) ?>
      </div>
      <?php endif; ?>

      <?php if (isset($_GET['registered'])): ?>
      <div class="alert alert-success" data-autohide="5000">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ
      </div>
      <?php endif; ?>

      <form method="POST" action="login.php<?= isset($_GET['redirect']) ? '?redirect='.urlencode($_GET['redirect']) : '' ?>" id="loginForm" novalidate>

        <div class="form-group">
          <label class="form-label" for="identifier">ชื่อผู้ใช้ หรือ อีเมล</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input
              type="text"
              id="identifier"
              name="identifier"
              class="form-control input-with-icon"
              placeholder="username หรือ email@example.com"
              value="<?= e($_POST['identifier'] ?? '') ?>"
              autocomplete="username"
              required
            >
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">รหัสผ่าน</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input
              type="password"
              id="password"
              name="password"
              class="form-control input-with-icon"
              placeholder="••••••••"
              autocomplete="current-password"
              required
            >
            <button type="button" class="toggle-pass" onclick="togglePassword()" title="แสดง/ซ่อนรหัสผ่าน">
              <svg id="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
          <label class="checkbox-wrap">
            <input type="checkbox" name="remember" id="remember">
            <span class="checkmark"></span>
            <span style="font-size:0.875rem;color:var(--clr-muted);">จดจำฉัน</span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg" id="loginBtn">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
          เข้าสู่ระบบ
        </button>

        <div class="auth-divider"><span>หรือ</span></div>

        <div class="auth-switch">
          ยังไม่มีบัญชี?
          <a href="register.php" class="auth-switch-link">สมัครสมาชิกฟรี →</a>
        </div>

        <!-- Demo Accounts hint -->
        <div class="demo-hint">
          <div class="demo-hint-title">🔑 Demo Account</div>
          <div class="demo-row" onclick="fillDemo('admin','password')">
            <span class="badge badge-admin">ADMIN</span>
            <span>admin / password</span>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

<script src="assets/js/main.js"></script>
<script>
  function togglePassword() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
  }
  function fillDemo(user, pass) {
    document.getElementById('identifier').value = user;
    document.getElementById('password').value = pass;
  }

  // Auth Canvas - spinning car silhouette
  (function() {
    const canvas = document.getElementById('auth-canvas');
    if (!canvas || typeof THREE === 'undefined') return;
    // Defer Three.js init to after load
    window.addEventListener('load', () => {
      if (typeof THREE === 'undefined') return;
      const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
      renderer.setPixelRatio(window.devicePixelRatio);
      renderer.setSize(canvas.clientWidth, canvas.clientHeight);
      const scene = new THREE.Scene();
      const cam = new THREE.PerspectiveCamera(50, canvas.clientWidth / canvas.clientHeight, 0.1, 100);
      cam.position.set(0, 1.5, 6);
      scene.add(new THREE.AmbientLight(0xffffff, 0.4));
      const dl = new THREE.DirectionalLight(0xe8b84b, 2);
      dl.position.set(5, 8, 5);
      scene.add(dl);
      const geo = new THREE.TorusKnotGeometry(1.5, 0.4, 100, 16);
      const mat = new THREE.MeshStandardMaterial({ color: 0xe8b84b, metalness: 0.9, roughness: 0.1, wireframe: true });
      const mesh = new THREE.Mesh(geo, mat);
      scene.add(mesh);
      function loop() {
        requestAnimationFrame(loop);
        mesh.rotation.x += 0.004;
        mesh.rotation.y += 0.008;
        renderer.render(scene, cam);
      }
      loop();
    });
  })();
</script>
<script src="https://cdn.jsdelivr.net/npm/three@0.163/build/three.min.js"></script>
</body>
</html>
