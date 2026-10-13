-- ============================================================
--  CarVerse 3D | ข้อมูลตัวอย่างเพิ่มเติม (สำหรับทดสอบหน้ารายงาน)
--  รันหลัง database.sql   |  รหัสผ่านสมาชิกตัวอย่างทุกคน = password
-- ============================================================
USE carverse_db;

INSERT IGNORE INTO users (username, email, password, full_name, phone, role) VALUES
 ('somchai','somchai@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','สมชาย ใจดี','0811111111','user'),
 ('suda','suda@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','สุดา รักรถ','0822222222','user'),
 ('anan','anan@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','อนันต์ มั่งมี','0833333333','user');

INSERT INTO test_drive_bookings (user_id, car_id, booking_date, booking_time, status, notes)
SELECT u.user_id, c.car_id, d.dt, d.tm, d.st, d.nt FROM (
  SELECT 'somchai' un, 2 cid, DATE_SUB(CURDATE(), INTERVAL 75 DAY) dt, '10:00:00' tm, 'completed' st, 'สนใจมาก' nt UNION ALL
  SELECT 'suda',    4, DATE_SUB(CURDATE(), INTERVAL 50 DAY), '11:30:00', 'completed', NULL UNION ALL
  SELECT 'anan',    2, DATE_SUB(CURDATE(), INTERVAL 33 DAY), '14:00:00', 'cancelled', 'ติดธุระ' UNION ALL
  SELECT 'somchai', 4, DATE_SUB(CURDATE(), INTERVAL 20 DAY), '09:30:00', 'completed', NULL UNION ALL
  SELECT 'suda',    1, DATE_SUB(CURDATE(), INTERVAL 8 DAY),  '13:00:00', 'confirmed', 'ขอขับวันหยุด' UNION ALL
  SELECT 'anan',    6, DATE_ADD(CURDATE(), INTERVAL 2 DAY),  '10:00:00', 'pending',   NULL UNION ALL
  SELECT 'somchai', 2, DATE_ADD(CURDATE(), INTERVAL 5 DAY),  '15:00:00', 'pending',   NULL
) d
JOIN users u ON u.username = d.un
JOIN cars  c ON c.car_id   = d.cid;
