<?php
// ============================================================
//  CarVerse 3D | Header/Navbar สำหรับหน้าผู้ใช้ (ซื้อ/ขายรถ)
//  ตัวแปรที่ต้องตั้งก่อน require: $pageTitle, $pageHeading, $pageDesc, $activeNav
// ============================================================
$activeNav   = $activeNav   ?? '';
$pageHeading = $pageHeading ?? '';
$pageDesc    = $pageDesc    ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($pageTitle ?? '') ?> | CarVerse 3D</title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/search.css">
  <style>
    .t-card{border:1px solid rgba(31,200,255,.14);border-radius:18px;padding:24px;margin-bottom:18px;background:linear-gradient(145deg,rgba(31,200,255,.05),rgba(11,134,214,.01))}
    .t-card h2{font-family:'Barlow Condensed',sans-serif;font-size:1.5rem;color:#e9f7ff;margin-bottom:14px}
    .t-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}
    .t-grid .full{grid-column:1/-1}
    .t-label{display:block;font-size:.82rem;color:#8aa3b2;margin-bottom:6px;font-weight:600}
    .t-row{display:flex;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px dashed rgba(31,200,255,.12);font-size:.92rem;color:#c9dbe6}
    .t-row:last-child{border-bottom:0}
    .t-total{font-size:1.5rem;font-weight:800;color:#1fc8ff;font-family:'Barlow Condensed',sans-serif}
    .t-item{display:grid;grid-template-columns:100px 1fr auto;gap:18px;align-items:center;padding:18px 22px;border:1px solid rgba(31,200,255,.12);border-radius:16px;margin-bottom:14px;background:rgba(31,200,255,.03)}
    .t-thumb{width:100px;height:70px;border-radius:10px;overflow:hidden;background:rgba(31,200,255,.06);display:flex;align-items:center;justify-content:center;font-size:1.8rem}
    .t-thumb img{width:100%;height:100%;object-fit:cover}
    .t-meta{font-size:.8rem;color:#8aa3b2;display:flex;gap:14px;flex-wrap:wrap;margin-top:4px}
    .t-side{display:flex;flex-direction:column;align-items:flex-end;gap:8px}
    .t-btn-danger{padding:6px 16px;font-size:.78rem;border-radius:20px;border:1px solid rgba(231,76,60,.45);background:rgba(231,76,60,.08);color:#e74c3c;cursor:pointer;font-weight:600}
    .t-btn-ok{padding:6px 16px;font-size:.78rem;border-radius:20px;border:1px solid rgba(46,204,113,.5);background:rgba(46,204,113,.1);color:#2ecc71;cursor:pointer;font-weight:600}
    .t-empty{text-align:center;padding:60px 20px;color:#8aa3b2}
    .t-err{color:#e74c3c;font-size:.8rem;margin-top:4px}
    @media(max-width:640px){.t-item{grid-template-columns:1fr}.t-side{align-items:flex-start}}
  </style>
</head>
<body>
<canvas id="particles" class="particles-canvas"></canvas>

<!-- Navbar มาตรฐานเดียวกันทุกหน้า -->
<nav class="navbar" id="main-navbar">
  <div class="nav-logo">CAR<span>VERSE</span></div>
  <div class="nav-links">
    <a href="index.php"       class="nav-link <?= $activeNav==='home'    ? 'active' : '' ?>">หน้าหลัก</a>
    <a href="search.php"      class="nav-link <?= $activeNav==='search'  ? 'active' : '' ?>">ค้นหารถ</a>
    <a href="sell_car.php"    class="nav-link <?= $activeNav==='sell'    ? 'active' : '' ?>">ขายรถ</a>
    <a href="my_orders.php"   class="nav-link <?= $activeNav==='orders'  ? 'active' : '' ?>">คำสั่งซื้อ</a>
    <a href="my_bookings.php" class="nav-link <?= $activeNav==='booking' ? 'active' : '' ?>">การจองของฉัน</a>
    <a href="profile.php"     class="nav-link <?= $activeNav==='profile' ? 'active' : '' ?>">โปรไฟล์</a>
    <?php if (isAdmin()): ?>
      <a href="admin_orders.php" class="nav-link">จัดการออเดอร์</a>
      <a href="admin_sell.php"   class="nav-link">รับซื้อรถ</a>
    <?php endif; ?>
    <a href="logout.php" class="btn btn-outline btn-sm">ออกจากระบบ</a>
  </div>
</nav>

<div class="page-header">
  <h1><?= $pageHeading ?></h1>
  <p><?= htmlspecialchars($pageDesc) ?></p>
</div>

<div class="container" style="padding-bottom:80px;">