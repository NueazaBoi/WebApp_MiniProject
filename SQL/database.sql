-- ============================================================
--  CarVerse 3D | Database Schema
--  ออกแบบโดย: ER Diagram → Relational Mapping
--  ฐานข้อมูลระบบขายรถยนต์พร้อม Test Drive Booking
-- ============================================================

CREATE DATABASE IF NOT EXISTS carverse_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE carverse_db;

-- ============================================================
--  TABLE: users  (สมาชิกและแอดมิน)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    user_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    full_name   VARCHAR(100) NOT NULL,
    phone       VARCHAR(20)  DEFAULT NULL,
    role        ENUM('user','admin') DEFAULT 'user',
    avatar      VARCHAR(255) DEFAULT NULL,
    is_active   TINYINT(1)   DEFAULT 1,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
--  TABLE: brands  (แบรนด์รถยนต์)
-- ============================================================
CREATE TABLE IF NOT EXISTS brands (
    brand_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    brand_name  VARCHAR(100) NOT NULL UNIQUE,
    logo_url    VARCHAR(255) DEFAULT NULL,
    country     VARCHAR(100) DEFAULT NULL,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
--  TABLE: car_types  (ประเภทรถ)
-- ============================================================
CREATE TABLE IF NOT EXISTS car_types (
    type_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type_name   VARCHAR(100) NOT NULL UNIQUE,
    description TEXT         DEFAULT NULL
) ENGINE=InnoDB;

-- ============================================================
--  TABLE: cars  (ข้อมูลรถยนต์หลัก)
-- ============================================================
CREATE TABLE IF NOT EXISTS cars (
    car_id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    brand_id        INT UNSIGNED NOT NULL,
    type_id         INT UNSIGNED NOT NULL,
    model_name      VARCHAR(150) NOT NULL,
    year            YEAR         NOT NULL,
    color           VARCHAR(50)  DEFAULT NULL,
    price           DECIMAL(12,2) NOT NULL,
    mileage         INT UNSIGNED DEFAULT 0,
    fuel_type       ENUM('gasoline','diesel','electric','hybrid') DEFAULT 'gasoline',
    transmission    ENUM('manual','automatic','cvt') DEFAULT 'automatic',
    engine_cc       INT UNSIGNED DEFAULT NULL,
    horsepower      INT UNSIGNED DEFAULT NULL,
    seats           TINYINT UNSIGNED DEFAULT 5,
    description     TEXT         DEFAULT NULL,
    image_url       VARCHAR(255) DEFAULT NULL,
    model_3d_url    VARCHAR(255) DEFAULT NULL,
    stock_qty       INT UNSIGNED DEFAULT 1,
    status          ENUM('available','sold','reserved') DEFAULT 'available',
    created_by      INT UNSIGNED DEFAULT NULL,
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_car_brand   FOREIGN KEY (brand_id)   REFERENCES brands(brand_id)   ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_car_type    FOREIGN KEY (type_id)    REFERENCES car_types(type_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_car_creator FOREIGN KEY (created_by) REFERENCES users(user_id)     ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
--  TABLE: car_images  (แกลเลอรีรูปภาพ)
-- ============================================================
CREATE TABLE IF NOT EXISTS car_images (
    image_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    car_id      INT UNSIGNED NOT NULL,
    image_url   VARCHAR(255) NOT NULL,
    sort_order  TINYINT UNSIGNED DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_img_car FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
--  TABLE: test_drive_bookings  (จองทดลองขับ)
-- ============================================================
CREATE TABLE IF NOT EXISTS test_drive_bookings (
    booking_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    car_id          INT UNSIGNED NOT NULL,
    booking_date    DATE         NOT NULL,
    booking_time    TIME         NOT NULL,
    status          ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    notes           TEXT         DEFAULT NULL,
    confirmed_by    INT UNSIGNED DEFAULT NULL,
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_booking_user  FOREIGN KEY (user_id)      REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_booking_car   FOREIGN KEY (car_id)       REFERENCES cars(car_id)   ON DELETE CASCADE,
    CONSTRAINT fk_booking_admin FOREIGN KEY (confirmed_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
--  TABLE: favorites  (Wishlist)
-- ============================================================
CREATE TABLE IF NOT EXISTS favorites (
    fav_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    car_id      INT UNSIGNED NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_fav (user_id, car_id),
    CONSTRAINT fk_fav_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_fav_car  FOREIGN KEY (car_id)  REFERENCES cars(car_id)   ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
--  SAMPLE DATA
-- ============================================================
INSERT INTO brands (brand_name, country) VALUES
  ('Toyota','Japan'),('BMW','Germany'),('Mercedes-Benz','Germany'),
  ('Honda','Japan'),('Tesla','USA'),('Ford','USA'),
  ('Mazda','Japan'),('Audi','Germany');

INSERT INTO car_types (type_name, description) VALUES
  ('SUV','Sport Utility Vehicle'),('Sedan','รถยนต์นั่ง 4 ประตู'),
  ('Hatchback','รถยนต์ 5 ประตู'),('Pickup','รถกระบะ'),
  ('Sports','รถสปอร์ต'),('Electric','รถยนต์ไฟฟ้า'),('MPV','รถอเนกประสงค์');

-- Admin password: Admin@123  (bcrypt hash below)
INSERT INTO users (username, email, password, full_name, role) VALUES
  ('admin','admin@carverse.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','System Admin','admin');

INSERT INTO cars (brand_id, type_id, model_name, year, color, price, fuel_type, transmission, engine_cc, horsepower, seats, stock_qty, status, description) VALUES
  (1,1,'Fortuner Legender 4WD',2024,'Black',1850000,'diesel','automatic',2755,204,7,3,'available','รถ SUV สุดแกร่ง'),
  (2,5,'M4 Competition',2024,'Blue',6500000,'gasoline','automatic',2993,510,4,1,'available','สปอร์ตสุดขีด Twin-Turbo'),
  (3,2,'C300 AMG Line',2024,'Silver',3200000,'gasoline','automatic',1999,258,5,2,'available','หรูหรา ทันสมัย MBUX'),
  (5,6,'Model 3 Long Range',2024,'White',1990000,'electric','automatic',0,358,5,4,'available','ไฟฟ้า 100% 602 km/charge'),
  (7,2,'Mazda3 Skyactiv-X',2024,'Red',999000,'gasoline','automatic',1998,178,5,5,'available','สปอร์ตพรีเมียม KODO'),
  (4,3,'Honda Civic e:HEV',2024,'Gray',1099000,'hybrid','cvt',1498,183,5,3,'available','ไฮบริด 26.5 km/L');
