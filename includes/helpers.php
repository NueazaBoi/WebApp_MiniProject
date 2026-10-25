<?php
// ============================================================
//  CarVerse 3D | Shared Helpers (CSRF, Flash, Upload, Validate)
//  ไฟล์: includes/helpers.php
// ============================================================

/* ── CSRF ── */
function csrfToken(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrfField(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}
function verifyCsrf(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
}

/* ── Flash message ── */
function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function getFlash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ── Label maps (ภาษาไทย) ── */
const FUEL_LABELS   = ['gasoline'=>'น้ำมัน','diesel'=>'ดีเซล','electric'=>'ไฟฟ้า','hybrid'=>'ไฮบริด'];
const TRANS_LABELS  = ['manual'=>'Manual','automatic'=>'Auto','cvt'=>'CVT'];
const STATUS_LABELS = ['available'=>'พร้อมขาย','reserved'=>'จอง','sold'=>'ขายแล้ว'];
const BOOK_LABELS   = ['pending'=>'รอยืนยัน','confirmed'=>'ยืนยันแล้ว','completed'=>'เสร็จสิ้น','cancelled'=>'ยกเลิก'];

/* ── Upload รูปรถ ── */
const UPLOAD_DIR = __DIR__ . '/../uploads/cars/';
const UPLOAD_URL = 'uploads/cars/';

/** คืนค่า path (relative) ถ้าสำเร็จ, null ถ้าไม่ได้เลือกไฟล์, throw Exception ถ้าผิดพลาด */
function uploadCarImage(array $file): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK)  throw new Exception('อัปโหลดไฟล์ไม่สำเร็จ (รหัส ' . $file['error'] . ')');
    if ($file['size'] > 5 * 1024 * 1024)   throw new Exception('ไฟล์รูปต้องมีขนาดไม่เกิน 5 MB');

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) throw new Exception('รองรับเฉพาะไฟล์ JPG, PNG, WEBP');

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $name = 'car_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) {
        throw new Exception('ไม่สามารถบันทึกไฟล์ได้ (ตรวจสอบสิทธิ์โฟลเดอร์ uploads/cars)');
    }
    return UPLOAD_URL . $name;
}

/** ลบไฟล์รูปเฉพาะที่อยู่ใน uploads/cars/ เท่านั้น */
function deleteUploadedFile(?string $path): void {
    if (!$path || strpos($path, UPLOAD_URL) !== 0) return;
    $full = UPLOAD_DIR . basename($path);
    if (is_file($full)) @unlink($full);
}

/* ── Validate ฟอร์มรถ (ใช้ร่วมกันทั้ง add / edit) ── */
function validateCar(array $in): array {
    $errors = [];
    $d = [
        'brand_id'     => (int)($in['brand_id'] ?? 0),
        'type_id'      => (int)($in['type_id'] ?? 0),
        'model_name'   => trim($in['model_name'] ?? ''),
        'year'         => (int)($in['year'] ?? 0),
        'color'        => trim($in['color'] ?? ''),
        'price'        => (float)($in['price'] ?? 0),
        'mileage'      => max(0, (int)($in['mileage'] ?? 0)),
        'fuel_type'    => $in['fuel_type'] ?? 'gasoline',
        'transmission' => $in['transmission'] ?? 'automatic',
        'engine_cc'    => ($in['engine_cc'] ?? '') !== '' ? max(0, (int)$in['engine_cc']) : null,
        'horsepower'   => ($in['horsepower'] ?? '') !== '' ? max(0, (int)$in['horsepower']) : null,
        'seats'        => (int)($in['seats'] ?? 5),
        'stock_qty'    => max(0, (int)($in['stock_qty'] ?? 1)),
        'status'       => $in['status'] ?? 'available',
        'description'  => trim($in['description'] ?? ''),
        'model_3d_url' => trim($in['model_3d_url'] ?? ''),
    ];
    if (!$d['brand_id'])                        $errors['brand_id']   = 'กรุณาเลือกแบรนด์';
    if (!$d['type_id'])                         $errors['type_id']    = 'กรุณาเลือกประเภทรถ';
    if ($d['model_name'] === '')                $errors['model_name'] = 'กรุณากรอกชื่อรุ่น';
    elseif (mb_strlen($d['model_name']) > 150)  $errors['model_name'] = 'ชื่อรุ่นยาวเกิน 150 ตัวอักษร';
    if ($d['year'] < 1990 || $d['year'] > (int)date('Y') + 1) $errors['year'] = 'ปีรถไม่ถูกต้อง';
    if ($d['price'] <= 0)                       $errors['price']      = 'ราคาต้องมากกว่า 0';
    if ($d['seats'] < 1 || $d['seats'] > 20)    $errors['seats']      = 'จำนวนที่นั่ง 1-20';
    if (!isset(FUEL_LABELS[$d['fuel_type']]))      $d['fuel_type']    = 'gasoline';
    if (!isset(TRANS_LABELS[$d['transmission']]))  $d['transmission'] = 'automatic';
    if (!isset(STATUS_LABELS[$d['status']]))       $d['status']       = 'available';
    return [$errors, $d];
}

/** คืนค่า image_url ของรถ ถ้าไม่มีใน DB ให้ตรวจจับไฟล์รูปที่ตรงกับรุ่นรถจาก uploads/cars/ อัตโนมัติ */
function carImage($car): string {
    $img = is_array($car) ? ($car['image_url'] ?? '') : ($car ?? '');
    if (!empty($img) && is_file(__DIR__ . '/../' . $img)) return $img;
    
    $name = strtolower(is_array($car) ? ($car['model_name'] ?? '') : (string)$car);
    if (strpos($name, 'fortuner') !== false) {
        return (strpos($name, 'sigma') !== false) ? 'uploads/cars/toyota_fortuner_sigma4.jpg' : 'uploads/cars/toyota_fortuner.jpg';
    }
    if (strpos($name, 'cla') !== false) return 'uploads/cars/mercedes_cla.jpg';
    if (strpos($name, '220') !== false) return 'uploads/cars/mercedes_c220d.jpg';
    if (strpos($name, 'c300') !== false) return 'uploads/cars/mercedes_c300.jpg';
    if (strpos($name, 'cx-30') !== false || strpos($name, 'cx30') !== false) return 'uploads/cars/mazda_cx30.jpg';
    if (strpos($name, 'mazda3') !== false || strpos($name, 'mazda 3') !== false) return 'uploads/cars/mazda3_sedan.jpg';
    if (strpos($name, 'civic') !== false) {
        $type = strtolower(is_array($car) ? ($car['type_name'] ?? '') : '');
        return ($type === 'hatchback' || strpos($name, 'hatchback') !== false)
            ? 'uploads/cars/honda_civic_hatchback.jpg' 
            : 'uploads/cars/honda_civic.jpg';
    }
    if (strpos($name, 'm4') !== false) return 'uploads/cars/bmw_m4.jpg';
    if (strpos($name, 'model 3') !== false || strpos($name, 'tesla') !== false) return 'uploads/cars/tesla_model3.jpg';
    return !empty($img) ? $img : '';
}

