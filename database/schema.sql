-- ═══════════════════════════════════════════════════════════════════
-- Notch Technology — Database Schema v1.0.0
-- ═══════════════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─── Admin Users ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(100) NOT NULL,
    `email`      VARCHAR(150) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `role`       ENUM('superadmin','admin','staff') NOT NULL DEFAULT 'staff',
    `avatar`     VARCHAR(255) NULL,
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `last_login` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default superadmin: admin@notchtech.co / password: Admin@123
INSERT IGNORE INTO `admin_users` (`name`, `email`, `password`, `role`)
VALUES ('Super Admin', 'admin@notchtech.co', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- ─── Store Customers ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `customers` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`            VARCHAR(100) NOT NULL,
    `email`           VARCHAR(150) NOT NULL UNIQUE,
    `password`        VARCHAR(255) NOT NULL,
    `phone`           VARCHAR(20) NULL,
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `email_verified`  TINYINT(1) NOT NULL DEFAULT 0,
    `total_orders`    INT UNSIGNED NOT NULL DEFAULT 0,
    `total_spent`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `last_order_at`   DATETIME NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Customer Addresses ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `customer_addresses` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT UNSIGNED NOT NULL,
    `label`       VARCHAR(50) NOT NULL DEFAULT 'المنزل',
    `name`        VARCHAR(100) NOT NULL,
    `phone`       VARCHAR(20) NOT NULL,
    `address`     TEXT NOT NULL,
    `city`        VARCHAR(100) NOT NULL,
    `governorate` VARCHAR(100) NOT NULL,
    `is_default`  TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Collections (Categories) ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `collections` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(150) NOT NULL,
    `slug`        VARCHAR(150) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `image`       VARCHAR(255) NULL,
    `parent_id`   INT UNSIGNED NULL,
    `sort_order`  INT NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `meta_title`  VARCHAR(255) NULL,
    `meta_desc`   TEXT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `collections`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Brands ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `brands` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(150) NOT NULL,
    `slug`       VARCHAR(150) NOT NULL UNIQUE,
    `logo`       VARCHAR(255) NULL,
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default brands
INSERT IGNORE INTO `brands` (`name`, `slug`) VALUES
('Apple', 'apple'),
('Huawei', 'huawei'),
('Cardo', 'cardo');

-- ─── Products ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `products` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`            VARCHAR(255) NOT NULL,
    `slug`            VARCHAR(255) NOT NULL UNIQUE,
    `description`     LONGTEXT NULL,
    `short_desc`      TEXT NULL,
    `sku`             VARCHAR(100) NULL UNIQUE,
    `collection_id`   INT UNSIGNED NULL,
    `brand_id`        INT UNSIGNED NULL,
    `price`           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `compare_price`   DECIMAL(12,2) NULL COMMENT 'Original price for showing discount',
    `cost_price`      DECIMAL(12,2) NULL,
    `stock`           INT NOT NULL DEFAULT 0,
    `track_stock`     TINYINT(1) NOT NULL DEFAULT 1,
    `allow_backorder` TINYINT(1) NOT NULL DEFAULT 0,
    `weight`          DECIMAL(8,3) NULL COMMENT 'kg',
    `status`          ENUM('active','draft','archived') NOT NULL DEFAULT 'draft',
    `is_featured`     TINYINT(1) NOT NULL DEFAULT 0,
    `has_variants`    TINYINT(1) NOT NULL DEFAULT 0,
    `thumbnail`       VARCHAR(255) NULL,
    `meta_title`      VARCHAR(255) NULL,
    `meta_desc`       TEXT NULL,
    `views`           INT UNSIGNED NOT NULL DEFAULT 0,
    `sort_order`      INT NOT NULL DEFAULT 0,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`collection_id`) REFERENCES `collections`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Product Images ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `product_images` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `image`      VARCHAR(255) NOT NULL,
    `alt`        VARCHAR(255) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Product Variant Options (e.g. Color, Storage) ───────────────────
CREATE TABLE IF NOT EXISTS `product_options` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `name`       VARCHAR(100) NOT NULL COMMENT 'e.g. Color, Storage',
    `sort_order` INT NOT NULL DEFAULT 0,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Product Option Values (e.g. Red, 256GB) ─────────────────────────
