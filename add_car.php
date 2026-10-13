<?php
// ============================================================
//  CarVerse 3D | เพิ่มข้อมูลรถ (Create)
//  add_car.php
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
requireLogin('login.php?redirect=add_car.php');
requireAdmin();

$brands = $pdo->query("SELECT * FROM brands ORDER BY brand_name")->fetchAll();
$types  = $pdo->query("SELECT * FROM car_types ORDER BY type_name")->fetchAll();
$errors = [];
$car    = ['year' => date('Y'), 'stock_qty' => 1, 'seats' => 5, 'mileage' => 0, 'status' => 'available'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors['_'] = 'Session หมดอายุ กรุณาลองใหม่';
    } else {
        [$errors, $car] = validateCar($_POST);
        $imagePath = null;
        if (!$errors) {
            try { $imagePath = uploadCarImage($_FILES['image'] ?? []); }
            catch (Exception $ex) { $errors['_'] = $ex->getMessage(); }
        }
        if (!$errors) {
            $stmt = $pdo->prepare("
                INSERT INTO cars (brand_id,type_id,model_name,year,color,price,mileage,fuel_type,transmission,
                                  engine_cc,horsepower,seats,description,image_url,model_3d_url,stock_qty,status,created_by)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ");
            $stmt->execute([
                $car['brand_id'], $car['type_id'], $car['model_name'], $car['year'], $car['color'] ?: null,
                $car['price'], $car['mileage'], $car['fuel_type'], $car['transmission'],
                $car['engine_cc'], $car['horsepower'], $car['seats'], $car['description'] ?: null,
                $imagePath, $car['model_3d_url'] ?: null, $car['stock_qty'], $car['status'], $_SESSION['user_id'],
            ]);
            setFlash('success', '✅ เพิ่มรถ "' . $car['model_name'] . '" เรียบร้อยแล้ว');
            header('Location: edit_car.php');
            exit;
        }
    }
}

$pageTitle  = 'เพิ่มรถใหม่';
$pageDesc   = 'กรอกข้อมูลรถยนต์เพื่อเพิ่มเข้าระบบ';
$activeNav  = 'add';
$formAction = 'add_car.php';
$submitLabel = '💾 บันทึกข้อมูลรถ';
require 'includes/admin_header.php';
if (isset($errors['_'])): ?>
  <div class="alert alert-danger"><?= e($errors['_']) ?></div>
<?php elseif ($errors): ?>
  <div class="alert alert-danger">กรุณาตรวจสอบข้อมูลที่กรอกให้ถูกต้อง</div>
<?php endif;
require 'includes/car_form.php';
require 'includes/admin_footer.php';
