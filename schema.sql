-- =============================================================
-- SpinFit Final Database Schema
-- Run: mysql -u root -p < schema.sql
-- =============================================================

CREATE DATABASE IF NOT EXISTS spinfit_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE spinfit_db;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS user_memberships;
DROP TABLE IF EXISTS membership_plans;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)  NOT NULL,
    email         VARCHAR(150)  NOT NULL UNIQUE,
    password      VARCHAR(255)  NOT NULL,
    role          ENUM('user','admin') DEFAULT 'user',
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE classes (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)  NOT NULL,
    type          ENUM('spin','hiit') NOT NULL,
    instructor    VARCHAR(100)  NOT NULL,
    class_date    DATE          NOT NULL,
    start_time    TIME          NOT NULL,
    duration_min  INT           NOT NULL DEFAULT 45,
    venue         VARCHAR(100)  NOT NULL,
    capacity      INT           NOT NULL DEFAULT 20,
    description   TEXT,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE bookings (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT           NOT NULL,
    class_id      INT           NOT NULL,
    booked_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    status        ENUM('confirmed','cancelled') DEFAULT 'confirmed',
    UNIQUE KEY uq_user_class (user_id, class_id),
    FOREIGN KEY (user_id)  REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE membership_plans (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    plan_name       VARCHAR(100)   NOT NULL,
    description     TEXT,
    price           DECIMAL(10,2)  NOT NULL,
    duration_months INT            NOT NULL DEFAULT 1,
    status          ENUM('active','inactive') DEFAULT 'active',
    created_at      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_memberships (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    user_id             INT            NOT NULL,
    membership_plan_id  INT            NOT NULL,
    start_date          DATE           NOT NULL,
    end_date            DATE           NOT NULL,
    membership_status   ENUM('active','expired','cancelled') DEFAULT 'active',
    created_at          TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)            REFERENCES users(id)            ON DELETE CASCADE,
    FOREIGN KEY (membership_plan_id) REFERENCES membership_plans(id) ON DELETE CASCADE
);

CREATE TABLE products (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(255)   NOT NULL,
    description   TEXT,
    category      VARCHAR(100)   DEFAULT 'general',
    price         DECIMAL(10,2)  NOT NULL,
    stock         INT            NOT NULL DEFAULT 0,
    image         VARCHAR(255)   DEFAULT NULL,
    sizes         VARCHAR(255)   DEFAULT NULL,
    measurements  TEXT,
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cart_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT            NOT NULL,
    product_id  INT            NOT NULL,
    quantity    INT            NOT NULL DEFAULT 1,
    created_at  TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_product (user_id, product_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE orders (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT            NOT NULL,
    subtotal         DECIMAL(10,2)  NOT NULL,
    discount_applied TINYINT(1)     DEFAULT 0,
    discount_amount  DECIMAL(10,2)  DEFAULT 0.00,
    total            DECIMAL(10,2)  NOT NULL,
    status           ENUM('pending','processing','completed','cancelled') DEFAULT 'pending',
    created_at       TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT            NOT NULL,
    product_id  INT,
    name        VARCHAR(255)   NOT NULL,
    quantity    INT            NOT NULL,
    unit_price  DECIMAL(10,2)  NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Admin user: admin123
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@spinfit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Member user: member123
INSERT INTO users (name, email, password, role) VALUES
('Sarah Tan', 'member@spinfit.com', '$2y$10$TKh8H1.PudCs7bNgT/2FTOQ9yBFuJhVqMkqSSrJsHBKy7nFQJXi2', 'user');

INSERT INTO membership_plans (plan_name, description, price, duration_months) VALUES
('Starter', 'Perfect for beginners — 4 classes per month.', 49.00, 1),
('Pro', 'Unlimited classes + 10% shop discount + priority booking.', 89.00, 1),
('Elite', 'Everything in Pro + personal training + 20% shop discount.', 149.00, 1);

INSERT INTO user_memberships (user_id, membership_plan_id, start_date, end_date, membership_status) VALUES
(2, 2, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'active');

INSERT INTO classes (name, type, instructor, class_date, start_time, duration_min, venue, capacity, description) VALUES
('Rhythm Ride',      'spin', 'Charis',  '2026-04-01', '07:00:00', 45, 'Studio A', 20, 'High-intensity cycling to the beat. Build endurance and torch calories.'),
('Core Blast',       'hiit', 'Hana',    '2026-04-01', '12:00:00', 30, 'Studio C', 20, 'Targeted core and cardio fusion.'),
('Endurance Climb',  'spin', 'Donavan', '2026-04-01', '19:00:00', 60, 'Studio A', 25, 'Long-form ride with simulated hill climbs.'),
('Power Circuit',    'hiit', 'Marcus',  '2026-04-02', '06:30:00', 30, 'Studio B', 15, 'Explosive intervals combining strength and cardio.'),
('Rhythm Ride',      'spin', 'Charis',  '2026-04-02', '18:00:00', 45, 'Studio A', 20, 'High-intensity cycling to the beat.'),
('Core Blast',       'hiit', 'Hana',    '2026-04-03', '07:00:00', 30, 'Studio C', 20, 'Targeted core and cardio fusion.'),
('Endurance Climb',  'spin', 'Donavan', '2026-04-03', '09:00:00', 60, 'Studio A', 25, 'Long-form ride with simulated hill climbs.'),
('Power Circuit',    'hiit', 'Marcus',  '2026-04-03', '19:00:00', 30, 'Studio B', 15, 'Explosive intervals combining strength and cardio.'),
('Rhythm Ride',      'spin', 'Charis',  '2026-04-05', '09:00:00', 45, 'Studio A', 20, 'High-intensity cycling to the beat.'),
('Core Blast',       'hiit', 'Hana',    '2026-04-05', '11:00:00', 30, 'Studio C', 20, 'Targeted core and cardio fusion.'),
('Endurance Climb',  'spin', 'Donavan', '2026-04-07', '08:00:00', 60, 'Studio A', 25, 'Long-form ride with simulated hill climbs.'),
('Power Circuit',    'hiit', 'Marcus',  '2026-04-07', '17:00:00', 30, 'Studio B', 15, 'Explosive intervals combining strength and cardio.');

INSERT INTO products (name, description, category, price, stock, sizes, measurements) VALUES
('Spin Shoe Clips', 'Compatible SPD clips for all studio bikes.', 'equipment', 38.00, 30, 'One size', 'Fits most standard SPD-compatible cycling shoes.'),
('Grip Water Bottle', '1L insulated stainless steel, SpinFit branded.', 'accessories', 24.00, 80, '1L', 'Height: 29cm | Diameter: 8cm | Capacity: 1000ml'),
('Performance Tee', 'Moisture-wicking fabric with SpinFit logo.', 'apparel', 42.00, 12, 'XS, S, M, L, XL', 'Chest width: XS 44cm | S 47cm | M 50cm | L 53cm | XL 56cm. Length: XS 62cm to XL 70cm.'),
('HIIT Gloves', 'Padded palm weightlifting gloves with wrist support.', 'accessories', 19.00, 60, 'S, M, L', 'Palm width: S 7-8cm | M 8-9cm | L 9-10cm'),
('Compression Shorts', '4-way stretch, quick-dry fabric.', 'apparel', 55.00, 20, 'XS, S, M, L', 'Waist: XS 60-66cm | S 66-72cm | M 72-78cm | L 78-84cm. Inseam: 16cm.'),
('Protein Shaker', '700ml BPA-free shaker with storage compartment.', 'nutrition', 15.00, 100, '700ml', 'Height: 22cm | Diameter: 9cm | Capacity: 700ml'),
('Resistance Bands Set', 'Set of 5 bands for all fitness levels.', 'equipment', 24.99, 8, 'Set of 5', 'Band lengths: 30cm flat lay each. Resistance from extra light to extra heavy.'),
('Foam Roller', 'High-density foam roller for muscle recovery.', 'equipment', 34.99, 25, 'Standard', 'Length: 45cm | Diameter: 14cm'),
('Gym Bag', 'Spacious bag with shoe compartment and wet pouch.', 'accessories', 49.99, 20, 'One size', 'Width: 48cm | Height: 28cm | Depth: 24cm'),
('Jump Rope', 'Speed jump rope with ball-bearing handles.', 'equipment', 9.99, 45, 'Adjustable', 'Rope length adjustable up to 300cm. Handle length: 14cm.');
