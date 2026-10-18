<?php
// ============================================================
//  CarVerse 3D | Admin Layout (Header + Sidebar)
//  ไฟล์: includes/admin_header.php
//  ต้องกำหนด $pageTitle, $activeNav ก่อน include
// ============================================================
$pageTitle  = $pageTitle  ?? 'จัดการระบบ';
$pageDesc   = $pageDesc   ?? '';
$activeNav  = $activeNav  ?? '';
$flash      = getFlash();
$nav = [
  'manage'  => ['edit_car.php', '🚘', 'จัดการรถ (แก้ไข/ลบ)'],
  'add'     => ['add_car.php',  '➕', 'เพิ่มรถใหม่'],
  'orders'  => ['admin_orders.php', '📦', 'จัดการคำสั่งซื้อ'],
  'report1' => ['report1.php',  '📊', 'รายงานสต็อกรถ'],
  'report2' => ['report2.php',  '📅', 'รายงานการจองทดลองขับ'],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | CarVerse 3D Admin</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<canvas id="particles" class="particles-canvas"></canvas>

<div class="admin-layout">
  <aside class="sidebar">
    <a href="index.php" class="sidebar-logo" style="display:block;">CAR<span>VERSE</span></a>
    <nav class="sidebar-nav">
      <?php foreach ($nav as $key => [$href, $icon, $label]): ?>
        <a href="<?= $href ?>" class="sidebar-link <?= $activeNav === $key ? 'active' : '' ?>">
          <span class="sidebar-icon"><?= $icon ?></span><?= $label ?>
        </a>
      <?php endforeach; ?>
      <div class="sidebar-sep"></div>
      <a href="search.php" class="sidebar-link"><span class="sidebar-icon">🔍</span>ค้นหารถ (หน้าลูกค้า)</a>
      <a href="index.php"  class="sidebar-link"><span class="sidebar-icon">🏠</span>หน้าหลัก</a>
    </nav>
    <div class="sidebar-user">
      <div class="sidebar-user-name"><?= e((string)($_SESSION['full_name'] ?? '')) ?> <span class="badge badge-admin">ADMIN</span></div>
      <a href="logout.php" class="btn btn-outline btn-sm btn-full" style="justify-content:center;margin-top:12px;">ออกจากระบบ</a>
    </div>
  </aside>

  <main class="admin-content">
    <div class="admin-header">
      <h1><?= e($pageTitle) ?></h1>
      <?php if ($pageDesc): ?><p><?= e($pageDesc) ?></p><?php endif; ?>
    </div>
    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : e($flash['type']) ?>" data-autohide="5000">
        <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>