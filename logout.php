<?php
// ============================================================
//  CarVerse 3D | Logout
//  logout.php
// ============================================================
require_once 'includes/auth.php';
destroySession();
header('Location: index.php');
exit;
