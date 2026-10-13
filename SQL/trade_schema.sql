-- ============================================================
--  CarVerse 3D | ตารางเพิ่มเติม: ซื้อรถ (orders) / ขายรถ (sell_requests)
--  รันหลัง database.sql
-- ============================================================
USE carverse_db;

CREATE TABLE IF NOT EXISTS orders (
    order_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NOT NULL,
    car_id         INT UNSIGNED NOT NULL,
    quantity       INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price     DECIMAL(12,2) NOT NULL,
    total_price    DECIMAL(14,2) NOT NULL,
    payment_method ENUM('transfer','cash','installment') NOT NULL DEFAULT 'transfer',
    contact_phone  VARCHAR(20)  NOT NULL,
    address        TEXT         DEFAULT NULL,
    notes          TEXT         DEFAULT NULL,
    status         ENUM('pending','paid','completed','cancelled') NOT NULL DEFAULT 'pending',
    handled_by     INT UNSIGNED DEFAULT NULL,
    created_at     DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user  FOREIGN KEY (user_id)    REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_order_car   FOREIGN KEY (car_id)     REFERENCES cars(car_id)   ON DELETE RESTRICT,
    CONSTRAINT fk_order_admin FOREIGN KEY (handled_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sell_requests (
    request_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NOT NULL,
    brand_id       INT UNSIGNED NOT NULL,
    type_id        INT UNSIGNED NOT NULL,
    model_name     VARCHAR(150) NOT NULL,
    year           YEAR         NOT NULL,
    color          VARCHAR(50)  DEFAULT NULL,
    mileage        INT UNSIGNED DEFAULT 0,
    fuel_type      ENUM('gasoline','diesel','electric','hybrid') DEFAULT 'gasoline',
    transmission   ENUM('manual','automatic','cvt') DEFAULT 'automatic',
    expected_price DECIMAL(12,2) NOT NULL,
    description    TEXT         DEFAULT NULL,
    image_url      VARCHAR(255) DEFAULT NULL,
    contact_phone  VARCHAR(20)  NOT NULL,
    status         ENUM('pending','offered','accepted','declined','rejected','purchased') NOT NULL DEFAULT 'pending',
    offer_price    DECIMAL(12,2) DEFAULT NULL,
    admin_note     TEXT         DEFAULT NULL,
    reviewed_by    INT UNSIGNED DEFAULT NULL,
    car_id         INT UNSIGNED DEFAULT NULL,   -- รถที่ถูกเพิ่มเข้าสต็อกหลังรับซื้อ
    created_at     DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_sell_user   FOREIGN KEY (user_id)     REFERENCES users(user_id)     ON DELETE CASCADE,
    CONSTRAINT fk_sell_brand  FOREIGN KEY (brand_id)    REFERENCES brands(brand_id)   ON DELETE RESTRICT,
    CONSTRAINT fk_sell_type   FOREIGN KEY (type_id)     REFERENCES car_types(type_id) ON DELETE RESTRICT,
    CONSTRAINT fk_sell_admin  FOREIGN KEY (reviewed_by) REFERENCES users(user_id)     ON DELETE SET NULL,
    CONSTRAINT fk_sell_car    FOREIGN KEY (car_id)      REFERENCES cars(car_id)       ON DELETE SET NULL
) ENGINE=InnoDB;