CREATE TABLE IF NOT EXISTS `product_option_values` (
    `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `option_id` INT UNSIGNED NOT NULL,
    `value`     VARCHAR(100) NOT NULL,
    FOREIGN KEY (`option_id`) REFERENCES `product_options`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Product Variants ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `product_variants` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id`  INT UNSIGNED NOT NULL,
    `title`       VARCHAR(255) NOT NULL COMMENT 'e.g. Red / 256GB',
    `sku`         VARCHAR(100) NULL,
    `price`       DECIMAL(12,2) NOT NULL,
    `compare_price` DECIMAL(12,2) NULL,
    `stock`       INT NOT NULL DEFAULT 0,
    `image`       VARCHAR(255) NULL,
    `sort_order`  INT NOT NULL DEFAULT 0,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Shipping Zones ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `shipping_zones` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(100) NOT NULL,
    `governorates` TEXT NULL COMMENT 'JSON array of governorate names',
    `price`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `free_above` DECIMAL(10,2) NULL COMMENT 'Free shipping if order above this amount',
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default shipping zones
INSERT IGNORE INTO `shipping_zones` (`name`, `governorates`, `price`, `free_above`) VALUES
('القاهرة الكبرى', '["القاهرة","الجيزة","القليوبية"]', 40.00, 500.00),
('باقي المحافظات', '[]', 70.00, 800.00);

-- ─── Discount Codes ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `discounts` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code`          VARCHAR(50) NOT NULL UNIQUE,
    `type`          ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage',
    `value`         DECIMAL(10,2) NOT NULL,
    `min_order`     DECIMAL(10,2) NULL,
    `max_uses`      INT NULL,
    `used_count`    INT NOT NULL DEFAULT 0,
    `starts_at`     DATETIME NULL,
    `expires_at`    DATETIME NULL,
    `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Orders ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `orders` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_number`      VARCHAR(20) NOT NULL UNIQUE,
    `customer_id`       INT UNSIGNED NULL,
    `customer_name`     VARCHAR(100) NOT NULL,
    `customer_email`    VARCHAR(150) NOT NULL,
    `customer_phone`    VARCHAR(20) NOT NULL,
    `shipping_address`  TEXT NOT NULL,
    `shipping_city`     VARCHAR(100) NOT NULL,
    `shipping_gov`      VARCHAR(100) NOT NULL,
    `shipping_price`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `subtotal`          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `discount_code`     VARCHAR(50) NULL,
    `discount_amount`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total`             DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `payment_method`    ENUM('fawateerk','cod') NOT NULL DEFAULT 'cod',
    `payment_status`    ENUM('unpaid','paid','refunded','failed') NOT NULL DEFAULT 'unpaid',
    `payment_ref`       VARCHAR(255) NULL,
    `status`            ENUM('pending','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
    `notes`             TEXT NULL,
    `admin_notes`       TEXT NULL,
    `shipped_at`        DATETIME NULL,
    `delivered_at`      DATETIME NULL,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Order Items ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `order_items` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id`    INT UNSIGNED NOT NULL,
    `product_id`  INT UNSIGNED NULL,
    `variant_id`  INT UNSIGNED NULL,
    `name`        VARCHAR(255) NOT NULL,
    `variant`     VARCHAR(255) NULL,
    `sku`         VARCHAR(100) NULL,
    `price`       DECIMAL(12,2) NOT NULL,
    `qty`         INT NOT NULL DEFAULT 1,
    `total`       DECIMAL(12,2) NOT NULL,
    `image`       VARCHAR(255) NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Store Settings ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`        VARCHAR(100) NOT NULL UNIQUE,
    `value`      LONGTEXT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default settings
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES
('store_name', 'Notch Technology'),
('store_email', 'info@notchtech.co'),
('store_phone', '01000000000'),
('store_address', 'القاهرة، مصر'),
('store_logo', ''),
('store_favicon', ''),
('store_description', 'متجر الإلكترونيات الرائد في مصر'),
('social_facebook', ''),
('social_instagram', ''),
('social_twitter', ''),
('social_youtube', ''),
('social_tiktok', ''),
('fawateerk_api_key', ''),
('fawateerk_active', '0'),
('cod_active', '1'),
('cod_label', 'الدفع عند الاستلام'),
('meta_title', 'Notch Technology - متجر الإلكترونيات'),
('meta_description', 'تسوق أحدث المنتجات الإلكترونية من Apple وHuawei وCardo'),
('google_analytics', ''),
('facebook_pixel', ''),
('maintenance_mode', '0'),
('min_order_amount', '0'),
('hero_title', 'أحدث التقنيات بين يديك'),
('hero_subtitle', 'تسوق أفضل المنتجات الإلكترونية من أشهر الماركات العالمية'),
('hero_image', ''),
('hero_btn_text', 'تسوق الآن'),
('hero_btn_url', '/products');

-- ─── Wishlists ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `wishlists` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT UNSIGNED NOT NULL,
    `product_id`  INT UNSIGNED NOT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_wishlist` (`customer_id`, `product_id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Reviews ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reviews` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id`  INT UNSIGNED NOT NULL,
    `customer_id` INT UNSIGNED NULL,
    `name`        VARCHAR(100) NOT NULL,
    `rating`      TINYINT NOT NULL DEFAULT 5,
    `title`       VARCHAR(255) NULL,
    `body`        TEXT NOT NULL,
    `is_approved` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Update Log ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `update_log` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `version`     VARCHAR(20) NOT NULL,
    `description` TEXT NULL,
    `applied_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `update_log` (`version`, `description`) VALUES ('1.0.0', 'Initial installation');

SET FOREIGN_KEY_CHECKS = 1;
